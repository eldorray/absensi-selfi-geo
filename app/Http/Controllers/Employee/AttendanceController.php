<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use App\Services\AttendanceService;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * AttendanceController - Handle employee attendance operations.
 *
 * Focuses only on check-in, check-out, and attendance history.
 * Follows Single Responsibility Principle.
 */
class AttendanceController extends Controller
{
    public function __construct(
        private readonly ImageService $imageService,
        private readonly AttendanceService $attendance,
    ) {}

    /**
     * Display the selfie attendance form.
     */
    public function selfie(): View
    {
        $user = Auth::user();
        $offices = $this->officesForUser($user);

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        return view('attendance.selfie', [
            'offices' => $offices,
            'user' => $user,
            'todayAttendance' => $todayAttendance,
        ]);
    }

    /**
     * Store a new attendance record.
     */
    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $isAjax = $request->expectsJson() || $request->ajax();

        // Check if already checked in today
        if ($this->attendance->hasCheckedInToday($user)) {
            return $this->errorResponse('Anda sudah melakukan absensi hari ini.', 'attendance', $isAjax);
        }

        // Validate schedule
        $dayOff = $this->attendance->dayOffError($user);
        if ($dayOff !== null) {
            return $this->errorResponse($dayOff, 'schedule', $isAjax);
        }

        // Validate time window
        $timeValidation = $this->attendance->checkInWindowError($user);
        if ($timeValidation !== null) {
            return $this->errorResponse($timeValidation, 'time', $isAjax);
        }

        // Validate request
        $validated = $request->validate([
            'office_id' => 'required|exists:offices,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'image_base64' => 'required|string',
        ], [
            'office_id.required' => 'Pilih kantor tujuan.',
            'office_id.exists' => 'Kantor tidak ditemukan.',
            'latitude.required' => 'Lokasi GPS diperlukan.',
            'longitude.required' => 'Lokasi GPS diperlukan.',
            'image_base64.required' => 'Foto selfie diperlukan.',
        ]);

        // Get office and validate geofencing. When the employee is assigned to
        // an office, that office is authoritative — a tampered office_id in the
        // request is ignored so attendance cannot be recorded elsewhere.
        /** @var Office $office */
        $office = $user->office_id
            ? Office::findOrFail($user->office_id)
            : Office::findOrFail($validated['office_id']);
        $distance = $this->attendance->distanceFrom(
            $office,
            (float) $validated['latitude'],
            (float) $validated['longitude'],
        );

        $outOfRange = $this->attendance->outOfRangeError($office, $distance);
        if ($outOfRange !== null) {
            return $this->errorResponse($outOfRange, 'location', $isAjax, true);
        }

        // Save image
        $imagePath = $this->imageService->saveBase64Image($validated['image_base64'], 'attendance', $user->id);
        if ($imagePath === null) {
            return $this->errorResponse('Gagal menyimpan foto. Silakan coba lagi.', 'image', $isAjax);
        }

        // Determine status and create attendance
        $status = $this->attendance->statusNow($user);

        Attendance::create([
            'user_id' => $user->id,
            'academic_year_id' => AcademicYear::getActive()?->id,
            'status' => $status,
            'image_path' => $imagePath,
            'check_in_lat' => $validated['latitude'],
            'check_in_long' => $validated['longitude'],
            'distance_meters' => $distance,
        ]);

        $successMessage = 'Absensi masuk berhasil dicatat! Status: '.$status->label();

        if ($isAjax) {
            return response()->json(['success' => true, 'message' => $successMessage]);
        }

        return redirect()->route('attendance.dashboard')->with('success', $successMessage);
    }

    /**
     * Display the checkout selfie form.
     */
    public function checkout(): View
    {
        $user = Auth::user();
        $offices = $this->officesForUser($user);

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        $schedule = WorkSchedule::todayFor((int) $user->id);

        $checkoutOpensAt = WorkSchedule::checkoutOpensAt($schedule, WorkSetting::current()->before_check_out);
        $checkoutTimeReached = now()->gte($checkoutOpensAt);

        return view('attendance.checkout', [
            'offices' => $offices,
            'user' => $user,
            'todayAttendance' => $todayAttendance,
            'checkoutOpensAt' => $checkoutOpensAt,
            'checkoutTimeReached' => $checkoutTimeReached,
        ]);
    }

    /**
     * Store checkout for today's attendance.
     */
    public function storeCheckout(Request $request): RedirectResponse|JsonResponse
    {
        $user = Auth::user();
        $isAjax = $request->expectsJson() || $request->ajax();

        // Get today's attendance
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        if (! $attendance) {
            return $this->errorResponse('Anda belum melakukan absensi masuk hari ini.', 'attendance', $isAjax);
        }

        if ($attendance->check_out_at) {
            return $this->errorResponse('Anda sudah melakukan absensi pulang hari ini.', 'attendance', $isAjax);
        }

        // Enforce the check-out time window (schedule end - "before check-out").
        $timeError = $this->attendance->checkOutWindowError($user);
        if ($timeError !== null) {
            return $this->errorResponse($timeError, 'time', $isAjax);
        }

        // Validate request
        $validated = $request->validate([
            'office_id' => 'required|exists:offices,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'image_base64' => 'required|string',
        ], [
            'office_id.required' => 'Pilih kantor tujuan.',
            'latitude.required' => 'Lokasi GPS diperlukan.',
            'longitude.required' => 'Lokasi GPS diperlukan.',
            'image_base64.required' => 'Foto selfie diperlukan.',
        ]);

        // Get office and validate geofencing. When the employee is assigned to
        // an office, that office is authoritative — a tampered office_id in the
        // request is ignored so attendance cannot be recorded elsewhere.
        /** @var Office $office */
        $office = $user->office_id
            ? Office::findOrFail($user->office_id)
            : Office::findOrFail($validated['office_id']);
        $distance = $this->attendance->distanceFrom(
            $office,
            (float) $validated['latitude'],
            (float) $validated['longitude'],
        );

        $outOfRange = $this->attendance->outOfRangeError($office, $distance);
        if ($outOfRange !== null) {
            return $this->errorResponse($outOfRange, 'location', $isAjax, true);
        }

        // Save image
        $imagePath = $this->imageService->saveBase64Image($validated['image_base64'], 'attendance', $user->id);
        if ($imagePath === null) {
            return $this->errorResponse('Gagal menyimpan foto. Silakan coba lagi.', 'image', $isAjax);
        }

        // Update attendance with checkout
        $attendance->update([
            'check_out_at' => now(),
            'check_out_image_path' => $imagePath,
            'check_out_lat' => $validated['latitude'],
            'check_out_long' => $validated['longitude'],
        ]);

        $successMessage = 'Absensi pulang berhasil dicatat!';

        if ($isAjax) {
            return response()->json(['success' => true, 'message' => $successMessage]);
        }

        return redirect()->route('attendance.dashboard')->with('success', $successMessage);
    }

    /**
     * Display attendance history for the authenticated user.
     */
    public function index(): View
    {
        $attendances = Attendance::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('attendance.index', ['attendances' => $attendances]);
    }

    /**
     * Offices selectable by the user. When the user is assigned to an office,
     * only that office is returned so the picker is locked to it.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Office>
     */
    private function officesForUser(User $user): \Illuminate\Database\Eloquent\Collection
    {
        if ($user->office_id) {
            return Office::where('id', $user->office_id)->get();
        }

        return Office::all();
    }

    /**
     * Create error response (JSON or redirect).
     */
    private function errorResponse(string $message, string $key, bool $isAjax, bool $withInput = false): RedirectResponse|JsonResponse
    {
        if ($isAjax) {
            return response()->json(['errors' => [$key => [$message]]], 422);
        }

        $response = back()->withErrors([$key => $message]);

        return $withInput ? $response->withInput() : $response;
    }
}

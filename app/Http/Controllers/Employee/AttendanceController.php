<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Enums\AttendanceStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use App\Services\ImageService;
use App\Traits\HasHaversineCalculation;
use Carbon\Carbon;
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
    use HasHaversineCalculation;

    public function __construct(
        private readonly ImageService $imageService
    ) {}

    /**
     * Display the selfie attendance form.
     */
    public function selfie(): View
    {
        $user = Auth::user();
        $offices = Office::all();

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
     * Display the attendance check-in form (legacy).
     */
    public function create(): View
    {
        $user = Auth::user();
        $offices = Office::all();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        return view('attendance.create', [
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
        if ($this->hasCheckedInToday($user->id)) {
            return $this->errorResponse('Anda sudah melakukan absensi hari ini.', 'attendance', $isAjax);
        }

        // Validate schedule
        $scheduleValidation = $this->validateSchedule($user->id);
        if ($scheduleValidation !== null) {
            return $this->errorResponse($scheduleValidation['message'], $scheduleValidation['key'], $isAjax);
        }

        // Validate time window
        $timeValidation = $this->validateTimeWindow();
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

        // Get office and validate geofencing
        $office = Office::findOrFail($validated['office_id']);
        $distance = $this->calculateHaversineDistance(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (float) $office->latitude,
            (float) $office->longitude
        );

        if ($distance > $office->radius_meters) {
            $msg = sprintf('Anda berada %.0f meter dari kantor. Jarak maksimal yang diizinkan adalah %d meter.', $distance, $office->radius_meters);
            return $this->errorResponse($msg, 'location', $isAjax, true);
        }

        // Save image
        $imagePath = $this->imageService->saveBase64Image($validated['image_base64'], 'attendance', $user->id);
        if ($imagePath === null) {
            return $this->errorResponse('Gagal menyimpan foto. Silakan coba lagi.', 'image', $isAjax);
        }

        // Determine status and create attendance
        $status = $this->determineAttendanceStatus();

        Attendance::create([
            'user_id' => $user->id,
            'academic_year_id' => AcademicYear::getActive()?->id,
            'status' => $status,
            'image_path' => $imagePath,
            'check_in_lat' => $validated['latitude'],
            'check_in_long' => $validated['longitude'],
            'distance_meters' => $distance,
        ]);

        $successMessage = 'Absensi masuk berhasil dicatat! Status: ' . $status->label();

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
        $offices = Office::all();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        return view('attendance.checkout', [
            'offices' => $offices,
            'user' => $user,
            'todayAttendance' => $todayAttendance,
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

        if (!$attendance) {
            return $this->errorResponse('Anda belum melakukan absensi masuk hari ini.', 'attendance', $isAjax);
        }

        if ($attendance->check_out_at) {
            return $this->errorResponse('Anda sudah melakukan absensi pulang hari ini.', 'attendance', $isAjax);
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

        // Get office and validate geofencing
        $office = Office::findOrFail($validated['office_id']);
        $distance = $this->calculateHaversineDistance(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (float) $office->latitude,
            (float) $office->longitude
        );

        if ($distance > $office->radius_meters) {
            $msg = sprintf('Anda berada %.0f meter dari kantor. Jarak maksimal yang diizinkan adalah %d meter.', $distance, $office->radius_meters);
            return $this->errorResponse($msg, 'location', $isAjax, true);
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
     * Check if user has already checked in today.
     */
    private function hasCheckedInToday(int $userId): bool
    {
        return Attendance::where('user_id', $userId)
            ->whereDate('created_at', today())
            ->exists();
    }

    /**
     * Validate work schedule for today.
     * 
     * @return array{message: string, key: string}|null
     */
    private function validateSchedule(int $userId): ?array
    {
        $todayDay = strtolower(now()->locale('id')->dayName);
        $schedule = WorkSchedule::where('user_id', $userId)
            ->where('day', $todayDay)
            ->where('is_active', true)
            ->first();

        // Skip schedule check on Sunday if no schedule defined
        if (!$schedule && $todayDay === 'minggu') {
            return ['message' => 'Hari ini (Minggu) adalah hari libur.', 'key' => 'schedule'];
        }

        return null;
    }

    /**
     * Validate check-in time window.
     * 
     * @return string|null Error message if invalid, null if valid
     */
    private function validateTimeWindow(): ?string
    {
        $workSettings = WorkSetting::current();
        $todayDay = strtolower(now()->locale('id')->dayName);
        $userId = Auth::id();

        $schedule = WorkSchedule::where('user_id', $userId)
            ->where('day', $todayDay)
            ->where('is_active', true)
            ->first();

        $checkInTime = $schedule?->check_in_time ?? '07:00:00';
        $scheduleCheckIn = Carbon::parse($checkInTime);
        $earliestCheckIn = $scheduleCheckIn->copy()->subMinutes($workSettings->before_check_in);
        $latestCheckIn = $scheduleCheckIn->copy()->addMinutes($workSettings->late_limit);

        $now = now();

        if ($now->lt($earliestCheckIn)) {
            return 'Anda belum dapat absen. Waktu absen dimulai pukul ' . $earliestCheckIn->format('H:i') . '.';
        }

        if ($now->gt($latestCheckIn)) {
            return 'Waktu absen sudah berakhir. Batas absen adalah pukul ' . $latestCheckIn->format('H:i') . '.';
        }

        return null;
    }

    /**
     * Determine attendance status based on time.
     */
    private function determineAttendanceStatus(): AttendanceStatus
    {
        $workSettings = WorkSetting::current();
        $todayDay = strtolower(now()->locale('id')->dayName);
        $userId = Auth::id();

        $schedule = WorkSchedule::where('user_id', $userId)
            ->where('day', $todayDay)
            ->where('is_active', true)
            ->first();

        $checkInTime = $schedule?->check_in_time ?? '07:00:00';
        $scheduleCheckIn = Carbon::parse($checkInTime);
        $lateThreshold = $scheduleCheckIn->copy()->addMinutes($workSettings->after_check_in);

        return now()->gt($lateThreshold) ? AttendanceStatus::Late : AttendanceStatus::Present;
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

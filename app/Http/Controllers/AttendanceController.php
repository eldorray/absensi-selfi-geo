<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\Office;
use App\Models\WorkSchedule;
use App\Models\WorkSetting;
use App\Traits\HasHaversineCalculation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * AttendanceController
 *
 * Handles attendance check-ins with selfie capture and geolocation validation.
 */
class AttendanceController extends Controller
{
    use HasHaversineCalculation;

    /**
     * Late threshold hour (24-hour format).
     * After this hour, attendance is marked as 'late'.
     */
    private const int LATE_THRESHOLD_HOUR = 9;

    /**
     * Display the employee dashboard with attendance summary.
     */
    public function dashboard(): View
    {
        $user = Auth::user();

        // Get today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        // Get today's work schedule
        $todayDay = strtolower(now()->locale('id')->dayName);
        $todaySchedule = WorkSchedule::where('user_id', $user->id)
            ->where('day', $todayDay)
            ->where('is_active', true)
            ->first();

        // Monthly stats - hadir includes both present and late
        $monthStart = now()->startOfMonth();
        $monthlyPresent = Attendance::where('user_id', $user->id)
            ->whereIn('status', [AttendanceStatus::Present, AttendanceStatus::Late])
            ->whereBetween('created_at', [$monthStart, now()])
            ->count();

        $monthlyLate = Attendance::where('user_id', $user->id)
            ->where('status', AttendanceStatus::Late)
            ->whereBetween('created_at', [$monthStart, now()])
            ->count();

        $totalAttendance = $monthlyPresent; // Same as hadir now

        return view('attendance.dashboard', [
            'todayAttendance' => $todayAttendance,
            'todaySchedule' => $todaySchedule,
            'monthlyPresent' => $monthlyPresent,
            'monthlyLate' => $monthlyLate,
            'totalAttendance' => $totalAttendance,
        ]);
    }

    /**
     * Display the selfie attendance form.
     */
    public function selfie(): View
    {
        $user = Auth::user();
        $offices = Office::all();

        // Check if user already checked in today
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

        // Check if user already checked in today
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
    public function store(Request $request): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = Auth::user();
        $isAjax = $request->expectsJson() || $request->ajax();

        // Check if already checked in today
        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->exists();

        if ($existingAttendance) {
            if ($isAjax) {
                return response()->json(['errors' => ['attendance' => ['Anda sudah melakukan absensi hari ini.']]], 422);
            }
            return back()->withErrors(['attendance' => 'Anda sudah melakukan absensi hari ini.']);
        }

        // Get work settings and user's schedule for today
        $workSettings = WorkSetting::current();
        $todayDay = strtolower(now()->locale('id')->dayName);
        $schedule = WorkSchedule::where('user_id', $user->id)
            ->where('day', $todayDay)
            ->where('is_active', true)
            ->first();

        // Use default schedule if user has no specific schedule
        // Default: 07:00 - 16:00, weekdays only (Sunday is libur)
        $checkInTime = $schedule ? $schedule->check_in_time : '07:00:00';
        $checkOutTime = $schedule ? $schedule->check_out_time : '16:00:00';

        // Skip schedule check on Sunday if no schedule defined
        if (!$schedule && in_array($todayDay, ['minggu'])) {
            if ($isAjax) {
                return response()->json(['errors' => ['schedule' => ['Hari ini (Minggu) adalah hari libur.']]], 422);
            }
            return back()->withErrors(['schedule' => 'Hari ini (Minggu) adalah hari libur.']);
        }

        // Check if within allowed check-in window
        $scheduleCheckIn = \Carbon\Carbon::parse($checkInTime);
        $earliestCheckIn = $scheduleCheckIn->copy()->subMinutes($workSettings->before_check_in);
        $latestCheckIn = $scheduleCheckIn->copy()->addMinutes($workSettings->late_limit);

        $now = now();
        if ($now->lt($earliestCheckIn)) {
            $msg = 'Anda belum dapat absen. Waktu absen dimulai pukul ' . $earliestCheckIn->format('H:i') . '.';
            if ($isAjax) {
                return response()->json(['errors' => ['time' => [$msg]]], 422);
            }
            return back()->withErrors(['time' => $msg]);
        }

        if ($now->gt($latestCheckIn)) {
            $msg = 'Waktu absen sudah berakhir. Batas absen adalah pukul ' . $latestCheckIn->format('H:i') . '.';
            if ($isAjax) {
                return response()->json(['errors' => ['time' => [$msg]]], 422);
            }
            return back()->withErrors(['time' => $msg]);
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

        // Get office
        $office = Office::findOrFail($validated['office_id']);

        // Calculate distance from office using Haversine formula
        $distance = $this->calculateHaversineDistance(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (float) $office->latitude,
            (float) $office->longitude
        );

        // Validate geofencing
        if ($distance > $office->radius_meters) {
            $msg = sprintf('Anda berada %.0f meter dari kantor. Jarak maksimal yang diizinkan adalah %d meter.', $distance, $office->radius_meters);
            if ($isAjax) {
                return response()->json(['errors' => ['location' => [$msg]]], 422);
            }
            return back()->withErrors(['location' => $msg])->withInput();
        }

        // Decode and save Base64 image
        $imagePath = $this->saveBase64Image($validated['image_base64'], $user->id);

        if ($imagePath === null) {
            if ($isAjax) {
                return response()->json(['errors' => ['image' => ['Gagal menyimpan foto. Silakan coba lagi.']]], 422);
            }
            return back()->withErrors(['image' => 'Gagal menyimpan foto. Silakan coba lagi.']);
        }

        // Determine status based on work schedule and tolerance
        $lateThreshold = $scheduleCheckIn->copy()->addMinutes($workSettings->after_check_in);
        $status = $now->gt($lateThreshold) 
            ? AttendanceStatus::Late 
            : AttendanceStatus::Present;

        // Create attendance record
        Attendance::create([
            'user_id' => $user->id,
            'academic_year_id' => AcademicYear::getActive()?->id,
            'status' => $status,
            'image_path' => $imagePath,
            'check_in_lat' => $validated['latitude'],
            'check_in_long' => $validated['longitude'],
            'distance_meters' => $distance,
        ]);

        if ($isAjax) {
            return response()->json(['success' => true, 'message' => 'Absensi masuk berhasil dicatat! Status: ' . $status->label()]);
        }

        return redirect()
            ->route('attendance.dashboard')
            ->with('success', 'Absensi masuk berhasil dicatat! Status: ' . $status->label());
    }

    /**
     * Display the checkout selfie form.
     */
    public function checkout(): View
    {
        $user = Auth::user();
        $offices = Office::all();

        // Get today's attendance (must have checked in)
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
    public function storeCheckout(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // Get today's attendance
        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('created_at', today())
            ->first();

        if (!$attendance) {
            return back()->withErrors([
                'attendance' => 'Anda belum melakukan absensi masuk hari ini.',
            ]);
        }

        if ($attendance->hasCheckedOut()) {
            return back()->withErrors([
                'attendance' => 'Anda sudah melakukan absensi pulang hari ini.',
            ]);
        }

        // Get work settings and user's schedule
        $workSettings = WorkSetting::current();
        $todayDay = strtolower(now()->locale('id')->dayName);
        $schedule = WorkSchedule::where('user_id', $user->id)
            ->where('day', $todayDay)
            ->where('is_active', true)
            ->first();

        // Note: Checkout time restriction is disabled for flexibility
        // Users can checkout any time after checking in

        // Validate request
        $validated = $request->validate([
            'office_id' => 'required|exists:offices,id',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'image_base64' => 'required|string',
        ], [
            'office_id.required' => 'Pilih kantor tujuan.',
            'latitude.required' => 'Lokasi GPS diperlukan.',
            'image_base64.required' => 'Foto selfie diperlukan.',
        ]);

        // Get office
        $office = Office::findOrFail($validated['office_id']);

        // Calculate distance
        $distance = $this->calculateHaversineDistance(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
            (float) $office->latitude,
            (float) $office->longitude
        );

        // Validate geofencing
        if ($distance > $office->radius_meters) {
            return back()->withErrors([
                'location' => sprintf(
                    'Anda berada %.0f meter dari kantor. Jarak maksimal adalah %d meter.',
                    $distance,
                    $office->radius_meters
                ),
            ])->withInput();
        }

        // Save selfie image
        $imagePath = $this->saveBase64Image($validated['image_base64'], $user->id);

        if ($imagePath === null) {
            return back()->withErrors([
                'image' => 'Gagal menyimpan foto. Silakan coba lagi.',
            ]);
        }

        // Update attendance with checkout data
        $attendance->update([
            'check_out_at' => now(),
            'check_out_lat' => $validated['latitude'],
            'check_out_long' => $validated['longitude'],
            'check_out_image_path' => $imagePath,
            'check_out_distance_meters' => $distance,
        ]);

        return redirect()
            ->route('attendance.dashboard')
            ->with('success', 'Absensi pulang berhasil dicatat!');
    }

    /**
     * Display attendance history for the authenticated user.
     */
    public function index(): View
    {
        $attendances = Attendance::where('user_id', Auth::id())
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('attendance.index', [
            'attendances' => $attendances,
        ]);
    }

    /**
     * Decode Base64 image and save to storage.
     *
     * @param string $base64Image The Base64 encoded image string
     * @param int $userId The user ID for unique filename generation
     * @return string|null The relative path to the saved image, or null on failure
     */
    private function saveBase64Image(string $base64Image, int $userId): ?string
    {
        try {
            // Remove data URL prefix if present (e.g., "data:image/jpeg;base64,")
            if (str_contains($base64Image, ',')) {
                $base64Image = explode(',', $base64Image)[1];
            }

            // Decode Base64
            $imageData = base64_decode($base64Image, true);

            if ($imageData === false) {
                return null;
            }

            // Generate unique filename
            $filename = sprintf(
                'attendance/%d_%s_%s.jpg',
                $userId,
                now()->format('Y-m-d_H-i-s'),
                Str::random(8)
            );

            // Save to storage
            Storage::disk('public')->put($filename, $imageData);

            return $filename;
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    /**
     * Display mobile profile page.
     */
    public function profile(): View
    {
        return view('attendance.profile', [
            'user' => Auth::user(),
        ]);
    }

    /**
     * Update user profile from mobile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', \Illuminate\Validation\Rule::unique('users')->ignore($user->id)],
        ], [
            'name.required' => 'Nama harus diisi.',
            'email.required' => 'Email harus diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Display mobile password change page.
     */
    public function password(): View
    {
        return view('attendance.password');
    }

    /**
     * Update password from mobile.
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'current_password.required' => 'Password saat ini harus diisi.',
            'current_password.current_password' => 'Password saat ini tidak sesuai.',
            'password.required' => 'Password baru harus diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
        ]);

        Auth::user()->update([
            'password' => \Illuminate\Support\Facades\Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}

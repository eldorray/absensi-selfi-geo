<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreAttendanceRequest;
use App\Http\Requests\Api\StoreCheckoutRequest;
use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Services\AttendanceService;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Check-in and check-out for the iOS client.
 *
 * Every rule comes from AttendanceService, the same one the Blade controller
 * uses, so the API cannot drift into a looser policy than the web app.
 */
class AttendanceController extends Controller
{
    public function __construct(
        private readonly AttendanceService $attendance,
        private readonly ImageService $images,
    ) {}

    /**
     * Record a check-in for the signed-in teacher.
     *
     * A queued check-in carries `captured_at` (when the teacher actually stood
     * at the gate) and `client_uuid`. Every timing rule is judged against that
     * moment, and the row's created_at is written to it, so the dashboard and
     * history read the real time without any change on their side.
     */
    public function store(StoreAttendanceRequest $request): JsonResponse
    {
        $user = $request->user();
        $capturedAt = $request->capturedAt();
        $clientUuid = $request->clientUuid();
        $moment = Carbon::instance($capturedAt ?? now());

        // A retry after a lost response must not read as a duplicate: the
        // teacher's attendance is already recorded, so hand it back as-is.
        if ($clientUuid !== null) {
            $existing = Attendance::query()
                ->where('user_id', $user->id)
                ->where('client_uuid', $clientUuid)
                ->whereDate('created_at', today())
                ->first();

            if ($existing !== null) {
                return response()->json([
                    'status' => $existing->status->apiValue(),
                    'check_in_time' => $existing->created_at?->format('H:i'),
                    'message' => 'Absen masuk berhasil.',
                ]);
            }
        }

        if ($this->attendance->hasCheckedInToday($user)) {
            $this->fail('attendance', 'Anda sudah melakukan absensi hari ini.');
        }

        if ($message = $this->attendance->dayOffError($user)) {
            $this->fail('schedule', $message);
        }

        if ($message = $this->attendance->checkInWindowError($user, $moment)) {
            $this->fail('time', $message);
        }

        $latitude = (float) $request->validated('latitude');
        $longitude = (float) $request->validated('longitude');

        $office = $this->attendance->officeFor($user);

        if ($office === null) {
            $this->fail('office', 'Kantor Anda belum diatur. Hubungi admin.');
        }

        $distance = $this->attendance->distanceFrom($office, $latitude, $longitude);

        if ($message = $this->attendance->outOfRangeError($office, $distance)) {
            $this->fail('location', $message);
        }

        $imagePath = $this->storePhoto($request->file('photo'));

        if ($imagePath === null) {
            $this->fail('photo', 'Gagal menyimpan foto. Silakan coba lagi.');
        }

        $attendance = new Attendance([
            'user_id' => $user->id,
            'academic_year_id' => AcademicYear::getActive()?->id,
            'status' => $this->attendance->statusAt($user, $moment),
            'image_path' => $imagePath,
            'check_in_lat' => $latitude,
            'check_in_long' => $longitude,
            'distance_meters' => $distance,
            'client_uuid' => $clientUuid,
            'synced_at' => $capturedAt === null ? null : now(),
        ]);

        // Setting created_at before save keeps Eloquent from overwriting it:
        // updateTimestamps() only fills a created_at that is not already dirty.
        $attendance->created_at = $moment;
        $attendance->updated_at = $moment;
        $attendance->save();

        return response()->json([
            'status' => $attendance->status->apiValue(),
            'check_in_time' => $attendance->created_at->format('H:i'),
            'message' => 'Absen masuk berhasil.',
        ], 201);
    }

    /**
     * Close out today's record. Scoped to the token's own user, so a teacher
     * can only ever check themselves out.
     */
    public function checkout(StoreCheckoutRequest $request): JsonResponse
    {
        $user = $request->user();

        $attendance = $this->attendance->todayFor($user);

        if ($attendance === null) {
            $this->fail('attendance', 'Anda belum melakukan absensi masuk hari ini.');
        }

        if ($attendance->check_out_at !== null) {
            $this->fail('attendance', 'Anda sudah melakukan absensi pulang hari ini.');
        }

        if ($message = $this->attendance->checkOutWindowError($user)) {
            $this->fail('time', $message);
        }

        $latitude = $request->validated('latitude');
        $longitude = $request->validated('longitude');
        $distance = null;

        // Coordinates are optional, but when sent they are held to the same
        // geofence as check-in.
        if ($latitude !== null && $longitude !== null) {
            $office = $this->attendance->officeFor($user);

            if ($office !== null) {
                $distance = $this->attendance->distanceFrom($office, (float) $latitude, (float) $longitude);

                if ($message = $this->attendance->outOfRangeError($office, $distance)) {
                    $this->fail('location', $message);
                }
            }
        }

        $checkedOutAt = now();

        $attendance->update([
            'check_out_at' => $checkedOutAt,
            'check_out_image_path' => $this->storePhoto($request->file('photo')),
            'check_out_lat' => $latitude,
            'check_out_long' => $longitude,
            'check_out_distance_meters' => $distance,
        ]);

        return response()->json([
            'check_out_time' => $checkedOutAt->format('H:i'),
            'message' => 'Absen pulang berhasil.',
        ]);
    }

    /**
     * Store an uploaded selfie, or null when none was sent.
     */
    private function storePhoto(?UploadedFile $photo): ?string
    {
        return $photo === null
            ? null
            : $this->images->compressAndStore($photo, 'attendance');
    }

    /**
     * Reject the request with Laravel's standard 422 validation shape.
     *
     * @throws ValidationException
     */
    private function fail(string $key, string $message): never
    {
        throw ValidationException::withMessages([$key => $message]);
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Student;
use App\Models\User;
use App\Services\EmployeeDashboardData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * The "beranda" payload for the iOS client.
 *
 * @property-read EmployeeDashboardData $resource
 */
class DashboardResource extends JsonResource
{
    public function __construct(
        EmployeeDashboardData $resource,
        private readonly User $user,
    ) {
        parent::__construct($resource);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $attendance = $this->resource->todayAttendance;
        $schedule = $this->resource->todaySchedule;

        return [
            'user' => new UserResource($this->user),
            // Akun tertaut untuk fitur Ganti Akun (mirror dashboard web).
            'linked_accounts' => $this->user->linkedAccounts()
                ->with('office')
                ->orderBy('name')
                ->get()
                ->map(fn (User $account): array => [
                    'id' => $account->id,
                    'name' => $account->name,
                    'office' => $account->office?->name,
                ])
                ->all(),
            // Geofence kantor untuk pengukur jarak di klien; null bila belum di-set.
            'office_location' => $this->user->office === null ? null : [
                'name' => $this->user->office->name,
                'latitude' => (float) $this->user->office->latitude,
                'longitude' => (float) $this->user->office->longitude,
                'radius_meters' => (int) $this->user->office->radius_meters,
            ],
            // null until the teacher checks in today.
            'status' => $attendance?->status->apiValue(),
            'check_in_time' => $attendance?->created_at?->format('H:i'),
            'check_out_time' => $attendance?->check_out_at?->format('H:i'),
            // null on a day off.
            'schedule' => $schedule === null ? null : [
                'start' => Carbon::parse($schedule->check_in_time)->format('H:i'),
                'end' => Carbon::parse($schedule->check_out_time)->format('H:i'),
            ],
            'day_name' => $this->todayDayName(),
            'date' => now()->format('Y-m-d'),
            'summary' => [
                'present' => $this->resource->monthlyPresent,
                'late' => $this->resource->monthlyLate,
                'total' => $this->resource->monthlyTotal(),
            ],
            'announcements' => AnnouncementResource::collection($this->resource->announcements),
            // Kapabilitas menentukan menu mana yang dirender klien native. Dikirim
            // dari server — bukan disimpulkan klien dari peran — supaya pencabutan
            // wewenang oleh admin langsung berlaku tanpa rilis aplikasi baru.
            'capabilities' => $this->capabilities(),
            'homeroom' => $this->homeroom(),
        ];
    }

    /**
     * @return array<string, bool>
     */
    private function capabilities(): array
    {
        $level = $this->user->office?->school_level;
        $scoped = in_array($level, Student::LEVELS, true);

        return [
            'is_admin' => $this->user->isAdmin(),
            'can_access_bk' => $this->user->canAccessBk(),
            'is_bk_counselor' => (bool) $this->user->is_bk_counselor && $scoped,
            'is_student_affairs_officer' => (bool) $this->user->is_student_affairs_officer && $scoped,
            'is_homeroom_teacher' => $this->user->activeHomeroomAssignment() !== null,
            'can_approve_leave' => ($this->user->role->is_admin ?? false)
                || $this->user->role?->slug === 'kepala-sekolah',
        ];
    }

    /**
     * Ringkasan kartu "Kelas Wali" di beranda, atau null bila tak ada penugasan.
     *
     * @return array<string, mixed>|null
     */
    private function homeroom(): ?array
    {
        $assignment = $this->user->activeHomeroomAssignment();

        if ($assignment === null) {
            return null;
        }

        $students = Student::query()
            ->where('school_class_id', $assignment->school_class_id)
            ->where('status', 'Aktif');

        return [
            'class_name' => $assignment->schoolClass?->name,
            'academic_year' => $assignment->academicYear?->name,
            'student_count' => (clone $students)->count(),
            'students_with_violations' => (clone $students)
                ->whereHas('bkRecords', fn ($query) => $query
                    ->where('record_type', 'violation')
                    ->whereNull('archived_at'))
                ->count(),
        ];
    }

    /**
     * Today's day name in Indonesian, e.g. "Senin".
     *
     * Set the locale as a statement rather than chaining, matching the rest of
     * the codebase: the chained form widens Carbon's return type.
     */
    private function todayDayName(): string
    {
        $now = now();
        $now->locale('id');

        return $now->isoFormat('dddd');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources;

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

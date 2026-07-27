<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One attendance row as the iOS client reads it: "HH:mm" clock times, a
 * "YYYY-MM-DD" date, and the on_time/late status string.
 *
 * @property-read Attendance $resource
 */
class AttendanceResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'date' => $this->resource->created_at?->format('Y-m-d'),
            'check_in_time' => $this->resource->created_at?->format('H:i'),
            'check_out_time' => $this->resource->check_out_at?->format('H:i'),
            'status' => $this->resource->status->apiValue(),
            'image_url' => $this->resource->image_url,
        ];
    }
}

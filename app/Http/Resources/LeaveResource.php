<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Leave;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * One leave request as the mobile client reads it: "YYYY-MM-DD" dates,
 * the raw type/status strings, and Indonesian labels for display.
 *
 * @property-read Leave $resource
 */
class LeaveResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'type' => $this->resource->type,
            'type_label' => $this->resource->type_label,
            'start_date' => $this->resource->start_date?->format('Y-m-d'),
            'end_date' => $this->resource->end_date?->format('Y-m-d'),
            'duration' => $this->resource->duration,
            'reason' => $this->resource->reason,
            'attachment_url' => $this->resource->attachment_url,
            'status' => $this->resource->status,
            'status_label' => $this->resource->status_label,
            'rejection_reason' => $this->resource->rejection_reason,
            'created_at' => $this->resource->created_at?->toIso8601String(),
        ];
    }
}

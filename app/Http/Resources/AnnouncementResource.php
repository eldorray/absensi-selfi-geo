<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property-read Announcement $resource
 */
class AnnouncementResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'title' => $this->resource->title,
            'summary' => $this->resource->summary,
            // The full text the admin form requires. Without it the mobile
            // detail pages have nothing to show, since `summary` is optional.
            'body' => $this->resource->body,
            'image_url' => $this->resource->image_url,
        ];
    }
}

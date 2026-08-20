<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BkRecordResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id, 'record_type' => $this->record_type, 'occurred_at' => $this->occurred_at?->toISOString(), 'custom_topic' => $this->custom_topic,
            'severity' => $this->severity, 'chronology' => $this->chronology, 'action_taken' => $this->action_taken, 'counseling_content' => $this->counseling_content,
            'counseling_result' => $this->counseling_result, 'follow_up_plan' => $this->follow_up_plan, 'next_follow_up_at' => $this->next_follow_up_at?->toISOString(),
            'status' => $this->status, 'archived_at' => $this->archived_at?->toISOString(), 'school_level' => $this->school_level,
            'student' => $this->whenLoaded('student'), 'category' => $this->whenLoaded('category'), 'counselor' => $this->whenLoaded('counselor'),
            'related_students' => $this->whenLoaded('relatedStudents'), 'attachments' => $this->whenLoaded('attachments', fn () => $this->attachments->map(fn ($a) => ['id' => $a->id, 'name' => $a->original_name, 'mime_type' => $a->mime_type, 'size_bytes' => $a->size_bytes, 'download_url' => url("/api/bk/records/{$this->id}/attachments/{$a->id}")])),
            'follow_ups' => $this->whenLoaded('followUps'), 'parent_contacts' => $this->whenLoaded('parentContacts'),
            'parent_contacted' => $this->whenLoaded('parentContacts', fn (): bool => $this->parentContacts->isNotEmpty()),
            'attachments_count' => $this->whenCounted('attachments'), 'follow_ups_count' => $this->whenCounted('followUps'), 'parent_contacts_count' => $this->whenCounted('parentContacts'),
            'created_at' => $this->created_at?->toISOString(), 'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

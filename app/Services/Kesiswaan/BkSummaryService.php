<?php

namespace App\Services\Kesiswaan;

use App\Models\Student;
use App\Models\User;

class BkSummaryService
{
    public function for(Student $s, User $actor): array
    {
        $q = $s->bkRecords()->visibleTo($actor)->whereNull('archived_at');

        return ['active_count' => (clone $q)->where('status', '!=', 'completed')->count(), 'types' => (clone $q)->distinct()->pluck('record_type')->all(), 'statuses' => (clone $q)->distinct()->pluck('status')->all(), 'needs_follow_up' => (clone $q)->where(fn ($x) => $x->whereIn('status', ['in_progress', 'waiting_follow_up'])->orWhereNotNull('next_follow_up_at'))->exists()];
    }
}

<?php

namespace App\Policies;

use App\Models\BkRecord;
use App\Models\User;

class BkRecordPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canAccessBk();
    }

    public function view(User $user, BkRecord $record): bool
    {
        return $user->isAdmin() || $this->ownsRecordInUnit($user, $record);
    }

    public function create(User $user): bool
    {
        return ! $user->isAdmin() && $user->canAccessBk();
    }

    public function update(User $user, BkRecord $record): bool
    {
        return ! $user->isAdmin()
            && $record->archived_at === null
            && $this->ownsRecordInUnit($user, $record);
    }

    /** Archive only; BK records must never be hard-deleted. */
    public function delete(User $user, BkRecord $record): bool
    {
        return $record->archived_at === null && $this->view($user, $record);
    }

    public function restore(User $user, BkRecord $record): bool
    {
        return $record->archived_at !== null && $this->view($user, $record);
    }

    public function forceDelete(User $user, BkRecord $record): bool
    {
        return false;
    }

    public function downloadAttachment(User $user, BkRecord $record): bool
    {
        return $this->view($user, $record);
    }

    private function ownsRecordInUnit(User $user, BkRecord $record): bool
    {
        return $user->canAccessBk()
            && (int) $record->counselor_id === (int) $user->id
            && $record->school_level === $user->office?->school_level;
    }
}

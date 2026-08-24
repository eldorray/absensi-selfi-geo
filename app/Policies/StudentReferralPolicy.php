<?php

namespace App\Policies;

use App\Models\StudentReferral;
use App\Models\User;

class StudentReferralPolicy
{
    public function viewAny(User $u): bool
    {
        return $u->isAdmin() || $u->activeHomeroomAssignment() !== null || ($u->is_bk_counselor && in_array($u->office?->school_level, ['mi', 'smp'], true));
    }

    public function view(User $u, StudentReferral $r): bool
    {
        return $u->isAdmin() || (int) $r->created_by === $u->id || ($u->is_bk_counselor && $r->school_level === $u->office?->school_level && ($r->status->value === 'new' || (int) $r->assigned_counselor_id === $u->id));
    }

    public function create(User $u): bool
    {
        return ! $u->isAdmin() && $u->activeHomeroomAssignment() !== null;
    }

    public function claim(User $u, StudentReferral $r): bool
    {
        return ! $u->isAdmin() && $u->is_bk_counselor && $u->office?->school_level === $r->school_level && $r->status->value === 'new';
    }

    public function transition(User $u, StudentReferral $r): bool
    {
        return $this->claim($u, $r) || (int) $r->assigned_counselor_id === $u->id;
    }

    public function delete(User $u, StudentReferral $r): bool
    {
        return false;
    }
}

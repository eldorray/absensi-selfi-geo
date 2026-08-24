<?php

namespace App\Services\Kesiswaan;

use App\Models\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class StudentAccessService
{
    public function query(User $u): Builder
    {
        $q = Student::query()->where('status', 'Aktif');
        if ($u->isAdmin()) {
            return $q;
        } if ($u->is_student_affairs_officer && in_array($u->office?->school_level, Student::LEVELS, true)) {
            return $q->where('school_level', $u->office->school_level);
        }

        abort(403, 'Anda tidak memiliki akses sebagai Petugas Kesiswaan.');
    }

    public function authorize(User $u, Student $s): void
    {
        abort_unless((clone $this->query($u))->whereKey($s)->exists(), 404);
    }
}

<?php

namespace App\Http\Middleware;

use App\Models\Student;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentAffairsAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user?->is_student_affairs_officer
                && in_array($user->office?->school_level, Student::LEVELS, true),
            403,
            'Anda tidak memiliki akses sebagai Petugas Kesiswaan.'
        );

        return $next($request);
    }
}

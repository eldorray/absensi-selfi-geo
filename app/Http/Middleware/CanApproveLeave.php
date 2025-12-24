<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to check if user can approve leaves (Admin or Kepala Sekolah).
 */
class CanApproveLeave
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Unauthorized access.');
        }

        // Allow Admin (is_admin = true) or Kepala Sekolah role
        $canApprove = $user->role?->is_admin || 
                      $user->role?->slug === 'kepala-sekolah';

        if (!$canApprove) {
            abort(403, 'Anda tidak memiliki izin untuk menyetujui perizinan.');
        }

        return $next($request);
    }
}

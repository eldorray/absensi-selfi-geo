<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureBkAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if (! $user || (! $user->isAdmin() && (! $user->is_bk_counselor || ! in_array($user->office?->school_level, ['mi', 'smp'], true)))) {
            abort(403, 'Akses Catatan BK tidak tersedia atau jenjang kantor belum dikonfigurasi.');
        }

        return $next($request);
    }
}

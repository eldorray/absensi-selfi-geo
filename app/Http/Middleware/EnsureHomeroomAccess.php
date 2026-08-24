<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Wali kelas dengan penugasan aktif pada tahun ajaran berjalan.
 *
 * Gagal tertutup: tanpa tahun ajaran aktif atau tanpa penugasan, akses ditolak.
 * Penugasan yang sudah ditemukan diselipkan ke request supaya controller tidak
 * memanggil ulang query yang sama.
 */
class EnsureHomeroomAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $assignment = $request->user()?->activeHomeroomAssignment();

        abort_unless($assignment !== null, 403, 'Anda tidak memiliki penugasan wali kelas aktif.');

        $request->attributes->set('homeroom_assignment', $assignment);

        return $next($request);
    }
}

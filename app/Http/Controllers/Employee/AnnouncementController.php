<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Employee-facing announcement detail page.
 */
class AnnouncementController extends Controller
{
    /**
     * Show a single active announcement.
     */
    public function show(Announcement $announcement): View
    {
        if (! $announcement->is_active) {
            throw new NotFoundHttpException;
        }

        return view('attendance.information.show', compact('announcement'));
    }
}

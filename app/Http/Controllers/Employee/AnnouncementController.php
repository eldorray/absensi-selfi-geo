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
        $officeId = auth()->user()?->office_id;

        // Not visible if inactive, or targeted at a different office than the
        // viewer's (null office_id = global, visible to everyone).
        if (! $announcement->is_active
            || ($announcement->office_id !== null && $announcement->office_id !== $officeId)) {
            throw new NotFoundHttpException;
        }

        return view('attendance.information.show', compact('announcement'));
    }
}

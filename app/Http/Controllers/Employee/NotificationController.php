<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $notifications = $request->user()->notifications()->where('type', 'like', '%StudentReferral%')->latest()->paginate(20);

        return view('attendance.kesiswaan.notifications', compact('notifications'));
    }

    public function show(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user()->notifications()->whereKey($notification)->where('type', 'like', '%StudentReferral%')->firstOrFail();
        $item->markAsRead();

        return redirect()->to($item->data['url'] ?? route('attendance.kesiswaan.index'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->where('type', 'like', '%StudentReferral%')->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi Kesiswaan telah dibaca.');
    }
}

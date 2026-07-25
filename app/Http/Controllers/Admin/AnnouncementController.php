<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * Controller for managing Announcements (Informasi) shown on the teacher dashboard.
 */
class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements.
     */
    public function index(): View
    {
        $announcements = Announcement::with('office')
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('admin.announcements.index', compact('announcements'));
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create(): View
    {
        return view('admin.announcements.create', [
            'offices' => Office::orderBy('name')->get(),
        ]);
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateData($request);

        if ($request->hasFile('image')) {
            $validated['image_path'] = $request->file('image')->store('announcements', 'public');
        }

        Announcement::create($validated);

        return redirect()
            ->route('admin.announcements.index')
            ->with('status', 'Informasi berhasil ditambahkan.');
    }

    /**
     * Show the form for editing an announcement.
     */
    public function edit(Announcement $announcement): View
    {
        return view('admin.announcements.edit', [
            'announcement' => $announcement,
            'offices' => Office::orderBy('name')->get(),
        ]);
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $validated = $this->validateData($request);

        if ($request->hasFile('image')) {
            if ($announcement->image_path) {
                Storage::disk('public')->delete($announcement->image_path);
            }
            $validated['image_path'] = $request->file('image')->store('announcements', 'public');
        }

        $announcement->update($validated);

        return redirect()
            ->route('admin.announcements.index')
            ->with('status', 'Informasi berhasil diperbarui.');
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy(Announcement $announcement): RedirectResponse
    {
        if ($announcement->image_path) {
            Storage::disk('public')->delete($announcement->image_path);
        }

        $announcement->delete();

        return redirect()
            ->route('admin.announcements.index')
            ->with('status', 'Informasi berhasil dihapus.');
    }

    /**
     * Quickly toggle active state from the list.
     */
    public function toggle(Announcement $announcement): RedirectResponse
    {
        $announcement->update(['is_active' => ! $announcement->is_active]);

        return back()->with('status', 'Status informasi diperbarui.');
    }

    /**
     * Shared validation rules for store/update.
     *
     * @return array<string, mixed>
     */
    private function validateData(Request $request): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'summary' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'office_id' => ['nullable', 'exists:offices,id'],
            'image' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['sort_order'] = (int) $request->input('sort_order', 0);
        // Empty select ("Semua Kantor") => global announcement.
        $validated['office_id'] = $request->input('office_id') ?: null;
        unset($validated['image']);

        return $validated;
    }
}

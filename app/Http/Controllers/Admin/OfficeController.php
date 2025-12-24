<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Office;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * OfficeController - CRUD for office management.
 */
class OfficeController extends Controller
{
    /**
     * Display a listing of offices.
     */
    public function index(): View
    {
        $offices = Office::withCount('users')
            ->orderBy('name')
            ->paginate(10);

        return view('admin.offices.index', [
            'offices' => $offices,
        ]);
    }

    /**
     * Show the form for creating a new office.
     */
    public function create(): View
    {
        return view('admin.offices.create');
    }

    /**
     * Store a newly created office.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:5000',
        ], [
            'name.required' => 'Nama kantor wajib diisi.',
            'latitude.required' => 'Latitude wajib diisi.',
            'longitude.required' => 'Longitude wajib diisi.',
            'radius_meters.required' => 'Radius wajib diisi.',
            'radius_meters.min' => 'Radius minimal 10 meter.',
            'radius_meters.max' => 'Radius maksimal 5000 meter.',
        ]);

        Office::create($validated);

        return redirect()
            ->route('admin.offices.index')
            ->with('success', 'Kantor berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified office.
     */
    public function edit(Office $office): View
    {
        return view('admin.offices.edit', [
            'office' => $office,
        ]);
    }

    /**
     * Update the specified office.
     */
    public function update(Request $request, Office $office): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius_meters' => 'required|integer|min:10|max:5000',
        ]);

        $office->update($validated);

        return redirect()
            ->route('admin.offices.index')
            ->with('success', 'Kantor berhasil diperbarui.');
    }

    /**
     * Remove the specified office.
     */
    public function destroy(Office $office): RedirectResponse
    {
        $office->delete();

        return redirect()
            ->route('admin.offices.index')
            ->with('success', 'Kantor berhasil dihapus.');
    }
}

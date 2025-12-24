<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller for managing Academic Years (Tahun Ajaran).
 */
class AcademicYearController extends Controller
{
    /**
     * Display a listing of academic years.
     */
    public function index(): View
    {
        $academicYears = AcademicYear::orderByDesc('start_date')->paginate(10);

        return view('admin.academic-years.index', compact('academicYears'));
    }

    /**
     * Show the form for creating a new academic year.
     */
    public function create(): View
    {
        return view('admin.academic-years.create');
    }

    /**
     * Store a newly created academic year.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20', 'unique:academic_years,name'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        AcademicYear::create($validated);

        return redirect()
            ->route('admin.academic-years.index')
            ->with('status', 'Tahun ajaran berhasil ditambahkan.');
    }

    /**
     * Show the form for editing an academic year.
     */
    public function edit(AcademicYear $academicYear): View
    {
        return view('admin.academic-years.edit', compact('academicYear'));
    }

    /**
     * Update the specified academic year.
     */
    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:20', 'unique:academic_years,name,' . $academicYear->id],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $academicYear->update($validated);

        return redirect()
            ->route('admin.academic-years.index')
            ->with('status', 'Tahun ajaran berhasil diperbarui.');
    }

    /**
     * Remove the specified academic year.
     */
    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        if ($academicYear->is_active) {
            return back()->with('error', 'Tidak dapat menghapus tahun ajaran yang aktif.');
        }

        $academicYear->delete();

        return redirect()
            ->route('admin.academic-years.index')
            ->with('status', 'Tahun ajaran berhasil dihapus.');
    }

    /**
     * Activate the specified academic year.
     */
    public function activate(AcademicYear $academicYear): RedirectResponse
    {
        $academicYear->activate(resetSchedules: true);

        return redirect()
            ->route('admin.academic-years.index')
            ->with('status', "Tahun ajaran {$academicYear->name} berhasil diaktifkan. Jadwal kerja telah di-reset.");
    }
}

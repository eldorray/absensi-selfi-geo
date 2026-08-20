<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BkRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BkRecordController extends Controller
{
    public function index(Request $request): View
    {
        $records = BkRecord::with(['student', 'counselor', 'category'])->when($request->boolean('archived'), fn ($q) => $q->whereNotNull('archived_at'), fn ($q) => $q->whereNull('archived_at'))->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))->when($request->filled('school_level'), fn ($q) => $q->where('school_level', $request->school_level))->latest('occurred_at')->paginate(20)->withQueryString();

        return view('admin.bk-records.index', compact('records'));
    }

    public function show(BkRecord $bkRecord): View
    {
        $bkRecord->load(['student', 'counselor', 'category', 'relatedStudents', 'attachments', 'followUps.creator', 'parentContacts.creator']);

        return view('admin.bk-records.show', ['record' => $bkRecord]);
    }

    public function archive(Request $request, BkRecord $bkRecord): RedirectResponse
    {
        $bkRecord->update(['archived_at' => now(), 'archived_by' => $request->user()->id]);

        return back()->with('success', 'Catatan diarsipkan.');
    }

    public function restore(BkRecord $bkRecord): RedirectResponse
    {
        $bkRecord->update(['archived_at' => null, 'archived_by' => null]);

        return back()->with('success', 'Catatan dipulihkan.');
    }
}

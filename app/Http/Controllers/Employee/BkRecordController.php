<?php

declare(strict_types=1);

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBkRecordRequest;
use App\Http\Requests\UpdateBkRecordRequest;
use App\Models\BkAttachment;
use App\Models\BkRecord;
use App\Models\Student;
use App\Models\StudentReferral;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BkRecordController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', BkRecord::class);
        $records = BkRecord::query()->visibleTo($request->user())->active()->with(['student', 'category'])->latest('occurred_at')->paginate(10);

        return view('attendance.bk.index', compact('records'));
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', BkRecord::class);
        $students = Student::query()->where('school_level', $request->user()->office->school_level)->orderBy('nama_lengkap')->get();
        $categories = \App\Models\BkCategory::query()->where('is_active', true)->orderBy('sort_order')->get();
        $record = new BkRecord;
        $referral = null;
        if ($request->filled('referral')) {
            $referral = StudentReferral::query()->with('student')->whereKey($request->integer('referral'))->where('status', 'in_handling')->where('assigned_counselor_id', $request->user()->id)->firstOrFail();
            abort_if($referral->bkRecord()->exists(), 409, 'Rujukan sudah memiliki Catatan BK.');
            $record->student_id = $referral->student_id;
            $record->student_referral_id = $referral->id;
        }

        return view('attendance.bk.form', compact('record', 'students', 'categories', 'referral'));
    }

    public function show(BkRecord $record): View
    {
        Gate::authorize('view', $record);
        $record->load(['student', 'category', 'relatedStudents', 'attachments', 'followUps', 'parentContacts']);

        return view('attendance.bk.show', compact('record'));
    }

    public function edit(Request $request, BkRecord $record): View
    {
        Gate::authorize('update', $record);
        $students = Student::query()->where('school_level', $request->user()->office->school_level)->orderBy('nama_lengkap')->get();
        $categories = \App\Models\BkCategory::query()->where('is_active', true)->orderBy('sort_order')->get();

        return view('attendance.bk.form', compact('record', 'students', 'categories'));
    }

    public function store(StoreBkRecordRequest $request): RedirectResponse
    {
        $record = DB::transaction(function () use ($request): BkRecord {
            $data = Arr::except($request->validated(), ['related_student_ids', 'attachments']);
            if ($request->filled('student_referral_id')) {
                $referral = StudentReferral::query()->lockForUpdate()->whereKey($request->integer('student_referral_id'))->where('status', 'in_handling')->where('assigned_counselor_id', $request->user()->id)->firstOrFail();
                abort_if($referral->bkRecord()->exists(), 409, 'Rujukan sudah memiliki Catatan BK.');
                $data['student_id'] = $referral->student_id;
                $data['student_referral_id'] = $referral->id;
            }
            $data['counselor_id'] = $request->user()->id;
            $data['school_level'] = $request->user()->office->school_level;
            $data['status_updated_at'] = now();
            $record = BkRecord::query()->create($data);
            $record->relatedStudents()->sync($request->validated('related_student_ids', []));
            $this->storeAttachments($record, $request);

            return $record;
        });

        return redirect()->route('attendance.bk.index')->with('success', "Catatan BK #{$record->id} dibuat.");
    }

    public function update(UpdateBkRecordRequest $request, BkRecord $record): RedirectResponse
    {
        DB::transaction(function () use ($request, $record): void {
            $data = Arr::except($request->validated(), ['student_id', 'related_student_ids', 'attachments']);
            if (array_key_exists('status', $data) && $data['status'] !== $record->status) {
                $data['status_updated_at'] = now();
            }
            $record->update($data);
            if ($request->has('related_student_ids')) {
                $record->relatedStudents()->sync($request->validated('related_student_ids', []));
            }
            $this->storeAttachments($record, $request);
        });

        return back()->with('success', 'Catatan BK diperbarui.');
    }

    public function archive(Request $request, BkRecord $record): RedirectResponse
    {
        Gate::authorize('delete', $record);
        $record->update(['archived_at' => now(), 'archived_by' => $request->user()->id]);

        return back()->with('success', 'Catatan BK diarsipkan.');
    }

    public function restore(Request $request, BkRecord $record): RedirectResponse
    {
        Gate::authorize('restore', $record);
        $record->update(['archived_at' => null, 'archived_by' => null]);

        return back()->with('success', 'Catatan BK dipulihkan.');
    }

    public function followUp(Request $request, BkRecord $record): RedirectResponse
    {
        Gate::authorize('update', $record);
        $data = $request->validate([
            'followed_up_at' => ['required', 'date'],
            'progress_notes' => ['required', 'string', 'max:10000'],
            'result' => ['nullable', 'string', 'max:10000'],
        ]);
        $record->followUps()->create($data + ['created_by' => $request->user()->id]);

        return back()->with('success', 'Tindak lanjut ditambahkan.');
    }

    public function parentContact(Request $request, BkRecord $record): RedirectResponse
    {
        Gate::authorize('update', $record);
        $data = $request->validate([
            'contacted_at' => ['required', 'date'],
            'method' => ['required', 'in:phone,whatsapp,meeting,letter,other'],
            'contact_name' => ['required', 'string', 'max:255'],
            'summary' => ['required', 'string', 'max:10000'],
        ]);
        $record->parentContacts()->create($data + ['created_by' => $request->user()->id]);

        return back()->with('success', 'Komunikasi wali ditambahkan.');
    }

    public function attachment(Request $request, BkAttachment $attachment): StreamedResponse
    {
        Gate::authorize('downloadAttachment', $attachment->record);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        return response()->streamDownload(
            fn () => print (Storage::disk('local')->get($attachment->path)),
            $attachment->original_name,
            ['Content-Type' => $attachment->mime_type],
        );
    }

    private function storeAttachments(BkRecord $record, Request $request): void
    {
        $files = $request->file('attachments', []);
        abort_if($record->attachments()->count() + count($files) > 5, 422, 'A record may have at most 5 attachments.');
        foreach ($files as $file) {
            $path = $file->store("bk/{$record->id}", 'local');
            $record->attachments()->create(['uploaded_by' => $request->user()->id, 'path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(), 'size_bytes' => $file->getSize()]);
        }
    }
}

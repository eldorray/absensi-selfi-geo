<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BkRecordResource;
use App\Models\BkCategory;
use App\Models\BkRecord;
use App\Models\Student;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BkController extends Controller
{
    private const METHODS = ['phone', 'whatsapp', 'meeting', 'letter', 'other'];

    public function meta(Request $request): JsonResponse
    {
        return response()->json([
            'school_level' => $request->user()->office?->school_level,
            'read_only' => $request->user()->isAdmin(),
            'record_types' => BkRecord::TYPES,
            'statuses' => BkRecord::STATUSES,
            'severities' => BkRecord::SEVERITIES,
            'parent_contact_methods' => self::METHODS,
            'categories' => BkCategory::query()->where('is_active', true)->orderBy('sort_order')->get(['id', 'name', 'record_type', 'default_severity']),
            'limits' => ['max_attachments' => 5, 'max_attachment_kb' => 5120, 'attachment_mimes' => ['jpg', 'jpeg', 'png', 'pdf']],
        ]);
    }

    public function students(Request $request): JsonResponse
    {
        $query = Student::query()->with('schoolClass:id,name')->where('status', 'Aktif');
        if (! $request->user()->isAdmin()) {
            $query->where('school_level', $request->user()->office->school_level);
        } elseif ($request->filled('school_level')) {
            $query->where('school_level', $request->string('school_level'));
        }
        if ($request->filled('search')) {
            $query->where(fn (Builder $q) => $q->where('nama_lengkap', 'like', '%'.$request->string('search').'%')->orWhere('nisn', 'like', '%'.$request->string('search').'%'));
        }
        $students = $query->orderBy('nama_lengkap')->paginate($this->perPage($request));

        return response()->json($students);
    }

    public function index(Request $request): JsonResponse
    {
        $query = $this->visibleRecords($request)->with(['student.schoolClass', 'category', 'counselor:id,name'])->withCount(['attachments', 'followUps', 'parentContacts']);
        foreach (['record_type', 'status', 'severity', 'student_id'] as $field) {
            if ($request->filled($field)) {
                $query->where($field, $request->input($field));
            }
        }
        if ($request->filled('archived')) {
            $request->boolean('archived') ? $query->whereNotNull('archived_at') : $query->whereNull('archived_at');
        } else {
            $query->whereNull('archived_at');
        }
        if ($request->filled('from')) {
            $query->whereDate('occurred_at', '>=', $request->input('from'));
        }
        if ($request->filled('to')) {
            $query->whereDate('occurred_at', '<=', $request->input('to'));
        }
        if ($request->filled('search')) {
            $query->where(fn (Builder $q) => $q->where('custom_topic', 'like', '%'.$request->string('search').'%')->orWhereHas('student', fn (Builder $s) => $s->where('nama_lengkap', 'like', '%'.$request->string('search').'%')));
        }

        return response()->json(BkRecordResource::collection($query->latest('occurred_at')->paginate($this->perPage($request)))->response()->getData(true));
    }

    public function store(Request $request): JsonResponse
    {
        $this->writable($request);
        $data = $this->validateRecord($request);
        $record = DB::transaction(function () use ($request, $data) {
            $related = $data['related_student_ids'] ?? [];
            $files = $data['attachments'] ?? [];
            unset($data['related_student_ids'], $data['attachments']);
            $record = BkRecord::create($data + ['counselor_id' => $request->user()->id, 'school_level' => $request->user()->office->school_level, 'status_updated_at' => now()]);
            $record->relatedStudents()->sync($related);
            $this->saveFiles($record, $files, $request->user()->id);

            return $record;
        });

        return (new BkRecordResource($this->loadRecord($record)))->response()->setStatusCode(201);
    }

    public function show(Request $request, int $record): BkRecordResource
    {
        return new BkRecordResource($this->loadRecord($this->record($request, $record)));
    }

    public function update(Request $request, int $record): BkRecordResource
    {
        $this->writable($request);
        $model = $this->record($request, $record);
        $data = $this->validateRecord($request, $model);
        DB::transaction(function () use ($request, $model, $data) {
            $related = $data['related_student_ids'] ?? null;
            $files = $data['attachments'] ?? [];
            unset($data['related_student_ids'], $data['attachments']);
            if (isset($data['status']) && $data['status'] !== $model->status) {
                $data['status_updated_at'] = now();
            }
            $model->update($data);
            if ($related !== null) {
                $model->relatedStudents()->sync($related);
            } $this->saveFiles($model, $files, $request->user()->id);
        });

        return new BkRecordResource($this->loadRecord($model));
    }

    public function archive(Request $request, int $record): JsonResponse
    {
        $this->writable($request);
        $model = $this->record($request, $record);
        $model->update(['archived_at' => now(), 'archived_by' => $request->user()->id]);

        return response()->json(['message' => 'Catatan BK berhasil diarsipkan.']);
    }

    public function restore(Request $request, int $record): BkRecordResource
    {
        $this->writable($request);
        $model = $this->record($request, $record);
        $model->update(['archived_at' => null, 'archived_by' => null]);

        return new BkRecordResource($this->loadRecord($model));
    }

    public function download(Request $request, int $record, int $attachment): BinaryFileResponse
    {
        $model = $this->record($request, $record);
        $file = $model->attachments()->findOrFail($attachment);
        abort_unless(Storage::disk('local')->exists($file->path), 404);

        return response()->download(Storage::disk('local')->path($file->path), $file->original_name, ['Content-Type' => $file->mime_type]);
    }

    public function followUps(Request $request, int $record): JsonResponse
    {
        return response()->json($this->record($request, $record)->followUps()->latest('followed_up_at')->paginate($this->perPage($request)));
    }

    public function storeFollowUp(Request $request, int $record): JsonResponse
    {
        $this->writable($request);
        $model = $this->record($request, $record);
        $data = $request->validate(['followed_up_at' => 'required|date', 'progress_notes' => 'required|string|max:10000', 'result' => 'nullable|string|max:10000']);

        return response()->json($model->followUps()->create($data + ['created_by' => $request->user()->id]), 201);
    }

    public function updateFollowUp(Request $request, int $record, int $followUp): JsonResponse
    {
        $this->writable($request);
        $model = $this->record($request, $record);
        $item = $model->followUps()->findOrFail($followUp);
        $item->update($request->validate(['followed_up_at' => 'sometimes|required|date', 'progress_notes' => 'sometimes|required|string|max:10000', 'result' => 'nullable|string|max:10000']));

        return response()->json($item->fresh());
    }

    public function parentContacts(Request $request, int $record): JsonResponse
    {
        return response()->json($this->record($request, $record)->parentContacts()->latest('contacted_at')->paginate($this->perPage($request)));
    }

    public function storeParentContact(Request $request, int $record): JsonResponse
    {
        $this->writable($request);
        $model = $this->record($request, $record);
        $data = $this->validateContact($request);

        return response()->json($model->parentContacts()->create($data + ['created_by' => $request->user()->id]), 201);
    }

    public function updateParentContact(Request $request, int $record, int $contact): JsonResponse
    {
        $this->writable($request);
        $model = $this->record($request, $record);
        $item = $model->parentContacts()->findOrFail($contact);
        $item->update($this->validateContact($request, true));

        return response()->json($item->fresh());
    }

    private function visibleRecords(Request $request): Builder
    {
        $q = BkRecord::query();

        return $request->user()->isAdmin() ? $q : $q->where('counselor_id', $request->user()->id)->where('school_level', $request->user()->office->school_level);
    }

    private function record(Request $request, int $id): BkRecord
    {
        return $this->visibleRecords($request)->findOrFail($id);
    }

    private function writable(Request $request): void
    {
        abort_if($request->user()->isAdmin(), 403, 'Administrator hanya memiliki akses baca pada Catatan BK.');
    }

    private function perPage(Request $request): int
    {
        return min(100, max(1, $request->integer('per_page', 20)));
    }

    private function loadRecord(BkRecord $record): BkRecord
    {
        return $record->fresh(['student.schoolClass', 'category', 'counselor:id,name', 'relatedStudents.schoolClass', 'attachments', 'followUps', 'parentContacts']);
    }

    private function validateRecord(Request $request, ?BkRecord $record = null): array
    {
        $level = $request->user()->office->school_level;
        $required = $record ? 'sometimes|required' : 'required';

        return $request->validate([
            'student_id' => [$required, 'integer', Rule::exists('students', 'id')->where(fn ($q) => $q->where('school_level', $level))],
            'category_id' => ['nullable', 'integer', Rule::exists('bk_categories', 'id')->where(fn ($q) => $q->where('is_active', true))],
            'record_type' => [$required, Rule::in(BkRecord::TYPES)], 'occurred_at' => [$required, 'date'], 'custom_topic' => 'nullable|string|max:255',
            'severity' => ['nullable', Rule::in(BkRecord::SEVERITIES)], 'chronology' => 'nullable|string|max:10000', 'action_taken' => 'nullable|string|max:10000',
            'counseling_content' => 'nullable|string|max:10000', 'counseling_result' => 'nullable|string|max:10000', 'follow_up_plan' => 'nullable|string|max:10000', 'next_follow_up_at' => 'nullable|date',
            'status' => [$record ? 'sometimes' : 'nullable', Rule::in(BkRecord::STATUSES)],
            'related_student_ids' => 'sometimes|array', 'related_student_ids.*' => ['integer', 'distinct', Rule::exists('students', 'id')->where(fn ($q) => $q->where('school_level', $level))],
            'attachments' => 'sometimes|array|max:5', 'attachments.*' => 'file|max:5120|mimes:jpg,jpeg,png,pdf',
        ]);
    }

    private function saveFiles(BkRecord $record, array $files, int $userId): void
    {
        abort_if($record->attachments()->count() + count($files) > 5, 422, 'Setiap catatan maksimal memiliki lima lampiran.');
        foreach ($files as $file) {/** @var UploadedFile $file */ $path = $file->store('bk/'.$record->id, 'local');
            $record->attachments()->create(['uploaded_by' => $userId, 'path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(), 'size_bytes' => $file->getSize()]);
        }
    }

    private function validateContact(Request $request, bool $partial = false): array
    {
        $r = $partial ? ['sometimes', 'required'] : ['required'];

        return $request->validate(['contacted_at' => [...$r, 'date'], 'method' => [...$r, Rule::in(self::METHODS)], 'contact_name' => [...$r, 'string', 'max:255'], 'summary' => [...$r, 'string', 'max:10000']]);
    }
}

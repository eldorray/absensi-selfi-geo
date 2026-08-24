<?php

namespace App\Services\Kesiswaan;

use App\Models\Student;
use App\Models\StudentReferral;
use App\Models\User;
use App\Notifications\StudentReferralCreated;
use App\Notifications\StudentReferralStatusChanged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ReferralService
{
    public function create(Student $student, User $creator, array $data, array $files = []): StudentReferral
    {
        $paths = [];
        try {
            return DB::transaction(function () use ($student, $creator, $data, $files, &$paths): StudentReferral {
                $referral = StudentReferral::query()->create($data + ['student_id' => $student->id, 'created_by' => $creator->id, 'school_level' => $student->school_level, 'status' => 'new']);
                $referral->histories()->create(['actor_id' => $creator->id, 'from_status' => null, 'to_status' => 'new', 'transitioned_at' => now()]);
                foreach ($files as $file) {
                    $path = $file->store("kesiswaan/referrals/{$referral->id}", 'local');
                    throw_unless($path, \RuntimeException::class, 'Penyimpanan lampiran gagal.');
                    $paths[] = $path;
                    $referral->attachments()->create(['uploaded_by' => $creator->id, 'path' => $path, 'original_name' => $file->getClientOriginalName(), 'mime_type' => $file->getMimeType(), 'size_bytes' => $file->getSize()]);
                }
                DB::afterCommit(fn () => User::query()->where('is_bk_counselor', true)->whereHas('office', fn ($q) => $q->where('school_level', $student->school_level))->get()->each->notify(new StudentReferralCreated($referral->load('student'))));

                return $referral;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($paths);
            throw $e;
        }
    }

    public function claim(StudentReferral $referral, User $actor): void
    {
        DB::transaction(function () use ($referral, $actor): void {
            $locked = StudentReferral::query()->lockForUpdate()->findOrFail($referral->id);
            if ($locked->status->value !== 'new' || $locked->assigned_counselor_id !== null) {
                throw ValidationException::withMessages(['referral' => 'Rujukan telah diambil Guru BK lain.']);
            }
            $locked->update(['status' => 'in_handling', 'assigned_counselor_id' => $actor->id, 'claimed_at' => now()]);
            $locked->histories()->create(['actor_id' => $actor->id, 'from_status' => 'new', 'to_status' => 'in_handling', 'transitioned_at' => now()]);
            DB::afterCommit(fn () => $locked->creator?->notify(new StudentReferralStatusChanged($locked->load('student'))));
        });
    }

    public function transition(StudentReferral $referral, User $actor, string $to, string $summary): void
    {
        DB::transaction(function () use ($referral, $actor, $to, $summary): void {
            $locked = StudentReferral::query()->lockForUpdate()->findOrFail($referral->id);
            $from = $locked->status->value;
            $legal = ($from === 'new' && $to === 'rejected') || ($from === 'in_handling' && in_array($to, ['completed', 'rejected'], true));
            if (! $legal || ($from === 'in_handling' && (int) $locked->assigned_counselor_id !== $actor->id)) {
                throw ValidationException::withMessages(['status' => 'Transisi status tidak diizinkan.']);
            }
            $locked->update(['status' => $to, 'safe_summary' => $summary, $to === 'completed' ? 'completed_at' : 'rejected_at' => now()]);
            $locked->histories()->create(['actor_id' => $actor->id, 'from_status' => $from, 'to_status' => $to, 'safe_summary' => $summary, 'transitioned_at' => now()]);
            DB::afterCommit(fn () => $locked->creator?->notify(new StudentReferralStatusChanged($locked->load('student'))));
        });
    }
}

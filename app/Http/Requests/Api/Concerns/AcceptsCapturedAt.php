<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Concerns;

use Carbon\Carbon;

/**
 * Waktu tangkap dari antrean offline, dipakai absen masuk maupun pulang.
 *
 * Aturannya hidup di satu tempat: dua salinan yang boleh berselisih adalah
 * dua perilaku keamanan yang boleh berselisih.
 */
trait AcceptsCapturedAt
{
    /**
     * Toleransi selisih jam perangkat terhadap jam server, dalam menit.
     */
    public const CLOCK_SKEW_MINUTES = 2;

    /**
     * Dinilai terhadap jam server: jam perangkat justru yang sedang
     * diverifikasi, jadi tak boleh jadi alat verifikasinya sendiri.
     *
     * @return array<string, list<string>>
     */
    protected function capturedAtRules(): array
    {
        return [
            'captured_at' => [
                'nullable',
                'date',
                'after_or_equal:'.now()->startOfDay()->toDateTimeString(),
                'before_or_equal:'.now()->addMinutes(self::CLOCK_SKEW_MINUTES)->toDateTimeString(),
            ],
            'client_uuid' => ['nullable', 'uuid', 'required_with:captured_at'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function capturedAtMessages(): array
    {
        return [
            'captured_at.after_or_equal' => 'Absen tertunda hanya dapat dikirim pada hari yang sama.',
            'captured_at.before_or_equal' => 'Waktu absen tidak valid. Periksa jam pada perangkat Anda.',
            'client_uuid.required_with' => 'Kiriman absen tertunda harus menyertakan client_uuid.',
        ];
    }

    /**
     * Waktu tangkap di perangkat, atau null untuk absen online biasa.
     */
    public function capturedAt(): ?Carbon
    {
        $value = $this->validated('captured_at');

        return $value === null ? null : Carbon::parse($value);
    }

    public function clientUuid(): ?string
    {
        return $this->validated('client_uuid');
    }
}

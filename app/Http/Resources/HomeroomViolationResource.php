<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\BkRecord;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Pelanggaran seperti yang boleh dibaca WALI KELAS.
 *
 * Sengaja bukan BkRecordResource: wali kelas tidak berhak membaca kronologi,
 * isi konseling, hasil konseling, tindak lanjut internal, kontak orang tua,
 * maupun lampiran. Resource terpisah membuat batas privasi itu terlihat di satu
 * berkas, bukan tersebar sebagai `unset()` di controller.
 *
 * @property-read BkRecord $resource
 */
class HomeroomViolationResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $record = $this->resource;

        return [
            'id' => $record->id,
            'occurred_at' => $record->occurred_at?->toIso8601String(),
            'category_name' => $record->category?->name,
            'custom_topic' => $record->custom_topic,
            'severity' => $record->severity,
            'status' => $record->status,
            'has_follow_up_plan' => filled($record->follow_up_plan),
            'next_follow_up_at' => $record->next_follow_up_at?->toIso8601String(),
        ];
    }
}

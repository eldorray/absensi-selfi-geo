<?php

namespace App\Http\Requests;

use App\Models\BkRecord;

class UpdateBkRecordRequest extends StoreBkRecordRequest
{
    public function authorize(): bool
    {
        $record = $this->route('record') ?? $this->route('bkRecord');

        return $record instanceof BkRecord && ($this->user()?->can('update', $record) ?? false);
    }
}

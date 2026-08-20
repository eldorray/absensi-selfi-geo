<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BkAttachment extends Model
{
    protected $fillable = ['bk_record_id', 'uploaded_by', 'path', 'original_name', 'mime_type', 'size_bytes'];

    public function record(): BelongsTo
    {
        return $this->belongsTo(BkRecord::class, 'bk_record_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BkFollowUp extends Model
{
    protected $fillable = ['bk_record_id', 'created_by', 'followed_up_at', 'progress_notes', 'result'];

    protected function casts(): array
    {
        return ['followed_up_at' => 'datetime'];
    }

    public function record(): BelongsTo
    {
        return $this->belongsTo(BkRecord::class, 'bk_record_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}

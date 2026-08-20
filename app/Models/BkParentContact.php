<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BkParentContact extends Model
{
    protected $fillable = ['bk_record_id', 'created_by', 'contacted_at', 'method', 'contact_name', 'summary'];

    protected function casts(): array
    {
        return ['contacted_at' => 'datetime'];
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

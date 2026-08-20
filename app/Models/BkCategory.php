<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BkCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'record_type', 'default_severity', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'sort_order' => 'integer'];
    }

    public function records(): HasMany
    {
        return $this->hasMany(BkRecord::class, 'category_id');
    }
}

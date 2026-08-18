<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class SchoolClass extends Model
{
    use HasFactory;

    protected $fillable = ['school_level', 'name', 'normalized_name', 'grade_level', 'is_active'];

    protected function casts(): array
    {
        return ['grade_level' => 'integer', 'is_active' => 'boolean'];
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class);
    }

    public static function normalizeName(string $name): string
    {
        return Str::of($name)->squish()->lower()->value();
    }
}

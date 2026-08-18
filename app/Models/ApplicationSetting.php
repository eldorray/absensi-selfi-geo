<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ApplicationSetting extends Model
{
    use HasFactory;

    protected $fillable = ['application_logo_path', 'application_icon_path'];

    public static function current(): self
    {
        if (! Schema::hasTable('application_settings')) {
            return new self;
        }

        return self::query()->firstOrCreate([]);
    }

    public function logoUrl(): ?string
    {
        return $this->application_logo_path === null
            ? null
            : route('branding.asset', ['type' => 'logo', 'v' => $this->updated_at?->timestamp]);
    }

    public function iconUrl(): string
    {
        return $this->application_icon_path === null
            ? asset('images/icons/icon-512.png?v=2')
            : route('branding.asset', ['type' => 'icon', 'v' => $this->updated_at?->timestamp]);
    }
}

<?php

namespace Database\Factories;

use App\Models\ApplicationSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

class ApplicationSettingFactory extends Factory
{
    protected $model = ApplicationSetting::class;

    public function definition(): array
    {
        return ['application_logo_path' => null, 'application_icon_path' => null];
    }
}

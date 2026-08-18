<?php

use App\Models\ApplicationSetting;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

function brandingAdmin(): User
{
    $role = Role::firstOrCreate(['slug' => 'branding-admin'], ['name' => 'Branding Admin', 'is_admin' => true]);

    return User::factory()->create(['role_id' => $role->id]);
}

function brandingTeacher(): User
{
    $role = Role::firstOrCreate(['slug' => 'branding-teacher'], ['name' => 'Branding Teacher', 'is_admin' => false]);

    return User::factory()->create(['role_id' => $role->id]);
}

test('branding settings are only available to administrators', function () {
    $this->get(route('settings.branding.edit'))->assertRedirect(route('login'));
    $this->actingAs(brandingTeacher())->get(route('settings.branding.edit'))->assertRedirect(route('attendance.dashboard'));
    $this->actingAs(brandingAdmin())->get(route('settings.branding.edit'))
        ->assertSuccessful()->assertSee('Branding aplikasi')->assertSee('Logo aplikasi')->assertSee('Ikon aplikasi');
});

test('admin can upload separate application logo and square icon', function () {
    Storage::fake('local');

    $this->actingAs(brandingAdmin())->put(route('settings.branding.update'), [
        'application_logo' => UploadedFile::fake()->image('logo.png', 900, 300),
        'application_icon' => UploadedFile::fake()->image('icon.png', 512, 512),
    ])->assertRedirect(route('settings.branding.edit'))->assertSessionHas('success');

    $settings = ApplicationSetting::current();
    Storage::disk('local')->assertExists($settings->application_logo_path);
    Storage::disk('local')->assertExists($settings->application_icon_path);
});

test('application icon must be square and at least 512 pixels', function () {
    Storage::fake('local');

    $this->actingAs(brandingAdmin())->put(route('settings.branding.update'), [
        'application_icon' => UploadedFile::fake()->image('bad.png', 511, 500),
    ])->assertSessionHasErrors('application_icon');

    expect(ApplicationSetting::query()->count())->toBe(0);
});

test('branding assets can be served without a public storage symlink', function () {
    Storage::fake('local');
    $path = UploadedFile::fake()->image('icon.png', 512, 512)->store('branding', 'local');
    ApplicationSetting::query()->create(['application_icon_path' => $path]);

    $this->get(route('branding.asset', 'icon'))->assertSuccessful()->assertHeader('content-type', 'image/png');
});

test('manifest uses the configured icon and keeps static fallbacks', function () {
    $this->get(route('manifest'))->assertSuccessful()
        ->assertJsonPath('icons.0.src', '/images/icons/icon-192.png?v=2');

    ApplicationSetting::current()->update(['application_icon_path' => 'branding/icon.png']);
    $this->get(route('manifest'))->assertSuccessful()
        ->assertJsonPath('icons.0.src', fn (string $url): bool => str_contains($url, '/branding/icon'))
        ->assertJsonPath('icons.1.purpose', 'any maskable');
});

test('admin can reset uploaded branding files to defaults', function () {
    Storage::fake('local');
    Storage::disk('local')->put('branding/logo.png', 'logo');
    Storage::disk('local')->put('branding/icon.png', 'icon');
    ApplicationSetting::query()->create([
        'application_logo_path' => 'branding/logo.png',
        'application_icon_path' => 'branding/icon.png',
    ]);

    $this->actingAs(brandingAdmin())->delete(route('settings.branding.destroy'))
        ->assertRedirect(route('settings.branding.edit'));

    expect(ApplicationSetting::current()->application_logo_path)->toBeNull()
        ->and(ApplicationSetting::current()->application_icon_path)->toBeNull();
    Storage::disk('local')->assertMissing('branding/logo.png');
    Storage::disk('local')->assertMissing('branding/icon.png');
});

test('configured branding is rendered in shared pages and only admin sees its settings link', function () {
    ApplicationSetting::query()->create([
        'application_logo_path' => 'branding/logo.png',
        'application_icon_path' => 'branding/icon.png',
    ]);

    $this->get(route('home'))->assertSuccessful()->assertSee(route('branding.asset', 'logo'), false)->assertSee(route('branding.asset', 'icon'), false);
    $this->get(route('login'))->assertSuccessful()->assertSee(route('branding.asset', 'logo'), false)->assertSee(route('branding.asset', 'icon'), false);
    $this->actingAs(brandingAdmin())->get(route('settings.profile.edit'))->assertSee('Branding');
    $this->actingAs(brandingTeacher())->get(route('settings.profile.edit'))->assertDontSee(route('settings.branding.edit'));
});

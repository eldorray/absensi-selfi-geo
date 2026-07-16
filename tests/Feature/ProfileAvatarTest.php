<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('employee can upload an avatar and it is compressed/downscaled', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('attendance.profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => UploadedFile::fake()->image('me.jpg', 2000, 2000),
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    $user->refresh();
    expect($user->avatar_path)->not->toBeNull()
        ->and($user->avatar_url)->not->toBeNull()
        ->and($user->avatar_path)->toEndWith('.jpg');
    Storage::disk('public')->assertExists($user->avatar_path);

    // Downscaled to the 512px cap.
    [$w, $h] = getimagesizefromstring(Storage::disk('public')->get($user->avatar_path));
    expect(max($w, $h))->toBeLessThanOrEqual(512);
});

test('uploading a new avatar deletes the old one', function () {
    Storage::fake('public');
    $user = User::factory()->create(['avatar_path' => 'avatars/old.jpg']);
    Storage::disk('public')->put('avatars/old.jpg', 'x');

    $this->actingAs($user)->put(route('attendance.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->image('new.jpg'),
    ])->assertSessionHasNoErrors();

    Storage::disk('public')->assertMissing('avatars/old.jpg');
    Storage::disk('public')->assertExists($user->refresh()->avatar_path);
});

test('avatar must be an image', function () {
    Storage::fake('public');
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('attendance.profile.update'), [
        'name' => $user->name,
        'email' => $user->email,
        'avatar' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
    ])->assertSessionHasErrors('avatar');

    expect($user->refresh()->avatar_path)->toBeNull();
});

test('profile still updates without an avatar', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('attendance.profile.update'), [
        'name' => 'Nama Baru',
        'email' => $user->email,
    ])->assertSessionHasNoErrors();

    expect($user->refresh()->name)->toBe('Nama Baru');
});

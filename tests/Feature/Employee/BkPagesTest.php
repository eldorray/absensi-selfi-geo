<?php

// tests/Feature/Employee/BkPagesTest.php

test('bk views use the guru components and no legacy classes', function (string $view) {
    $template = file_get_contents(resource_path("views/attendance/bk/{$view}.blade.php"));

    expect($template)->not->toContain('glass-card')->not->toContain('theme-')->not->toContain('solid-panel');
})->with(['index', 'form', 'show', 'partials/student-combobox', 'partials/related-students-combobox']);

test('the unused bk create view is gone', function () {
    expect(file_exists(resource_path('views/attendance/bk/create.blade.php')))->toBeFalse();
});

test('bk detail and edit pages render for the owning counselor', function () {
    $role = App\Models\Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = App\Models\Office::create(['name' => 'Unit MI BK', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100, 'school_level' => 'mi']);
    $counselor = App\Models\User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id, 'is_bk_counselor' => true]);
    $record = App\Models\BkRecord::factory()->create(['counselor_id' => $counselor->id, 'school_level' => 'mi', 'record_type' => 'counseling', 'status' => 'in_progress', 'counseling_content' => 'Sesi pertama']);
    $record->followUps()->create(['created_by' => $counselor->id, 'followed_up_at' => now(), 'progress_notes' => 'Progres baik']);
    $record->parentContacts()->create(['created_by' => $counselor->id, 'contacted_at' => now(), 'method' => 'whatsapp', 'contact_name' => 'Ibu Wali', 'summary' => 'Sudah dihubungi']);

    $this->actingAs($counselor)->get(route('attendance.bk.show', $record))
        ->assertSuccessful()
        ->assertSee('Sesi pertama')
        ->assertSee('Progres baik')
        ->assertSee('Ibu Wali · WhatsApp')
        ->assertSee('Diproses');

    $this->actingAs($counselor)->get(route('attendance.bk.edit', $record))
        ->assertSuccessful()
        ->assertSee('Edit Catatan BK')
        ->assertSee('data-bk-student-combobox="primary"', false);
});

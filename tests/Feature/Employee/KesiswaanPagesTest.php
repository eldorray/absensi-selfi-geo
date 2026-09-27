<?php

// tests/Feature/Employee/KesiswaanPagesTest.php

test('kesiswaan views use the guru components and translate raw values', function (string $view) {
    $template = file_get_contents(resource_path("views/attendance/kesiswaan/{$view}.blade.php"));

    expect($template)
        ->not->toContain('solid-panel')
        ->not->toContain('theme-')
        ->not->toContain('border-l-4')
        ->not->toContain('strtoupper($referral->urgency->value)')
        ->not->toContain("ucfirst(str_replace('_', ' ', \$referral->status->value))");
})->with(['index', 'show', 'my-referrals', 'referral-queue']);

test('teacher kesiswaan views format dates in Indonesian', function (string $view) {
    expect(file_get_contents(resource_path("views/attendance/kesiswaan/{$view}.blade.php")))->not->toContain('translatedFormat');
})->with(['show', 'my-referrals', 'referral-queue']);

test('student profile shows the Indonesian month name', function () {
    $role = App\Models\Role::firstOrCreate(['slug' => 'guru'], ['name' => 'Guru', 'is_admin' => false]);
    $office = App\Models\Office::create(['name' => 'Unit MI', 'latitude' => -6.2, 'longitude' => 106.8, 'radius_meters' => 100, 'school_level' => 'mi']);
    $officer = App\Models\User::factory()->create(['role_id' => $role->id, 'office_id' => $office->id, 'is_student_affairs_officer' => true]);
    $student = App\Models\Student::create(['school_level' => 'mi', 'source' => 'manual', 'nama_lengkap' => 'Siswa Oktober', 'nisn' => '1234509876', 'status' => 'Aktif', 'tempat_lahir' => 'Bekasi', 'tanggal_lahir' => '2014-10-05']);

    $this->actingAs($officer)->get(route('attendance.kesiswaan.show', $student))
        ->assertOk()
        ->assertSee('Bekasi, 5 Oktober 2014');
});

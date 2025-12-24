<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Work tolerance settings (global)
        Schema::create('work_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('before_check_in')->default(60); // Sebelum Masuk (menit)
            $table->unsignedInteger('after_check_in')->default(10);  // Sesudah Masuk (menit)
            $table->unsignedInteger('late_limit')->default(120);     // Limit Sesudah Masuk (menit)
            $table->unsignedInteger('before_check_out')->default(30); // Sebelum Pulang (menit)
            $table->boolean('require_check_in')->default(true);      // Wajib Absen Masuk
            $table->timestamps();
        });

        // User work schedules per day
        Schema::create('work_schedules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('day', ['senin', 'selasa', 'rabu', 'kamis', 'jumat', 'sabtu', 'minggu']);
            $table->time('check_in_time')->default('07:00:00');
            $table->time('check_out_time')->default('16:00:00');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_schedules');
        Schema::dropIfExists('work_settings');
    }
};

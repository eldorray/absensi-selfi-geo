<?php

use App\Models\AcademicYear;
use App\Models\WorkSchedule;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Work schedules were previously global (one row per user+day, shared across
     * every academic year). This scopes them per academic year so each year keeps
     * its own set of schedules.
     */
    public function up(): void
    {
        Schema::table('work_schedules', function (Blueprint $table): void {
            $table->foreignId('academic_year_id')->nullable()->after('user_id')
                ->constrained()->cascadeOnDelete();
        });

        // Backfill pre-existing rows: attach them to the active year, or the most
        // recent year if none is active. If there are no academic years yet, leave
        // them null (they degrade to the default schedule until a year is set).
        $yearId = AcademicYear::query()->where('is_active', true)->value('id')
            ?? AcademicYear::query()->orderByDesc('start_date')->value('id');

        if ($yearId !== null) {
            WorkSchedule::query()->whereNull('academic_year_id')->update(['academic_year_id' => $yearId]);
        }

        // Uniqueness is now per academic year.
        Schema::table('work_schedules', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'day']);
            $table->unique(['user_id', 'day', 'academic_year_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_schedules', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'day', 'academic_year_id']);
            $table->dropConstrainedForeignId('academic_year_id');
            $table->unique(['user_id', 'day']);
        });
    }
};

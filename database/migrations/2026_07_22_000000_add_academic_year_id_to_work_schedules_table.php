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
     *
     * Every step is guarded so the migration is idempotent: if an earlier run
     * partially applied (e.g. the column landed but the unique swap failed on
     * MySQL), re-running recovers cleanly.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('work_schedules', 'academic_year_id')) {
            Schema::table('work_schedules', function (Blueprint $table): void {
                $table->foreignId('academic_year_id')->nullable()->after('user_id')
                    ->constrained()->cascadeOnDelete();
            });
        }

        // Backfill pre-existing rows: attach them to the active year, or the most
        // recent year if none is active. If there are no academic years yet, leave
        // them null (they degrade to the default schedule until a year is set).
        $yearId = AcademicYear::query()->where('is_active', true)->value('id')
            ?? AcademicYear::query()->orderByDesc('start_date')->value('id');

        if ($yearId !== null) {
            WorkSchedule::query()->whereNull('academic_year_id')->update(['academic_year_id' => $yearId]);
        }

        // Create the per-year unique BEFORE dropping the old one. MySQL refuses to
        // drop work_schedules_user_id_day_unique while the user_id foreign key
        // still needs an index, and this composite keeps user_id as its leftmost
        // column so it can serve that FK.
        if (! Schema::hasIndex('work_schedules', 'work_schedules_user_id_day_academic_year_id_unique')) {
            Schema::table('work_schedules', function (Blueprint $table): void {
                $table->unique(['user_id', 'day', 'academic_year_id']);
            });
        }

        if (Schema::hasIndex('work_schedules', 'work_schedules_user_id_day_unique')) {
            Schema::table('work_schedules', function (Blueprint $table): void {
                $table->dropUnique('work_schedules_user_id_day_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the old unique first so user_id keeps an index for its FK
        // before the per-year unique is dropped.
        if (! Schema::hasIndex('work_schedules', 'work_schedules_user_id_day_unique')) {
            Schema::table('work_schedules', function (Blueprint $table): void {
                $table->unique(['user_id', 'day']);
            });
        }

        if (Schema::hasIndex('work_schedules', 'work_schedules_user_id_day_academic_year_id_unique')) {
            Schema::table('work_schedules', function (Blueprint $table): void {
                $table->dropUnique('work_schedules_user_id_day_academic_year_id_unique');
            });
        }

        if (Schema::hasColumn('work_schedules', 'academic_year_id')) {
            Schema::table('work_schedules', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('academic_year_id');
            });
        }
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Scopes an announcement to a single office. Null means "all offices" —
     * shown to every teacher. On office delete, the announcement falls back to
     * global rather than being removed.
     */
    public function up(): void
    {
        Schema::table('announcements', function (Blueprint $table): void {
            $table->foreignId('office_id')->nullable()->after('id')
                ->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('office_id');
        });
    }
};

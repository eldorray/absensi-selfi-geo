<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('attendances', 'client_uuid')) {
            Schema::table('attendances', function (Blueprint $table): void {
                $table->uuid('client_uuid')->nullable();
            });
        }

        if (! Schema::hasColumn('attendances', 'check_out_client_uuid')) {
            Schema::table('attendances', function (Blueprint $table): void {
                $table->uuid('check_out_client_uuid')->nullable();
            });
        }

        if (! Schema::hasColumn('attendances', 'synced_at')) {
            Schema::table('attendances', function (Blueprint $table): void {
                $table->timestamp('synced_at')->nullable();
            });
        }

        if (! Schema::hasColumn('attendances', 'check_out_synced_at')) {
            Schema::table('attendances', function (Blueprint $table): void {
                $table->timestamp('check_out_synced_at')->nullable();
            });
        }

        if (! Schema::hasIndex('attendances', 'attendances_user_id_client_uuid_unique')) {
            Schema::table('attendances', function (Blueprint $table): void {
                $table->unique(['user_id', 'client_uuid']);
            });
        }

        if (! Schema::hasIndex('attendances', 'attendances_user_id_check_out_client_uuid_unique')) {
            Schema::table('attendances', function (Blueprint $table): void {
                $table->unique(['user_id', 'check_out_client_uuid']);
            });
        }
    }

    public function down(): void
    {
        // This repair migration intentionally has no rollback. The columns may
        // have been created by the original migration and contain attendance data.
    }
};

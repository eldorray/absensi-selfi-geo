<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            // Idempotensi kiriman dari antrean offline. Nullable: absen online
            // dan baris lama tidak punya uuid, dan MySQL/SQLite mengizinkan
            // banyak NULL pada unique index.
            $table->uuid('client_uuid')->nullable();
            $table->uuid('check_out_client_uuid')->nullable();

            // Non-null = baris ini datang dari antrean offline; nilainya jam
            // server saat menerima, sedangkan created_at memuat jam tangkap.
            $table->timestamp('synced_at')->nullable();
            $table->timestamp('check_out_synced_at')->nullable();

            $table->unique(['user_id', 'client_uuid']);
            $table->unique(['user_id', 'check_out_client_uuid']);
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropUnique(['user_id', 'client_uuid']);
            $table->dropUnique(['user_id', 'check_out_client_uuid']);
            $table->dropColumn([
                'client_uuid',
                'check_out_client_uuid',
                'synced_at',
                'check_out_synced_at',
            ]);
        });
    }
};

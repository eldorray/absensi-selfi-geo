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
        Schema::table('attendances', function (Blueprint $table): void {
            $table->timestamp('check_out_at')->nullable()->after('distance_meters');
            $table->decimal('check_out_lat', 10, 8)->nullable()->after('check_out_at');
            $table->decimal('check_out_long', 11, 8)->nullable()->after('check_out_lat');
            $table->string('check_out_image_path')->nullable()->after('check_out_long');
            $table->float('check_out_distance_meters')->nullable()->after('check_out_image_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table): void {
            $table->dropColumn([
                'check_out_at',
                'check_out_lat',
                'check_out_long',
                'check_out_image_path',
                'check_out_distance_meters',
            ]);
        });
    }
};

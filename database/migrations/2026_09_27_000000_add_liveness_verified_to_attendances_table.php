<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Whether each selfie passed blink detection. false = manual fallback photo
 * (flag for admin review); null = not reported (API/native apps, old records).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->boolean('liveness_verified')->nullable()->after('image_path');
            $table->boolean('check_out_liveness_verified')->nullable()->after('check_out_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn(['liveness_verified', 'check_out_liveness_verified']);
        });
    }
};

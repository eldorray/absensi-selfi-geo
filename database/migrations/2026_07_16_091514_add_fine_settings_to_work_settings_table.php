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
        Schema::table('work_settings', function (Blueprint $table) {
            $table->unsignedInteger('fine_tier1_amount')->default(5000)->after('require_check_in');
            $table->unsignedInteger('fine_tier2_amount')->default(10000)->after('fine_tier1_amount');
            $table->unsignedInteger('fine_tier1_max_minutes')->default(15)->after('fine_tier2_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_settings', function (Blueprint $table) {
            $table->dropColumn(['fine_tier1_amount', 'fine_tier2_amount', 'fine_tier1_max_minutes']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Stores an admin-readable copy of a user's password, encrypted at rest
     * (Laravel `encrypted` cast, APP_KEY). Only ever surfaced on the admin-only
     * users screen / password PDF; hidden from every serialization.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->text('visible_password')->nullable()->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('visible_password');
        });
    }
};

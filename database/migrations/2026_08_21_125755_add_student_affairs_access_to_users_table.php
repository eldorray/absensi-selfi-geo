<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'is_student_affairs_officer')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_student_affairs_officer')->default(false)->after('is_bk_counselor');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('users', 'is_student_affairs_officer')) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_student_affairs_officer');
        });
    }
};

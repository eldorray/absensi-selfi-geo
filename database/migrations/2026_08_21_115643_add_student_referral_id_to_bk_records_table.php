<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('bk_records', 'student_referral_id')) {
            return;
        }

        Schema::table('bk_records', fn (Blueprint $t) => $t->foreignId('student_referral_id')->nullable()->unique()->constrained()->nullOnDelete());
    }

    public function down(): void
    {
        if (! Schema::hasColumn('bk_records', 'student_referral_id')) {
            return;
        }

        Schema::table('bk_records', function (Blueprint $t) {
            $t->dropConstrainedForeignId('student_referral_id');
        });
    }
};

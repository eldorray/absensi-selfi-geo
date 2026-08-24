<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_referral_status_histories')) {
            return;
        }

        Schema::create('student_referral_status_histories', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_referral_id')->constrained()->restrictOnDelete();
            $t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('from_status')->nullable();
            $t->string('to_status');
            $t->text('safe_summary')->nullable();
            $t->timestamp('transitioned_at');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_referral_status_histories');
    }
};

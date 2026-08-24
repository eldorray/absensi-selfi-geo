<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_referrals', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained()->restrictOnDelete();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('school_level', 3);
            $t->foreignId('assigned_counselor_id')->nullable()->constrained('users')->nullOnDelete();
            $t->string('reason');
            $t->text('observation');
            $t->date('observed_at');
            $t->string('urgency', 20)->default('normal');
            $t->string('status', 20)->default('new');
            $t->text('safe_summary')->nullable();
            $t->timestamp('claimed_at')->nullable();
            $t->timestamp('completed_at')->nullable();
            $t->timestamp('rejected_at')->nullable();
            $t->timestamps();
            $t->index(['school_level', 'status', 'urgency']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_referrals');
    }
};

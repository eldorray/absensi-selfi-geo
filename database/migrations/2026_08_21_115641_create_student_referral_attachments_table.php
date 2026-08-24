<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('student_referral_attachments')) {
            return;
        }

        Schema::create('student_referral_attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_referral_id')->constrained()->restrictOnDelete();
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->string('path');
            $t->string('original_name');
            $t->string('mime_type');
            $t->unsignedBigInteger('size_bytes');
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_referral_attachments');
    }
};

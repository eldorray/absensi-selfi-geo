<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('offices', function (Blueprint $table): void {
            $table->string('school_level', 3)->nullable()->after('name')->index();
        });
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('is_bk_counselor')->default(false)->after('role_id')->index();
        });

        Schema::create('bk_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('record_type', 20);
            $table->string('default_severity', 10)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['record_type', 'name']);
        });

        Schema::create('bk_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('counselor_id')->constrained('users');
            $table->foreignId('student_id')->constrained();
            $table->foreignId('category_id')->nullable()->constrained('bk_categories');
            $table->string('school_level', 3);
            $table->string('record_type', 20);
            $table->dateTime('occurred_at');
            $table->string('custom_topic')->nullable();
            $table->string('severity', 10)->nullable();
            $table->text('chronology')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('counseling_content')->nullable();
            $table->text('counseling_result')->nullable();
            $table->text('follow_up_plan')->nullable();
            $table->dateTime('next_follow_up_at')->nullable();
            $table->string('status', 30)->default('new');
            $table->timestamp('status_updated_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->foreignId('archived_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['counselor_id', 'archived_at']);
            $table->index(['school_level', 'record_type', 'status']);
        });

        Schema::create('bk_record_related_students', function (Blueprint $table): void {
            $table->foreignId('bk_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->primary(['bk_record_id', 'student_id']);
        });

        Schema::create('bk_attachments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bk_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->timestamps();
        });

        Schema::create('bk_follow_ups', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bk_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->dateTime('followed_up_at');
            $table->text('progress_notes');
            $table->text('result')->nullable();
            $table->timestamps();
        });

        Schema::create('bk_parent_contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('bk_record_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');
            $table->dateTime('contacted_at');
            $table->string('method', 30);
            $table->string('contact_name');
            $table->text('summary');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bk_parent_contacts');
        Schema::dropIfExists('bk_follow_ups');
        Schema::dropIfExists('bk_attachments');
        Schema::dropIfExists('bk_record_related_students');
        Schema::dropIfExists('bk_records');
        Schema::dropIfExists('bk_categories');
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('is_bk_counselor'));
        Schema::table('offices', fn (Blueprint $table) => $table->dropColumn('school_level'));
    }
};

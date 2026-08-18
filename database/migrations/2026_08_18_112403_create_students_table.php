<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_class_id')->nullable()->constrained()->nullOnDelete();
            $table->string('school_level', 3);
            $table->string('source', 10)->default('manual');
            $table->string('external_id')->nullable();
            $table->string('nama_lengkap');
            $table->string('nisn', 20)->nullable()->unique();
            $table->string('nik', 20)->nullable()->unique();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('tingkat_rombel', 100)->nullable();
            $table->string('status', 20)->default('Aktif');
            $table->string('jenis_kelamin', 1)->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_telepon', 20)->nullable();
            $table->string('kebutuhan_khusus')->nullable();
            $table->string('disabilitas')->nullable();
            $table->string('nomor_kip_pip', 50)->nullable();
            $table->string('nama_ayah_kandung')->nullable();
            $table->string('nama_ibu_kandung')->nullable();
            $table->string('nama_wali')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->index(['school_level', 'status']);
            $table->unique(['school_level', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

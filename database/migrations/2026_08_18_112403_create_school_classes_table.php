<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('school_level', 3);
            $table->string('name');
            $table->string('normalized_name');
            $table->unsignedTinyInteger('grade_level')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['school_level', 'normalized_name']);
            $table->index(['school_level', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('school_classes');
    }
};

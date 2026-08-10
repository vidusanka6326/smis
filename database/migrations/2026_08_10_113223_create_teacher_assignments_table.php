<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_class_subject_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->string('role_in_assignment');
            $table->timestamps();

            $table->unique(
                ['teacher_id', 'school_class_id', 'subject_id', 'academic_year_id', 'role_in_assignment'],
                'teacher_assignments_unique',
            );
            $table->index(['academic_year_id', 'role_in_assignment']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_class_subject_assignments');
    }
};

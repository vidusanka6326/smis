<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained('academic_years')->cascadeOnDelete();
            $table->foreignId('school_class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('subjects')->nullOnDelete();
            $table->date('date');
            $table->string('scope');
            $table->foreignId('taken_by_teacher_id')->nullable()->constrained('teachers')->nullOnDelete();
            $table->timestamp('finalized_at')->nullable();
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['school_class_id', 'date', 'scope']);
            $table->index(['academic_year_id', 'date']);
            $table->index(['taken_by_teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};

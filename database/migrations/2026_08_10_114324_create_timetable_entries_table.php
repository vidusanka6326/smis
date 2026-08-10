<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('timetables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('academic_year_id')->constrained()->restrictOnDelete();
            $table->foreignId('school_class_id')->constrained('classes')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->unsignedTinyInteger('period_number');
            $table->foreignId('subject_id')->constrained()->restrictOnDelete();
            $table->foreignId('teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->timestamps();

            $table->unique(
                ['school_class_id', 'day_of_week', 'period_number', 'academic_year_id'],
                'timetables_class_slot_unique',
            );
            $table->index(['teacher_id', 'day_of_week', 'period_number', 'academic_year_id'], 'timetables_teacher_slot_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('timetables');
    }
};

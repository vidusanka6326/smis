<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('relief_teacher_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timetable_entry_id')->constrained('timetables')->cascadeOnDelete();
            $table->foreignId('relief_teacher_id')->constrained('teachers')->restrictOnDelete();
            $table->date('date');
            $table->string('reason')->nullable();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['timetable_entry_id', 'date'], 'relief_entry_date_unique');
            $table->index(['relief_teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('relief_teacher_assignments');
    }
};

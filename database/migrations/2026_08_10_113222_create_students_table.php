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
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('admission_no')->unique();
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 1);
            $table->string('guardian_name')->nullable();
            $table->string('guardian_phone')->nullable();
            $table->string('guardian_email')->nullable();
            $table->string('guardian_relationship')->nullable();
            $table->foreignId('current_class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id');
            $table->index('gender');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};

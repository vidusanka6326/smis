<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['class_teacher_id']);
        });

        // Clear any Phase 2 user-based class teacher ids before retargeting the FK.
        DB::table('classes')->update(['class_teacher_id' => null]);

        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('class_teacher_id')
                ->references('id')
                ->on('teachers')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $table->dropForeign(['class_teacher_id']);
        });

        DB::table('classes')->update(['class_teacher_id' => null]);

        Schema::table('classes', function (Blueprint $table) {
            $table->foreign('class_teacher_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }
};

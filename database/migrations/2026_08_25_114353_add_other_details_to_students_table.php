<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->text('address')->nullable();
            $table->string('grama_niladari_division')->nullable();
            $table->string('travel_method')->nullable();
            $table->string('town')->nullable();
            $table->json('relations_in_school')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'address',
                'grama_niladari_division',
                'travel_method',
                'town',
                'relations_in_school',
            ]);
        });
    }
};

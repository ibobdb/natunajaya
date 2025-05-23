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
        Schema::table('schedules', function (Blueprint $table) {
            $table->unsignedBigInteger('car_id')->nullable()->after('id');
            $table->foreign('car_id')->references('id')->on('cars')->onDelete('set null');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->unsignedBigInteger('default_car')->nullable()->after('id');
            $table->enum('default_car_type', ['matic', 'manual'])->nullable()->after('default_car');
            $table->foreign('default_car')->references('id')->on('cars')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->dropForeign(['car_id']);
            $table->dropColumn('car_id');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['default_car']);
            $table->dropColumn(['default_car', 'default_car_type']);
        });
    }
};

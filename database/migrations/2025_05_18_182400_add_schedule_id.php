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
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('schedule_id')->nullable()->after('invoice_id')->constrained('schedules');
            $table->foreignId('car_id')->nullable()->after('schedule_id')->constrained('cars');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['schedule_id']);
            $table->dropForeign(['car_id']);
            $table->dropColumn(['schedule_id', 'car_id']);
        });
    }
};

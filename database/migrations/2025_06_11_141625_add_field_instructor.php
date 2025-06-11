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
            // Add instructor_id field as a foreign key
            $table->foreignId('instructor_id')->nullable()->constrained('instructors')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Drop the foreign key first
            $table->dropForeign(['instructor_id']);
            // Then drop the column
            $table->dropColumn('instructor_id');
        });
    }
};

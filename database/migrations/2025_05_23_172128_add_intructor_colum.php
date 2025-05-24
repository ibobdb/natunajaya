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
        // Add instructor_id to student_course table
        Schema::table('student_courses', function (Blueprint $table) {
            $table->unsignedBigInteger('instructor_id')->nullable();
            $table->foreign('instructor_id')->references('id')->on('instructors')->onDelete('set null');
        });

        // Add ongoing columns to instructor table
        Schema::table('instructors', function (Blueprint $table) {
            $table->integer('ongoing_course')->default(0);
            $table->integer('ongoing_sch')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop foreign key and column from student_course table
        Schema::table('student_courses', function (Blueprint $table) {
            $table->dropForeign(['instructor_id']);
            $table->dropColumn('instructor_id');
        });

        // Drop columns from instructor table
        Schema::table('instructors', function (Blueprint $table) {
            $table->dropColumn(['ongoing_course', 'ongoing_sch']);
        });
    }
};

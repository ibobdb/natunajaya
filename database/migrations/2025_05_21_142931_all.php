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


        // Students table
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // Courses table
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->integer('session');
            $table->enum('duration_session', ['week', 'month', 'year']);
            $table->integer('duration');
            $table->integer('price');
            $table->date('expired')->nullable();
            $table->timestamps();
        });

        // Cars table
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type');
            $table->timestamps();
        });

        // Student_Courses table
        Schema::create('student_courses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->enum('status', ['active', 'waiting_schedule', 'schedule_not_set', 'done']);
            $table->date('active_on');
            $table->integer('score')->nullable();
            $table->timestamps();
        });

        // Schedules table
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_course_id')->constrained('student_courses')->onDelete('cascade');
            $table->integer('for_session');
            $table->dateTime('start_date');
            $table->enum('duration_session', ['week', 'month', 'year']);
            $table->timestamps();

            // Prevent duplicate sessions for each student_course_id
            $table->unique(['student_course_id', 'for_session']);
        });

        // Instructors table
        Schema::create('instructors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->timestamps();
        });

        // Attendance table
        Schema::create('attendance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('schedule_id')->constrained('schedules')->onDelete('cascade');
            $table->date('attendance_date');
            $table->enum('instructor_sign', ['signed', 'not_signed']);
            $table->enum('student_sign', ['signed', 'not_signed']);
            $table->timestamps();
        });

        // Orders table (created last due to references)
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_id')->unique();
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->integer('amount');
            $table->integer('final_amount');
            $table->enum('status', ['pending', 'success', 'expired']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order to avoid foreign key constraints issues
        Schema::dropIfExists('orders');
        Schema::dropIfExists('attendance');
        Schema::dropIfExists('instructors');
        Schema::dropIfExists('schedules');
        Schema::dropIfExists('student_courses');
        Schema::dropIfExists('cars');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('students');
    }
};

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
    // First ensure both tables exist
    if (Schema::hasTable('courses') && Schema::hasTable('teachers')) {
      // Drop the table if it exists to avoid duplicate
      Schema::dropIfExists('course_teacher');

      // Recreate the pivot table
      Schema::create('course_teacher', function (Blueprint $table) {
        $table->id();
        $table->foreignId('course_id')->constrained()->onDelete('cascade');
        $table->foreignId('teacher_id')->constrained()->onDelete('cascade');
        $table->timestamps();
      });
    }
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('course_teacher');
  }
};

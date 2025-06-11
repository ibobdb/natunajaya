<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Add attendance tracking fields for student and instructor
            $table->boolean('att_student')->default(false)->after('status');
            $table->boolean('att_instructor')->default(false)->after('att_student');

            // Update status enum to include 'complete' status
            DB::statement("ALTER TABLE schedules MODIFY COLUMN status ENUM(
                'waiting_approval', 
                'waiting_instructor_approval', 
                'waiting_admin_approval', 
                'date_not_set', 
                'ready',
                'on_schedule',
                'waiting_signature', 
                'complete'
            ) DEFAULT 'waiting_approval'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Drop attendance tracking fields
            $table->dropColumn(['att_student', 'att_instructor']);

            // Revert status enum to previous definition
            DB::statement("ALTER TABLE schedules MODIFY COLUMN status ENUM(
                'waiting_approval', 
                'waiting_instructor_approval', 
                'waiting_admin_approval', 
                'date_not_set', 
                'ready'
            ) DEFAULT 'waiting_approval'");
        });
    }
};

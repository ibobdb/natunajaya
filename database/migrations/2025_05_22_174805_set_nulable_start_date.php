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
            // Modify the start_date column to be nullable
            $table->dateTime('start_date')->nullable()->change();

            // Add new enum status column with default value
            $table->enum('status', [
                'waiting_approval',
                'waiting_instructor_approval',
                'waiting_admin_approval',
                'date_not_set',
                'ready'
            ])->default('waiting_approval')->after('for_session');

            // Add description column (nullable text)
            $table->text('description')->nullable()->after('status');

            // Add approval columns
            $table->boolean('instructor_approval')->default(false)->after('description');
            $table->boolean('admin_approval')->default(false)->after('instructor_approval');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('schedules', function (Blueprint $table) {
            // Make start_date required again
            $table->dateTime('start_date')->nullable(false)->change();

            // Drop all the new columns
            $table->dropColumn([
                'status',
                'description',
                'instructor_approval',
                'admin_approval'
            ]);
        });
    }
};

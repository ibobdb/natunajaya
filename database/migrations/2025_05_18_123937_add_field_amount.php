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
            $table->decimal('amount', 10, 2)->nullable()->after('status');
            $table->decimal('final_amount', 10, 2)->nullable()->after('amount');
            $table->date('start_date')->nullable()->after('final_amount');
            $table->integer('duration')->nullable()->after('start_date');
            $table->date('end_date')->nullable()->after('duration');
            $table->foreignId('teacher_id')->nullable()->after('end_date')->constrained();
            $table->foreignId('course_id')->nullable()->after('teacher_id')->constrained();
            // Drop schedule_id if it exists
            if (Schema::hasColumn('orders', 'schedule_id')) {
                $table->dropForeign(['schedule_id']);
                $table->dropColumn('schedule_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['teacher_id', 'course_id']);
            $table->dropColumn(['amount', 'final_amount', 'start_date', 'duration', 'end_date', 'teacher_id', 'course_id']);
            // Recreate schedule_id column if needed
            // $table->foreignId('schedule_id')->nullable()->constrained();
        });
    }
};

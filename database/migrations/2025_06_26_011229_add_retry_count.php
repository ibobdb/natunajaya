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
        // Create notification_queue_retries table to track retry attempts
        Schema::create('notification_queue_retries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_id')->constrained('notification_queue')->onDelete('cascade');
            $table->integer('retry_count')->default(0);
            $table->timestamp('retry_at')->useCurrent();
            $table->timestamps();
        });

        // Add retry_count column directly to notification_queue table for easier queries
        Schema::table('notification_queue', function (Blueprint $table) {
            $table->integer('retry_count')->default(0)->after('status');
            $table->integer('max_retries')->default(3)->after('retry_count');
            $table->timestamp('last_retry_at')->nullable()->after('max_retries');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the retry tracking table
        Schema::dropIfExists('notification_queue_retries');

        // Remove retry columns from notification_queue table
        Schema::table('notification_queue', function (Blueprint $table) {
            $table->dropColumn(['retry_count', 'max_retries', 'last_retry_at']);
        });
    }
};

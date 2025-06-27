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
        Schema::create('notification_queue', function (Blueprint $table) {
            $table->id();
            $table->string('channel')->comment('Communication channel for notification');
            $table->string('type')->comment('Type of notification');
            $table->longText('content')->comment('Content of the notification');
            $table->string('recipient')->comment('Recipient of the notification');
            $table->string('status')->default('0');
            $table->datetime('send_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_queue');
    }
};

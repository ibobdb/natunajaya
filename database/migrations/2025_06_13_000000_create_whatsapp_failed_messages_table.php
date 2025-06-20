<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWhatsappFailedMessagesTable extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('whatsapp_failed_messages', function (Blueprint $table) {
      $table->id();
      $table->string('recipient_number');
      $table->string('recipient_name')->nullable();
      $table->text('content');
      $table->text('error_message')->nullable();
      $table->integer('retry_count')->default(0);
      $table->timestamp('last_retry_at')->nullable();
      $table->string('status')->default('failed');
      $table->timestamps();
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('whatsapp_failed_messages');
  }
};

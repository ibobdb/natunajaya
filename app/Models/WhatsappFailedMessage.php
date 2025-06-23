<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WhatsappFailedMessage extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'recipient_number',
    'recipient_name',
    'content',
    'error_message',
    'retry_count',
    'last_retry_at',
    'status',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'last_retry_at' => 'datetime',
  ];

  /**
   * Scope for failed messages that should be retried
   */
  public function scopeRetryable($query, $maxRetries = 3)
  {
    return $query->where('retry_count', '<', $maxRetries)
      ->where('status', '!=', 'success')
      ->where(function ($q) {
        $q->whereNull('last_retry_at')
          ->orWhere('last_retry_at', '<', now()->subMinutes(15));
      });
  }
}

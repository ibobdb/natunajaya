<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
  use HasFactory;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'invoice_id',
    'student_id',
    'course_id',  // Added missing course_id
    'amount',
    'final_amount',
    'status',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'amount' => 'integer',
    'final_amount' => 'integer',
  ];

  // Modified boot method to include default values for amount and final_amount
  protected static function boot()
  {
    parent::boot();

    static::creating(function ($order) {
      if (empty($order->invoice_id)) {
        $order->invoice_id = 'INV-' . time() . '-' . rand(1000, 9999);
      }

      if (empty($order->status)) {
        $order->status = 'pending';
      }

      // Set default amount and final_amount if not provided
      if (empty($order->amount) && !empty($order->course_id)) {
        $course = Course::find($order->course_id);
        if ($course) {
          $order->amount = $course->price ?? 0;
          $order->final_amount = $course->price ?? 0;
        } else {
          // Set default values if course not found
          $order->amount = 0;
          $order->final_amount = 0;
        }
      }

      // Ensure final_amount is set if only amount is provided
      if (empty($order->final_amount) && !empty($order->amount)) {
        $order->final_amount = $order->amount;
      }
    });
  }

  /**
   * Get the student that owns the order.
   */
  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }

  public function course(): BelongsTo
  {
    return $this->belongsTo(Course::class);
  }
}

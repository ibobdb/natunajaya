<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'invoice_id',
    'student_id',
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

  /**
   * Get the student that owns the order.
   */
  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
  use HasFactory;

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'orders';

  /**
   * Indicates if the model should be timestamped.
   *
   * @var bool
   */
  public $timestamps = false;

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'invoice_id',
    'student_id',
    'course_id',
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
    'created_at' => 'datetime',
  ];

  /**
   * Get the student that owns the order.
   */
  public function student()
  {
    return $this->belongsTo(Student::class);
  }

  public function course()
  {
    return $this->belongsTo(Course::class);
  }
}

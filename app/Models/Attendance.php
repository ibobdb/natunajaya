<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'attendance';

  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'schedule_id',
    'attendance_date',
    'instructor_sign',
    'student_sign',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'attendance_date' => 'date',
  ];

  /**
   * Get the schedule that owns the attendance.
   */
  public function schedule(): BelongsTo
  {
    return $this->belongsTo(Schedule::class);
  }
}

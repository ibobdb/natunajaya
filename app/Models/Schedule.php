<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schedule extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'student_course_id',
    'for_session',
    'start_date',
    'duration_session',
    'status',
    'description',
    'instructor_approval',
    'admin_approval',
    'car_id',
    'instructor_id',
    'att_student',
    'att_instructor'
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'start_date' => 'datetime',
    'for_session' => 'integer',
    'att_student' => 'boolean',
    'att_instructor' => 'boolean',
    'instructor_approval' => 'boolean',
    'admin_approval' => 'boolean',
  ];

  /**
   * Get the student course associated with this schedule.
   */
  public function studentCourse(): BelongsTo
  {
    return $this->belongsTo(StudentCourse::class);
  }

  /**
   * Get the attendance records for this schedule.
   */
  public function attendances(): HasMany
  {
    return $this->hasMany(Attendance::class);
  }

  /**
   * Get the car associated with this schedule.
   */
  public function car(): BelongsTo
  {
    return $this->belongsTo(Car::class);
  }

  /**
   * Get the instructor associated with this schedule.
   */
  public function instructor(): BelongsTo
  {
    return $this->belongsTo(Instructor::class);
  }

  /**
   * Get the student associated with this schedule through studentCourse.
   * 
   * @return \App\Models\Student|null
   */
  public function getStudentAttribute()
  {
    return $this->studentCourse->student ?? null;
  }
}

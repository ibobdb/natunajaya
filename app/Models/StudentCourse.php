<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentCourse extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'course_id',
    'student_id',
    'status',
    'active_on',
    'score',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
    'active_on' => 'date',
    'score' => 'integer',
  ];

  /**
   * Get the course associated with this student course.
   */
  public function course(): BelongsTo
  {
    return $this->belongsTo(Course::class);
  }

  /**
   * Get the student associated with this student course.
   */
  public function student(): BelongsTo
  {
    return $this->belongsTo(Student::class);
  }

  /**
   * Get the schedules for this student course.
   */
  public function schedules(): HasMany
  {
    return $this->hasMany(Schedule::class);
  }
}

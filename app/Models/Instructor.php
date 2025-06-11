<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Instructor extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'user_id',
    'name',
    'ongoing_course',
    'ongoing_sch'

  ];

  /**
   * Get the user associated with the instructor.
   */  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the schedules associated with this instructor.
   */
  public function schedules(): HasMany
  {
    return $this->hasMany(Schedule::class);
  }

  /**
   * Get the student courses associated with this instructor.
   */
  public function studentCourses(): HasMany
  {
    return $this->hasMany(StudentCourse::class);
  }
}

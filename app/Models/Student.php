<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
  /**
   * The attributes that are mass assignable.
   *
   * @var array
   */
  protected $fillable = [
    'user_id',
    'name',
  ];

  /**
   * Get the user that owns the student.
   */
  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  /**
   * Get the student courses for the student.
   */
  public function studentCourses(): HasMany
  {
    return $this->hasMany(StudentCourse::class);
  }

  /**
   * Get the courses enrolled by this student.
   */
  public function courses()
  {
    return $this->belongsToMany(Course::class, 'student_courses')
      ->withPivot(['status', 'active_on', 'score'])
      ->withTimestamps();
  }

  /**
   * Get the orders for the student.
   */
  public function orders(): HasMany
  {
    return $this->hasMany(Order::class);
  }
}

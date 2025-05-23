<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'session',
        'duration_session',
        'duration',
        'price',
        'expired',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'expired' => 'date',
        'session' => 'integer',
        'duration' => 'integer',
        'price' => 'integer',
    ];

    /**
     * Get the student courses associated with this course.
     */
    public function studentCourses(): HasMany
    {
        return $this->hasMany(StudentCourse::class);
    }

    /**
     * Get the students enrolled in this course.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_courses')
            ->withPivot(['status', 'active_on', 'score'])
            ->withTimestamps();
    }
}

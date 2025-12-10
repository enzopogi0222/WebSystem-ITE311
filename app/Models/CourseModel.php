<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table      = 'courses';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'title',
        'course_code',
        'year_level',
        'instructor_id',
        'description',
        'created_at',
        'updated_at',
        'starting_date',
        'end_date',
        'start_time',
        'end_time',
        'is_archive',
        'school_year',
        'semester',
        'course_type',
    ];
}

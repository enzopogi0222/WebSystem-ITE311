<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table = 'enrollments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'course_id', 'enrolled_at'];
    protected $useTimestamps = false;

    public function enrollUser($data)
    {
        return $this->insert($data);
    }

    public function getUserEnrollments($user_id)
    {
        return $this->select('enrollments.*, courses.title, enrollments.enrolled_at as enrollment_date')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->where('enrollments.user_id', $user_id)
                    ->findAll();
    }

    public function isAlreadyEnrolled($user_id, $course_id)
    {
        return $this->where('user_id', $user_id)
                    ->where('course_id', $course_id)
                    ->first() !== null;
    }

    public function getAvailableCourses($user_id)
    {
        $enrolledCourseIds = $this->select('course_id')
                                  ->where('user_id', $user_id)
                                  ->findAll();

        $enrolledIds = array_column($enrolledCourseIds, 'course_id');

        $db = \Config\Database::connect();
        $builder = $db->table('courses');

        if (!empty($enrolledIds)) {
            $builder->whereNotIn('id', $enrolledIds);
        }

        return $builder->get()->getResultArray();
    }
}

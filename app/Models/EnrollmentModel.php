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
        $today = date('Y-m-d');

        return $this->select('enrollments.*, courses.title, enrollments.enrolled_at as enrollment_date')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->where('enrollments.user_id', $user_id)
                    // Only show active, non-archived courses to the student dashboard
                    ->where('courses.is_archive', 0)
                    // Respect course date window: starting_date <= today (or NULL), end_date > today (or NULL)
                    ->groupStart()
                        ->groupStart()
                            ->where('courses.starting_date <=', $today)
                            ->orWhere('courses.starting_date IS NULL', null, false)
                        ->groupEnd()
                        ->groupStart()
                            ->where('courses.end_date >', $today)
                            ->orWhere('courses.end_date IS NULL', null, false)
                        ->groupEnd()
                    ->groupEnd()
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

        $today = date('Y-m-d');
        $builder->where('is_archive', 0)
                // Only offer courses that are currently active in their date window
                ->groupStart()
                    ->groupStart()
                        ->where('starting_date <=', $today)
                        ->orWhere('starting_date IS NULL', null, false)
                    ->groupEnd()
                    ->groupStart()
                        ->where('end_date >', $today)
                        ->orWhere('end_date IS NULL', null, false)
                    ->groupEnd()
                ->groupEnd();

        return $builder->get()->getResultArray();
    }
}

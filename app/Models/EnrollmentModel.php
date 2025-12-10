<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table = 'enrollments';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'course_id', 'enrolled_at', 'status'];
    protected $useTimestamps = false;

    public function enrollUser($data)
    {
        return $this->insert($data);
    }

    public function getUserEnrollments($user_id)
    {
        // Show all approved enrollments regardless of course dates
        // Students should see courses they're enrolled in even if dates have passed
        return $this->select('enrollments.*, courses.title, enrollments.enrolled_at as enrollment_date')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->where('enrollments.user_id', $user_id)
                    ->where('enrollments.status', 'approved') // Only show approved enrollments
                    ->where('courses.is_archive', 0) // Exclude archived courses
                    ->orderBy('enrollments.enrolled_at', 'DESC')
                    ->findAll();
    }

    public function isAlreadyEnrolled($user_id, $course_id)
    {
        return $this->where('user_id', $user_id)
                    ->where('course_id', $course_id)
                    ->whereIn('status', ['pending', 'approved']) // Check for pending or approved
                    ->first() !== null;
    }

    public function getAvailableCourses($user_id)
    {
        $enrolledCourseIds = $this->select('course_id')
                                  ->where('user_id', $user_id)
                                  ->whereIn('status', ['pending', 'approved']) // Exclude courses with pending or approved enrollment
                                  ->findAll();

        $enrolledIds = array_column($enrolledCourseIds, 'course_id');

        $db = \Config\Database::connect();
        $builder = $db->table('courses');

        if (!empty($enrolledIds)) {
            $builder->whereNotIn('id', $enrolledIds);
        }

        $today = date('Y-m-d');
        $builder->where('is_archive', 0)
                // Show courses that are available:
                // - Not archived
                // - Either no end_date restriction OR end_date is in the future
                ->groupStart()
                    ->where('end_date IS NULL', null, false)
                    ->orWhere('end_date', '0000-00-00')
                    ->orWhere('end_date >', $today)
                ->groupEnd()
                ->orderBy('title', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getEnrolledStudents($course_id)
    {
        return $this->select('enrollments.*, users.id as user_id, users.name, users.email, enrollments.enrolled_at, enrollments.status')
                    ->join('users', 'users.id = enrollments.user_id')
                    ->where('enrollments.course_id', $course_id)
                    ->where('users.role', 'student')
                    ->where('enrollments.status', 'approved') // Only show approved enrollments
                    ->orderBy('enrollments.enrolled_at', 'DESC')
                    ->findAll();
    }

    public function getPendingEnrollments($course_id)
    {
        return $this->select('enrollments.*, users.id as user_id, users.name, users.email, enrollments.enrolled_at, enrollments.status')
                    ->join('users', 'users.id = enrollments.user_id')
                    ->where('enrollments.course_id', $course_id)
                    ->where('users.role', 'student')
                    ->where('enrollments.status', 'pending')
                    ->orderBy('enrollments.enrolled_at', 'DESC')
                    ->findAll();
    }

    public function approveEnrollment($enrollment_id)
    {
        return $this->update($enrollment_id, [
            'status' => 'approved',
            'enrolled_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function rejectEnrollment($enrollment_id)
    {
        return $this->update($enrollment_id, [
            'status' => 'rejected'
        ]);
    }

    public function removeEnrollment($user_id, $course_id)
    {
        return $this->where('user_id', $user_id)
                    ->where('course_id', $course_id)
                    ->delete();
    }

    public function getPendingEnrollmentsCountForTeacher($teacher_id)
    {
        return $this->select('enrollments.id')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->where('courses.instructor_id', $teacher_id)
                    ->where('enrollments.status', 'pending')
                    ->countAllResults();
    }

    public function getPendingEnrollmentsForTeacher($teacher_id)
    {
        return $this->select('enrollments.*, courses.title as course_title, courses.id as course_id, users.name as student_name, users.id as student_id, enrollments.enrolled_at as requested_at')
                    ->join('courses', 'courses.id = enrollments.course_id')
                    ->join('users', 'users.id = enrollments.user_id')
                    ->where('courses.instructor_id', $teacher_id)
                    ->where('enrollments.status', 'pending')
                    ->where('users.role', 'student')
                    ->orderBy('enrollments.enrolled_at', 'DESC')
                    ->limit(10)
                    ->findAll();
    }
}

<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EnrollmentModel;
use App\Models\CourseModel;

class Course extends BaseController
{
    public function enroll()
    {
        try {
            $session = session();

            // Check if user is logged in
            if (!$session->get('isLoggedIn')) {
                log_message('error', 'Enrollment attempt by non-logged in user');
                return $this->response->setJSON(['success' => false, 'message' => 'Unauthorized'])->setStatusCode(401);
            }

            // Check if user is a student
            if ($session->get('role') !== 'student') {
                log_message('error', 'Enrollment attempt by non-student user: ' . $session->get('role'));
                return $this->response->setJSON(['success' => false, 'message' => 'Access denied'])->setStatusCode(403);
            }

            $courseId = $this->request->getPost('course_id');
            $userId = $session->get('userID');

            // Validate course ID
            if (!$courseId || !is_numeric($courseId)) {
                log_message('error', 'Invalid course ID provided: ' . $courseId);
                return $this->response->setJSON(['success' => false, 'message' => 'Invalid course ID'])->setStatusCode(400);
            }

            // Validate user ID
            if (!$userId) {
                log_message('error', 'No user ID in session');
                return $this->response->setJSON(['success' => false, 'message' => 'User session invalid'])->setStatusCode(400);
            }

            // Check if course exists
            $db = \Config\Database::connect();
            $course = $db->table('courses')->where('id', $courseId)->get()->getRow();
            if (!$course) {
                log_message('error', 'Course not found: ' . $courseId);
                return $this->response->setJSON(['success' => false, 'message' => 'Course does not exist'])->setStatusCode(400);
            }

            $enrollmentModel = new EnrollmentModel();

            // Check if already enrolled
            if ($enrollmentModel->isAlreadyEnrolled($userId, $courseId)) {
                log_message('info', 'User ' . $userId . ' already enrolled in course ' . $courseId);
                return $this->response->setJSON(['success' => false, 'message' => 'Already enrolled in this course']);
            }

            // Prepare enrollment data
            $data = [
                'user_id' => $userId,
                'course_id' => $courseId,
                'enrolled_at' => date('Y-m-d H:i:s')
            ];

            // Attempt enrollment
            if ($enrollmentModel->enrollUser($data)) {
                log_message('info', 'User ' . $userId . ' successfully enrolled in course ' . $courseId);

                // Create notification for enrollment
                $notificationModel = new \App\Models\NotificationModel();
                $courseTitle = $course->title; // Assuming $course has title
                $message = "You have successfully enrolled in the course: {$courseTitle}";
                $notificationModel->createNotification($userId, $message);

                return $this->response->setJSON(['success' => true, 'message' => 'Successfully enrolled in the course']);
            } else {
                log_message('error', 'Failed to enroll user ' . $userId . ' in course ' . $courseId);
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to enroll'])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Enrollment error: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'message' => 'Server error: ' . $e->getMessage()])->setStatusCode(500);
        }
    }

    public function search()
    {
        $searchTerm   = $this->request->getGet('search_term');
        $courseModel  = new CourseModel();

        if (!empty($searchTerm)) {
            $courseModel->like('title', $searchTerm);
            $courseModel->orLike('description', $searchTerm);
        }

        $courses = $courseModel->findAll();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($courses);
        }

        return view('courses/index', ['courses' => $courses, 'searchTerm' => $searchTerm, ]);
    }
}

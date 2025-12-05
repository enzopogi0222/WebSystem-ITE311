<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EnrollmentModel;
use App\Models\CourseModel;

class Course extends BaseController
{
    private function ensureCourseManager()
    {
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please login to access course management pages.');
            return redirect()->to(base_url('/login'));
        }

        $role = session()->get('role');
        if (!in_array($role, ['admin', 'teacher'], true)) {
            session()->setFlashdata('error', 'You do not have permission to manage courses.');
            return redirect()->to(base_url('/dashboard'));
        }

        return null;
    }

    public function manage()
    {
        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        $courseModel = new CourseModel();
        $courses = $courseModel->findAll();

        return view('courses/list', [
            'courses' => $courses,
        ]);
    }

    public function create()
    {
        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        return view('courses/create');
    }

    public function store()
    {
        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        $validationRules = [
            'title'         => 'required|min_length[3]|max_length[150]',
            'description'   => 'permit_empty|max_length[1000]',
            'starting_date' => 'permit_empty|valid_date',
            'end_date'      => 'permit_empty|valid_date',
        ];

        if (! $this->validate($validationRules)) {
            return view('courses/create', [
                'validation' => $this->validator,
            ]);
        }

        // Additional date window validation: starting_date and end_date must be after today
        $today        = date('Y-m-d');
        $startingDate = $this->request->getPost('starting_date');
        $endDate      = $this->request->getPost('end_date');

        $errors = [];
        if (!empty($startingDate) && substr($startingDate, 0, 10) <= $today) {
            $errors['starting_date'] = 'Start date must be after today.';
        }
        if (!empty($endDate) && substr($endDate, 0, 10) <= $today) {
            $errors['end_date'] = 'End date must be after today.';
        }

        if (!empty($errors)) {
            return view('courses/create', [
                'validation' => $this->validator->setErrors($errors),
            ]);
        }

        $courseModel = new CourseModel();
        $courseModel->save([
            'title'         => $this->request->getPost('title'),
            'description'   => $this->request->getPost('description'),
            'starting_date' => $startingDate,
            'end_date'      => $endDate,
        ]);

        session()->setFlashdata('success', 'Course created successfully.');
        return redirect()->to('/courses/manage');
    }

    public function edit($id)
    {
        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        $courseModel = new CourseModel();
        $course = $courseModel->find($id);

        if (! $course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/courses/manage');
        }

        return view('courses/edit', [
            'course'     => $course,
        ]);
    }

    public function update($id)
    {
        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        $validationRules = [
            'title'         => 'required|min_length[3]|max_length[150]',
            'description'   => 'permit_empty|max_length[1000]',
            'starting_date' => 'permit_empty|valid_date',
            'end_date'      => 'permit_empty|valid_date',
        ];

        if (! $this->validate($validationRules)) {
            $courseModel = new CourseModel();
            $course = $courseModel->find($id);

            return view('courses/edit', [
                'course'     => $course,
                'validation' => $this->validator,
            ]);
        }

        // Additional date window validation: starting_date and end_date must be after today
        $today        = date('Y-m-d');
        $startingDate = $this->request->getPost('starting_date');
        $endDate      = $this->request->getPost('end_date');

        $errors = [];
        if (!empty($startingDate) && substr($startingDate, 0, 10) <= $today) {
            $errors['starting_date'] = 'Start date must be after today.';
        }
        if (!empty($endDate) && substr($endDate, 0, 10) <= $today) {
            $errors['end_date'] = 'End date must be after today.';
        }

        if (!empty($errors)) {
            $courseModel = new CourseModel();
            $course = $courseModel->find($id);

            return view('courses/edit', [
                'course'     => $course,
                'validation' => $this->validator->setErrors($errors),
            ]);
        }

        $courseModel = new CourseModel();
        $courseModel->update($id, [
            'title'         => $this->request->getPost('title'),
            'description'   => $this->request->getPost('description'),
            'starting_date' => $startingDate,
            'end_date'      => $endDate,
        ]);

        session()->setFlashdata('success', 'Course updated successfully.');
        return redirect()->to('/courses/manage');
    }

    public function delete($id)
    {
        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        $courseModel = new CourseModel();
        $course = $courseModel->find($id);

        if (! $course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/courses/manage');
        }

        $courseModel->delete($id);

        session()->setFlashdata('success', 'Course deleted successfully.');
        return redirect()->to('/courses/manage');
    }

    public function archive($id)
    {
        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        $courseModel = new CourseModel();
        $course = $courseModel->find($id);

        if (! $course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/courses/manage');
        }

        $courseModel->update($id, [
            'end_date'   => date('Y-m-d'),
            'is_archive' => 1,
        ]);

        session()->setFlashdata('success', 'Course archived successfully.');
        return redirect()->to('/courses/manage');
    }

    public function restore($id)
    {
        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        $courseModel = new CourseModel();
        $course = $courseModel->find($id);

        if (! $course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/courses/manage');
        }

        $courseModel->update($id, [
            'end_date'   => null,
            'is_archive' => 0,
        ]);

        session()->setFlashdata('success', 'Course restored successfully.');
        return redirect()->to('/courses/manage');
    }

    public function enroll()
    {
        try {
            $session = session();
            helper('security');

            // Check if user is logged in
            if (!$session->get('isLoggedIn')) {
                log_message('error', 'Enrollment attempt by non-logged in user');
                return $this->response->setJSON([
                    'success'    => false,
                    'message'    => 'Unauthorized',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ])->setStatusCode(401);
            }

            // Check if user is a student
            if ($session->get('role') !== 'student') {
                log_message('error', 'Enrollment attempt by non-student user: ' . $session->get('role'));
                return $this->response->setJSON([
                    'success'    => false,
                    'message'    => 'Access denied',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ])->setStatusCode(403);
            }

            $courseId = $this->request->getPost('course_id');
            $userId = $session->get('userID');

            // Validate course ID
            if (!$courseId || !is_numeric($courseId)) {
                log_message('error', 'Invalid course ID provided: ' . $courseId);
                return $this->response->setJSON([
                    'success'    => false,
                    'message'    => 'Invalid course ID',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ])->setStatusCode(400);
            }

            // Validate user ID
            if (!$userId) {
                log_message('error', 'No user ID in session');
                return $this->response->setJSON([
                    'success'    => false,
                    'message'    => 'User session invalid',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ])->setStatusCode(400);
            }

            // Check if course exists
            $db = \Config\Database::connect();
            $course = $db->table('courses')->where('id', $courseId)->get()->getRow();
            if (!$course) {
                log_message('error', 'Course not found: ' . $courseId);
                return $this->response->setJSON([
                    'success'    => false,
                    'message'    => 'Course does not exist',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ])->setStatusCode(400);
            }

            // Availability rules for enrollment:
            // - course must not be archived
            // - starting_date must be today or earlier (or NULL)
            // - end_date must be in the future (or NULL)
            $today = date('Y-m-d');
            $startDate = $course->starting_date ? substr($course->starting_date, 0, 10) : null;
            $endDate   = $course->end_date ? substr($course->end_date, 0, 10) : null;
            $isArchivedFlag = property_exists($course, 'is_archive') ? (int) $course->is_archive === 1 : false;

            $tooEarly = !empty($startDate) && $startDate > $today;
            $tooLate  = !empty($endDate) && $endDate <= $today;

            if ($isArchivedFlag || $tooEarly || $tooLate) {
                log_message('info', 'Enrollment blocked for unavailable course ' . $courseId . ' for user ' . $userId);
                return $this->response->setJSON([
                    'success'    => false,
                    'message'    => 'This course is not available for enrollment at this time.',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ])->setStatusCode(403);
            }

            $enrollmentModel = new EnrollmentModel();

            // Check if already enrolled
            if ($enrollmentModel->isAlreadyEnrolled($userId, $courseId)) {
                log_message('info', 'User ' . $userId . ' already enrolled in course ' . $courseId);
                return $this->response->setJSON([
                    'success'    => false,
                    'message'    => 'Already enrolled in this course',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
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

                return $this->response->setJSON([
                    'success'    => true,
                    'message'    => 'Successfully enrolled in the course',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ]);
            } else {
                log_message('error', 'Failed to enroll user ' . $userId . ' in course ' . $courseId);
                return $this->response->setJSON([
                    'success'    => false,
                    'message'    => 'Failed to enroll',
                    'csrf_token' => csrf_token(),
                    'csrf_hash'  => csrf_hash(),
                ])->setStatusCode(500);
            }

        } catch (\Exception $e) {
            log_message('error', 'Enrollment error: ' . $e->getMessage());
            return $this->response->setJSON([
                'success'    => false,
                'message'    => 'Server error: ' . $e->getMessage(),
                'csrf_token' => csrf_token(),
                'csrf_hash'  => csrf_hash(),
            ])->setStatusCode(500);
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

        // For non-admin/teacher (including guests), hide archived courses (by flag or end_date today/past)
        $session = session();
        $role = $session->get('role');
        if (!in_array($role, ['admin', 'teacher'], true)) {
            $today = date('Y-m-d');
            $courseModel->where('is_archive', 0)
                        ->groupStart()
                            // starting_date <= today OR NULL
                            ->groupStart()
                                ->where('starting_date <=', $today)
                                ->orWhere('starting_date', null)
                            ->groupEnd()
                            // end_date > today OR NULL
                            ->groupStart()
                                ->where('end_date >', $today)
                                ->orWhere('end_date', null)
                            ->groupEnd()
                        ->groupEnd();
        }

        $courses = $courseModel->findAll();

        if ($this->request->isAJAX()) {
            return $this->response->setJSON($courses);
        }

        return view('courses/index', ['courses' => $courses, 'searchTerm' => $searchTerm, ]);
    }

 
}

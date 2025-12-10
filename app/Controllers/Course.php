<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EnrollmentModel;
use App\Models\CourseModel;
use App\Models\UserModel;
use App\Models\NotificationModel;

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

    private function ensureAdminOnly()
    {
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('/login'));
        }

        $role = session()->get('role');
        if ($role !== 'admin') {
            session()->setFlashdata('error', 'Only administrators can create courses.');
            return redirect()->to(base_url('/courses/manage'));
        }

        return null;
    }

    private function canAccessCourse($course)
    {
        $role = session()->get('role');
        $userId = session()->get('userID');
        
        // Admins can access all courses
        if ($role === 'admin') {
            return true;
        }
        
        // Teachers can only access courses assigned to them
        if ($role === 'teacher') {
            return !empty($course['instructor_id']) && (int)$course['instructor_id'] === (int)$userId;
        }
        
        return false;
    }

    public function manage()
    {
        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        $courseModel = new CourseModel();
        $role = session()->get('role');
        $userId = session()->get('userID');
        
        // Get search term if provided
        $searchTerm = $this->request->getGet('search_term');
        
        // If teacher, only show courses assigned to them
        if ($role === 'teacher') {
            $courseModel->where('instructor_id', $userId);
        }
        
        // Apply search filter if provided
        if (!empty($searchTerm)) {
            $courseModel->groupStart()
                        ->like('title', $searchTerm)
                        ->orLike('course_code', $searchTerm)
                        ->orLike('description', $searchTerm)
                        ->groupEnd();
        }
        
        $courses = $courseModel->findAll();
        
        // Get instructor names for each course
        $userModel = new UserModel();
        foreach ($courses as &$course) {
            if (!empty($course['instructor_id'])) {
                $instructor = $userModel->find($course['instructor_id']);
                $course['instructor_name'] = $instructor ? $instructor['name'] : 'N/A';
            } else {
                $course['instructor_name'] = 'Not Assigned';
            }
        }

        // Return JSON if AJAX request
        if ($this->request->isAJAX()) {
            return $this->response->setJSON($courses);
        }

        return view('courses/list', [
            'courses' => $courses,
            'searchTerm' => $searchTerm,
        ]);
    }

    public function create()
    {
        if ($redirect = $this->ensureAdminOnly()) {
            return $redirect;
        }

        // Get list of teachers for instructor selection
        $userModel = new UserModel();
        $teachers = $userModel->where('role', 'teacher')->findAll();

        return view('courses/create', [
            'teachers' => $teachers,
        ]);
    }

    public function store()
    {
        if ($redirect = $this->ensureAdminOnly()) {
            return $redirect;
        }

        $validationRules = [
            'title'         => 'required|min_length[3]|max_length[150]',
            'course_code'   => 'required|min_length[2]|max_length[50]',
            'year_level'    => 'permit_empty|max_length[20]',
            'instructor_id' => 'permit_empty|integer',
            'description'   => 'permit_empty|max_length[1000]',
            'starting_date' => 'permit_empty|valid_date',
            'end_date'      => 'required_with[semester]|valid_date',
            'start_time'    => 'permit_empty',
            'end_time'      => 'permit_empty',
            'school_year'   => 'permit_empty|max_length[20]',
            'semester'      => 'permit_empty|max_length[20]',
            'course_type'   => 'permit_empty|in_list[Major,Minor]',
        ];

        if (! $this->validate($validationRules)) {
            // Get list of teachers for instructor selection
            $userModel = new UserModel();
            $teachers = $userModel->where('role', 'teacher')->findAll();
            
            return view('courses/create', [
                'validation' => $this->validator,
                'teachers' => $teachers,
            ]);
        }

        // Additional date window validation: starting_date and end_date must be after today
        $today        = date('Y-m-d');
        $startingDate = $this->request->getPost('starting_date');
        $endDate      = $this->request->getPost('end_date');

        $errors = [];
        if (!empty($startingDate) && substr($startingDate, 0, 10) < $today) {
            $errors['starting_date'] = 'Start date must not be in the past.';
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
        $title = $this->request->getPost('title');
        $courseCode = $this->request->getPost('course_code');
        $startTime = $this->request->getPost('start_time');
        $endTime = $this->request->getPost('end_time');
        
        // Check for duplicate course code (case-insensitive)
        $allCourses = $courseModel->findAll();
        $userModel = new UserModel();
        $teachers = $userModel->where('role', 'teacher')->findAll();
        foreach ($allCourses as $course) {
            if (isset($course['course_code']) && strcasecmp($course['course_code'], $courseCode) === 0) {
                return view('courses/create', [
                    'validation' => $this->validator->setError('course_code', 'A course with this code already exists.'),
                    'teachers' => $teachers,
                ]);
            }
        }

        // Check for duplicate course name with same time
        if (!empty($startTime) && !empty($endTime)) {
            foreach ($allCourses as $course) {
                if (strcasecmp($course['title'], $title) === 0) {
                    $existingStartTime = $course['start_time'] ?? '';
                    $existingEndTime = $course['end_time'] ?? '';
                    
                    // Normalize times for comparison (handle NULL/empty)
                    $existingStartTime = empty($existingStartTime) ? '' : substr($existingStartTime, 0, 5);
                    $existingEndTime = empty($existingEndTime) ? '' : substr($existingEndTime, 0, 5);
                    $newStartTime = substr($startTime, 0, 5);
                    $newEndTime = substr($endTime, 0, 5);
                    
                    if ($existingStartTime === $newStartTime && $existingEndTime === $newEndTime) {
                        return view('courses/create', [
                            'validation' => $this->validator->setError('title', 'A course with this name and time already exists.'),
                            'teachers' => $teachers,
                        ]);
                    }
                }
            }
        }

        $instructorId = $this->request->getPost('instructor_id');
        $instructorIdValue = !empty($instructorId) ? (int)$instructorId : null;
        
        $courseModel->save([
            'title'         => $title,
            'course_code'   => $courseCode,
            'year_level'    => $this->request->getPost('year_level'),
            'instructor_id' => $instructorIdValue,
            'description'   => $this->request->getPost('description'),
            'starting_date' => $startingDate,
            'end_date'      => $endDate,
            'start_time'    => $this->request->getPost('start_time'),
            'end_time'      => $this->request->getPost('end_time'),
            'school_year'   => $this->request->getPost('school_year'),
            'semester'      => $this->request->getPost('semester'),
            'course_type'   => $this->request->getPost('course_type'),
        ]);

        $notificationModel = new NotificationModel();
        
        // Notify teacher if assigned to the course
        if (!empty($instructorIdValue)) {
            $message = "You have been assigned as instructor for the course: {$title}";
            $notificationModel->createNotification($instructorIdValue, $message);
        }

        // Notify all admins about the new course
        $adminId = session()->get('userID');
        $admins = $userModel->where('role', 'admin')->findAll();
        foreach ($admins as $admin) {
            $adminMessage = "📚 New Course Created: Course '{$title}' ({$courseCode}) has been created successfully.";
            $notificationModel->createNotification($admin['id'], $adminMessage);
        }

        session()->setFlashdata('success', 'Course created successfully.');
        return redirect()->to('/courses/manage');
    }

    public function show($id)
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

        // Check if teacher can access this course
        if (!$this->canAccessCourse($course)) {
            session()->setFlashdata('error', 'You do not have permission to access this course.');
            return redirect()->to('/courses/manage');
        }

        // Get instructor name for display
        $userModel = new UserModel();
        $instructorName = 'Not Assigned';
        if (!empty($course['instructor_id'])) {
            $instructor = $userModel->find($course['instructor_id']);
            $instructorName = $instructor ? $instructor['name'] : 'N/A';
        }
        $course['instructor_name'] = $instructorName;

        // Format dates and times for display
        $startingDate = !empty($course['starting_date']) ? date('F d, Y', strtotime($course['starting_date'])) : 'Not set';
        $endDate = !empty($course['end_date']) ? date('F d, Y', strtotime($course['end_date'])) : 'Not set';
        $startTime = !empty($course['start_time']) ? date('h:i A', strtotime($course['start_time'])) : 'Not set';
        $endTime = !empty($course['end_time']) ? date('h:i A', strtotime($course['end_time'])) : 'Not set';

        // Determine status
        $today = date('Y-m-d');
        $isFlagArchived = !empty($course['is_archive']) && (int) $course['is_archive'] === 1;
        $endDateRaw = $course['end_date'] ?? null;
        $endDateDateOnly = $endDateRaw ? substr($endDateRaw, 0, 10) : null;
        $isDateExpired = !empty($endDateDateOnly)
                        && $endDateDateOnly !== '0000-00-00'
                        && $endDateDateOnly <= $today;
        $isArchived = $isFlagArchived || $isDateExpired;

        // Check if user can edit (admin or teacher assigned to course)
        $role = session()->get('role');
        $canEdit = ($role === 'admin') || ($role === 'teacher' && $this->canAccessCourse($course));

        return view('courses/show', [
            'course' => $course,
            'startingDate' => $startingDate,
            'endDate' => $endDate,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'isArchived' => $isArchived,
            'canEdit' => $canEdit,
        ]);
    }

    public function students($id)
    {
        // Only teachers can access enrolled students, not admins
        $role = session()->get('role');
        if ($role !== 'teacher') {
            session()->setFlashdata('error', 'Only teachers can view enrolled students.');
            return redirect()->to('/courses/manage');
        }

        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        $courseModel = new CourseModel();
        $course = $courseModel->find($id);

        if (! $course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/courses/manage');
        }

        // Check if teacher can access this course
        if (!$this->canAccessCourse($course)) {
            session()->setFlashdata('error', 'You do not have permission to access this course.');
            return redirect()->to('/courses/manage');
        }

        // Get enrolled students (approved)
        $enrollmentModel = new EnrollmentModel();
        $students = $enrollmentModel->getEnrolledStudents($id);
        
        // Get pending enrollment requests
        $pendingEnrollments = $enrollmentModel->getPendingEnrollments($id);

        // Get instructor name for display
        $userModel = new UserModel();
        $instructorName = 'Not Assigned';
        if (!empty($course['instructor_id'])) {
            $instructor = $userModel->find($course['instructor_id']);
            $instructorName = $instructor ? $instructor['name'] : 'N/A';
        }
        $course['instructor_name'] = $instructorName;

        return view('courses/students', [
            'course' => $course,
            'students' => $students,
            'pendingEnrollments' => $pendingEnrollments,
        ]);
    }

    public function removeStudent($courseId, $userId)
    {
        // Only teachers can remove students, not admins
        $role = session()->get('role');
        if ($role !== 'teacher') {
            session()->setFlashdata('error', 'Only teachers can remove students from courses.');
            return redirect()->to('/courses/manage');
        }

        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        $courseModel = new CourseModel();
        $course = $courseModel->find($courseId);

        if (! $course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/courses/manage');
        }

        // Check if teacher can access this course
        if (!$this->canAccessCourse($course)) {
            session()->setFlashdata('error', 'You do not have permission to access this course.');
            return redirect()->to('/courses/manage');
        }

        // Validate user ID
        if (!$userId || !is_numeric($userId)) {
            session()->setFlashdata('error', 'Invalid student ID.');
            return redirect()->to('/courses/manage/students/' . $courseId);
        }

        // Get user to verify they are a student
        $userModel = new UserModel();
        $user = $userModel->find($userId);
        
        if (!$user) {
            session()->setFlashdata('error', 'Student not found.');
            return redirect()->to('/courses/manage/students/' . $courseId);
        }

        if ($user['role'] !== 'student') {
            session()->setFlashdata('error', 'Only students can be removed from courses.');
            return redirect()->to('/courses/manage/students/' . $courseId);
        }

        // Remove enrollment
        $enrollmentModel = new EnrollmentModel();
        if ($enrollmentModel->removeEnrollment($userId, $courseId)) {
            session()->setFlashdata('success', 'Student has been removed from the course successfully.');
        } else {
            session()->setFlashdata('error', 'Failed to remove student from the course.');
        }

        return redirect()->to('/courses/manage/students/' . $courseId);
    }

    public function approveEnrollment($courseId, $enrollmentId)
    {
        // Only teachers can approve enrollments
        $role = session()->get('role');
        if ($role !== 'teacher') {
            session()->setFlashdata('error', 'Only teachers can approve enrollments.');
            return redirect()->to('/courses/manage');
        }

        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        $courseModel = new CourseModel();
        $course = $courseModel->find($courseId);

        if (!$course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/courses/manage');
        }

        // Check if teacher can access this course
        if (!$this->canAccessCourse($course)) {
            session()->setFlashdata('error', 'You do not have permission to access this course.');
            return redirect()->to('/courses/manage');
        }

        $enrollmentModel = new EnrollmentModel();
        $enrollment = $enrollmentModel->find($enrollmentId);

        if (!$enrollment || $enrollment['course_id'] != $courseId) {
            session()->setFlashdata('error', 'Enrollment not found.');
            return redirect()->to('/courses/manage/students/' . $courseId);
        }

        // Approve enrollment
        if ($enrollmentModel->approveEnrollment($enrollmentId)) {
            // Notify student
            $notificationModel = new NotificationModel();
            $userModel = new UserModel();
            $courseTitle = $course['title'] ?? 'Course';
            $student = $userModel->find($enrollment['user_id']);
            $studentName = $student ? ($student['name'] ?? 'Student') : 'Student';
            
            $message = "✅ Enrollment Approved: Your request for course '{$courseTitle}' has been approved! You can now access course materials.";
            $notificationModel->createNotification($enrollment['user_id'], $message);

            session()->setFlashdata('success', "Enrollment for {$studentName} has been approved.");
        } else {
            session()->setFlashdata('error', 'Failed to approve enrollment.');
        }

        return redirect()->to('/courses/manage/students/' . $courseId);
    }

    public function rejectEnrollment($courseId, $enrollmentId)
    {
        // Only teachers can reject enrollments
        $role = session()->get('role');
        if ($role !== 'teacher') {
            session()->setFlashdata('error', 'Only teachers can reject enrollments.');
            return redirect()->to('/courses/manage');
        }

        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        $courseModel = new CourseModel();
        $course = $courseModel->find($courseId);

        if (!$course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/courses/manage');
        }

        // Check if teacher can access this course
        if (!$this->canAccessCourse($course)) {
            session()->setFlashdata('error', 'You do not have permission to access this course.');
            return redirect()->to('/courses/manage');
        }

        $enrollmentModel = new EnrollmentModel();
        $enrollment = $enrollmentModel->find($enrollmentId);

        if (!$enrollment || $enrollment['course_id'] != $courseId) {
            session()->setFlashdata('error', 'Enrollment not found.');
            return redirect()->to('/courses/manage/students/' . $courseId);
        }

        // Reject enrollment
        if ($enrollmentModel->rejectEnrollment($enrollmentId)) {
            // Notify student
            $notificationModel = new NotificationModel();
            $userModel = new UserModel();
            $courseTitle = $course['title'] ?? 'Course';
            $student = $userModel->find($enrollment['user_id']);
            $studentName = $student ? ($student['name'] ?? 'Student') : 'Student';
            
            $message = "❌ Enrollment Rejected: Your request for course '{$courseTitle}' has been rejected. Please contact the instructor if you have questions.";
            $notificationModel->createNotification($enrollment['user_id'], $message);

            session()->setFlashdata('success', "Enrollment for {$studentName} has been rejected.");
        } else {
            session()->setFlashdata('error', 'Failed to reject enrollment.');
        }

        return redirect()->to('/courses/manage/students/' . $courseId);
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

        // Check if teacher can access this course
        if (!$this->canAccessCourse($course)) {
            session()->setFlashdata('error', 'You do not have permission to access this course.');
            return redirect()->to('/courses/manage');
        }

        // Get list of teachers for instructor selection
        $userModel = new UserModel();
        $teachers = $userModel->where('role', 'teacher')->findAll();
        
        // Get instructor name for display
        $instructorName = 'Not Assigned';
        if (!empty($course['instructor_id'])) {
            $instructor = $userModel->find($course['instructor_id']);
            $instructorName = $instructor ? $instructor['name'] : 'N/A';
        }
        $course['instructor_name'] = $instructorName;

        return view('courses/edit', [
            'course'     => $course,
            'teachers'  => $teachers,
        ]);
    }

    public function update($id)
    {
        if ($redirect = $this->ensureCourseManager()) {
            return $redirect;
        }

        // Check if teacher can access this course
        $courseModel = new CourseModel();
        $course = $courseModel->find($id);
        if (!$course) {
            session()->setFlashdata('error', 'Course not found.');
            return redirect()->to('/courses/manage');
        }
        if (!$this->canAccessCourse($course)) {
            session()->setFlashdata('error', 'You do not have permission to access this course.');
            return redirect()->to('/courses/manage');
        }

        $validationRules = [
            'title'         => 'required|min_length[3]|max_length[150]',
            'course_code'   => 'required|min_length[2]|max_length[50]',
            'year_level'    => 'permit_empty|max_length[20]',
            'instructor_id' => 'permit_empty|integer',
            'description'   => 'permit_empty|max_length[1000]',
            'starting_date' => 'permit_empty|valid_date',
            'end_date'      => 'required_with[semester]|valid_date',
            'start_time'    => 'permit_empty',
            'end_time'      => 'permit_empty',
            'school_year'   => 'permit_empty|max_length[20]',
            'semester'      => 'permit_empty|max_length[20]',
            'course_type'   => 'permit_empty|in_list[Major,Minor]',
        ];

        if (! $this->validate($validationRules)) {
            $courseModel = new CourseModel();
            $course = $courseModel->find($id);
            
            // Get list of teachers for instructor selection
            $userModel = new UserModel();
            $teachers = $userModel->where('role', 'teacher')->findAll();

            return view('courses/edit', [
                'course'     => $course,
                'validation' => $this->validator,
                'teachers'   => $teachers,
            ]);
        }

        // Additional date window validation: starting_date and end_date must be after today
        $today        = date('Y-m-d');
        $startingDate = $this->request->getPost('starting_date');
        $endDate      = $this->request->getPost('end_date');

        $errors = [];
        if (!empty($startingDate) && substr($startingDate, 0, 10) < $today) {
            $errors['starting_date'] = 'Start date must not be in the past.';
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
        $title = $this->request->getPost('title');
        $courseCode = $this->request->getPost('course_code');
        $startTime = $this->request->getPost('start_time');
        $endTime = $this->request->getPost('end_time');
        
        // Check for duplicate course code (case-insensitive) - exclude current course
        $allCourses = $courseModel->findAll();
        $userModel = new UserModel();
        $teachers = $userModel->where('role', 'teacher')->findAll();
        foreach ($allCourses as $courseItem) {
            if ($courseItem['id'] != $id && isset($courseItem['course_code']) && strcasecmp($courseItem['course_code'], $courseCode) === 0) {
                $course = $courseModel->find($id);
                return view('courses/edit', [
                    'course'     => $course,
                    'validation' => $this->validator->setError('course_code', 'A course with this code already exists.'),
                    'teachers'   => $teachers,
                ]);
            }
        }

        // Check for duplicate course name with same time - exclude current course
        if (!empty($startTime) && !empty($endTime)) {
            foreach ($allCourses as $courseItem) {
                if ($courseItem['id'] != $id && strcasecmp($courseItem['title'], $title) === 0) {
                    $existingStartTime = $courseItem['start_time'] ?? '';
                    $existingEndTime = $courseItem['end_time'] ?? '';
                    
                    // Normalize times for comparison (handle NULL/empty)
                    $existingStartTime = empty($existingStartTime) ? '' : substr($existingStartTime, 0, 5);
                    $existingEndTime = empty($existingEndTime) ? '' : substr($existingEndTime, 0, 5);
                    $newStartTime = substr($startTime, 0, 5);
                    $newEndTime = substr($endTime, 0, 5);
                    
                    if ($existingStartTime === $newStartTime && $existingEndTime === $newEndTime) {
                        $course = $courseModel->find($id);
                        return view('courses/edit', [
                            'course'     => $course,
                            'validation' => $this->validator->setError('title', 'A course with this name and time already exists.'),
                            'teachers'   => $teachers,
                        ]);
                    }
                }
            }
        }

        $role = session()->get('role');
        
        // Get old instructor before update
        $oldInstructorId = $course['instructor_id'] ?? null;
        
        $updateData = [
            'title'         => $title,
            'course_code'   => $courseCode,
            'year_level'    => $this->request->getPost('year_level'),
            'description'   => $this->request->getPost('description'),
            'starting_date' => $startingDate,
            'end_date'      => $endDate,
            'start_time'    => $this->request->getPost('start_time'),
            'end_time'      => $this->request->getPost('end_time'),
            'school_year'   => $this->request->getPost('school_year'),
            'semester'      => $this->request->getPost('semester'),
            'course_type'   => $this->request->getPost('course_type'),
        ];
        
        // Only admins can change instructor assignment
        $newInstructorId = null;
        if ($role === 'admin') {
            $instructorId = $this->request->getPost('instructor_id');
            $newInstructorId = !empty($instructorId) ? (int)$instructorId : null;
            $updateData['instructor_id'] = $newInstructorId;
        }
        
        $courseModel->update($id, $updateData);

        // Notify teacher if instructor assignment changed
        if ($role === 'admin') {
            $notificationModel = new NotificationModel();
            
            // If teacher was removed
            if (!empty($oldInstructorId) && $oldInstructorId != $newInstructorId) {
                $message = "You have been removed as instructor from the course: {$title}";
                $notificationModel->createNotification($oldInstructorId, $message);
            }
            
            // If new teacher was assigned
            if (!empty($newInstructorId) && $newInstructorId != $oldInstructorId) {
                $message = "You have been assigned as instructor for the course: {$title}";
                $notificationModel->createNotification($newInstructorId, $message);
            }
        }

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

        // Check if teacher can access this course
        if (!$this->canAccessCourse($course)) {
            session()->setFlashdata('error', 'You do not have permission to delete this course.');
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

        // Check if teacher can access this course
        if (!$this->canAccessCourse($course)) {
            session()->setFlashdata('error', 'You do not have permission to archive this course.');
            return redirect()->to('/courses/manage');
        }

        $courseModel->update($id, [
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

        // Check if teacher can access this course
        if (!$this->canAccessCourse($course)) {
            session()->setFlashdata('error', 'You do not have permission to restore this course.');
            return redirect()->to('/courses/manage');
        }

        $courseModel->update($id, [
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
            // - end_date must be in the future (or NULL)
            $today = date('Y-m-d');
            $endDate   = $course->end_date ? substr($course->end_date, 0, 10) : null;
            if ($endDate === '0000-00-00') {
                $endDate = null;
            }
            $isArchivedFlag = property_exists($course, 'is_archive') ? (int) $course->is_archive === 1 : false;

            $tooLate  = !empty($endDate) && $endDate <= $today;

            if ($isArchivedFlag || $tooLate) {
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

            // Prepare enrollment data (status: pending - requires teacher approval)
            $data = [
                'user_id' => $userId,
                'course_id' => $courseId,
                'status' => 'pending',
                'enrolled_at' => date('Y-m-d H:i:s')
            ];

            // Attempt enrollment request
            if ($enrollmentModel->enrollUser($data)) {
                log_message('info', 'User ' . $userId . ' requested enrollment in course ' . $courseId);

                // Create notification for student enrollment request
                $notificationModel = new NotificationModel();
                $userModel = new UserModel();
                $courseTitle = $course->title ?? 'Course';
                
                // Notify the student
                $message = "⏳ Enrollment Request Submitted: Your request for course '{$courseTitle}' is pending teacher approval. You will be notified once reviewed.";
                $notificationModel->createNotification($userId, $message);

                // Notify the teacher when student requests enrollment
                if (!empty($course->instructor_id)) {
                    $student = $userModel->find($userId);
                    $studentName = $student ? ($student['name'] ?? 'A student') : 'A student';
                    $teacherMessage = "🔔 New Enrollment Request: {$studentName} wants to enroll in '{$courseTitle}'. Click 'Students' to review.";
                    $notificationModel->createNotification($course->instructor_id, $teacherMessage);
                }

                return $this->response->setJSON([
                    'success'    => true,
                    'message'    => 'Enrollment request submitted. Waiting for teacher approval.',
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

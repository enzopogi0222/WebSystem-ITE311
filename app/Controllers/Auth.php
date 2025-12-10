<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use App\Models\NotificationModel;
use App\Models\UserModel;

class Auth extends BaseController
{
    public function register()
    {
        $session = session();

        // Only logged-in admins can access registration
        if (!$session->get('userID') || !$session->get('isLoggedIn')) {
            $session->setFlashdata('error', 'Only admins can access the registration page.');
            return redirect()->to('/login');
        }

        if ($session->get('role') !== 'admin') {
            $session->setFlashdata('error', 'You do not have permission to create users.');
            return redirect()->to('/dashboard');
        }

        if ($this->request->getMethod() === 'POST') {
            // Set validation rules
            $rules = [
                'name'     => 'required|min_length[2]|max_length[100]',
                'email'    => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[6]|alpha_numeric',
                'password_confirm' => 'required|matches[password]',
                'role'     => 'required|in_list[admin,teacher,student]',
            ];

            if (!$this->validate($rules)) {
                return view('auth/register', ['validation' => $this->validator]);
            }

            // Get form data
            $name = $this->request->getPost('name');
            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');
            // Normalize role to lowercase without extra spaces
            $role = strtolower(trim((string) $this->request->getPost('role')));

            // Step 1: Validate Email (prevent bad format) - Additional security layer
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $session->setFlashdata('error', 'Invalid email format.');
                return view('auth/register', ['validation' => $this->validator]);
            }

            // Step 2: Validate Password (alphanumeric only, no special characters)
            if (!preg_match('/^[a-zA-Z0-9]+$/', $password)) {
                $session->setFlashdata('error', 'Password must contain only letters and numbers. No special characters allowed.');
                return view('auth/register', ['validation' => $this->validator]);
            }

            // Step 3: Protect SQL (CodeIgniter's query builder uses prepared statements automatically)
            // The insert() method uses parameterized queries internally
            $data = [
                'name' => $name,
                'email' => $email,
                'password'   => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'status' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db = \Config\Database::connect();
            $builder = $db->table('users');
            if ($builder->insert($data)) {
                // Notify all admins about the new user creation
                $notificationModel = new NotificationModel();
                $userModel = new UserModel();
                $admins = $userModel->where('role', 'admin')->findAll();
                
                $roleLabel = ucfirst($role);
                $adminMessage = "👤 New {$roleLabel} Created: {$name} ({$email}) has been added to the system.";
                
                foreach ($admins as $admin) {
                    $notificationModel->createNotification($admin['id'], $adminMessage);
                }
                
                $session->setFlashdata('success', 'User account created successfully.');
                return redirect()->to('/admin/users');
            } else {
                $session->setFlashdata('error', 'Registration failed. Please try again.');
            }
        }

        return view('auth/register');
    }

    public function login()
    {
        $db = \Config\Database::connect();
        $session = session();

        // If user is already logged in, redirect to dashboard
        if ($session->get('userID')) {
            return redirect()->to('/dashboard');
        }

        if ($this->request->getMethod() === 'POST') {
            $rules = [
                'email' => 'required|valid_email',
                'password' => 'required|alpha_numeric'
            ];

            if (!$this->validate($rules)) {
                return view('auth/login', ['validation' => $this->validator]);
            }

            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            // Step 1: Validate Email (prevent bad format) - Additional security layer
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $session->setFlashdata('error', 'Invalid email format.');
                return view('auth/login', ['validation' => $this->validator]);
            }

            // Step 2: Validate Password (alphanumeric only, no special characters)
            if (!preg_match('/^[a-zA-Z0-9]+$/', $password)) {
                $session->setFlashdata('error', 'Password must contain only letters and numbers. No special characters allowed.');
                return view('auth/login', ['validation' => $this->validator]);
            }

            // Step 3: Protect SQL (CodeIgniter's query builder uses prepared statements automatically)
            // The where() and get() methods use parameterized queries internally
            // Check if user exists
            $builder = $db->table('users');
            $user = $builder->where('email', $email)->get()->getRowArray();

            if ($user && password_verify($password, $user['password'])) {
                // Check if user is active
                if (isset($user['status']) && $user['status'] !== 'active') {
                    $session->setFlashdata('error', 'Your account is inactive. Please contact an administrator.');
                    return view('auth/login');
                }

                // Set session data
                $session->set([
                    'userID' => $user['id'],
                    'name'   => $user['name'],
                    'email'  => $user['email'],
                    'role'   => $user['role'],
                    'isLoggedIn' => true
                ]);

                // Single generic dashboard for all roles
                return redirect()->to('/dashboard');
            } else {
                $session->setFlashdata('error', 'Invalid email or password.');
                return view('auth/login');
            }
        }

        return view('auth/login');
    }

    public function logout()
    {
        $session = session();
        $session->destroy();
        $session->setFlashdata('success', 'You have been logged out successfully.');
        return redirect()->to('/login');
    }

    public function dashboard()
    {
        $session = session();
        
        // Check if user is logged in
        if (!$session->get('userID')) {
            $session->setFlashdata('error', 'Please login to access the dashboard.');
            return redirect()->to('/login');
        }

        $role = $session->get('role');

        $data = [
            'user' => [
                'id' => $session->get('userID'),
                'name' => $session->get('name'),
                'email' => $session->get('email'),
                'role' => $role,
            ],
        ];

        // If admin or teacher, load courses for dashboard display
        if ($role === 'admin' || $role === 'teacher') {
            $courseModel = new CourseModel();
            $enrollmentModel = new EnrollmentModel();
            
            if ($role === 'teacher') {
                // For teachers, only load courses assigned to them
                $userId = $session->get('userID');
                $data['courses'] = $courseModel->where('instructor_id', $userId)->findAll();
            } else {
                // For admin, load all courses
                $data['courses'] = $courseModel->findAll();
            }
            
            // Get enrolled students count for each course
            if (!empty($data['courses'])) {
                foreach ($data['courses'] as &$course) {
                    $students = $enrollmentModel->getEnrolledStudents($course['id']);
                    $course['enrolled_count'] = count($students);
                }
            }
        }

        // If student, load enrolled and available courses for the dashboard
        if ($role === 'student') {
            $enrollmentModel = new EnrollmentModel();
            $userId = $session->get('userID');

            $data['enrolledCourses']  = $enrollmentModel->getUserEnrollments($userId);
            $data['availableCourses'] = $enrollmentModel->getAvailableCourses($userId);
        }

        return view('auth/dashboard', $data);
    }
}

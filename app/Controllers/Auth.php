<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\CourseModel;
use App\Models\EnrollmentModel;

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
                'password' => 'required|min_length[6]',
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


            $data = [
                'name' => $name,
                'email' => $email,
                'password'   => password_hash($password, PASSWORD_DEFAULT),
                'role' => $role,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];

            $db = \Config\Database::connect();
            $builder = $db->table('users');
            if ($builder->insert($data)) {
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
                'password' => 'required'
            ];

            if (!$this->validate($rules)) {
                return view('auth/login', ['validation' => $this->validator]);
            }

            $email = $this->request->getPost('email');
            $password = $this->request->getPost('password');

            // Check if user exists
            $builder = $db->table('users');
            $user = $builder->where('email', $email)->get()->getRowArray();

            if ($user && password_verify($password, $user['password'])) {
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

        // If admin or teacher, load all courses for dashboard display
        if ($role === 'admin' || $role === 'teacher') {
            $courseModel = new CourseModel();
            $data['courses'] = $courseModel->findAll();
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

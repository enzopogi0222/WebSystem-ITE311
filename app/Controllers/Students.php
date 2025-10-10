<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\EnrollmentModel;

class Students extends BaseController
{
    public function dashboard()
    {
        $session = session();

        if (!$session->get('isLoggedIn')) {
            $session->setFlashdata('error', 'Please login to access your dashboard.');
            return redirect()->to(base_url('/login'));
        }

       
        if ($session->get('role') !== 'student') {
            $session->setFlashdata('error', 'Access denied. Student role required.');
            return redirect()->to(base_url('/login'));
        }

      
        $enrollmentModel = new EnrollmentModel();

        $userId = $session->get('userID');

        $enrolledCourses = $enrollmentModel->getUserEnrollments($userId);
        $availableCourses = $enrollmentModel->getAvailableCourses($userId);

        $data = [
            'user' => [
                'name'  => $session->get('name'),
                'email' => $session->get('email'),
                'role'  => $session->get('role'),
            ],
            'enrolledCourses'  => $enrolledCourses,
            'availableCourses' => $availableCourses,
        ];

        return view('auth/dashboard', $data);
    }
}

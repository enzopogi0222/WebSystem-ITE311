<?php
namespace App\Controllers;
use App\Controllers\BaseController;
use App\Models\UserModel;

class Admin extends BaseController
{
    public function dashboard()
    {
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please Login to access the admin dashboard.');
            return redirect()->to(base_url('/login'));
        }
        
        if (session()->get('role') !== 'admin') {
            session()->setFlashdata('error', 'You do not have permission to access');
            return redirect()->to(base_url('/login'));
        }

        return view('auth/dashboard', [
            'user' => [
                'name' => session()->get('name'),
                'email' => session()->get('email'),
                'role' => session()->get('role'),
            ],
        ]);
    }

    private function ensureAdmin()
    {

    if (!session()->get('isLoggedIn')) {
        session()->setFlashdata('error', 'Please login to access admin pages.');
        return redirect()->to(base_url('/login'));
    }

    if (session()->get('role') !== 'admin') {
        session()->setFlashdata('error', 'You do not have permission to access this page.');
        return redirect()->to(base_url('/dashboard'));
    }

    return null;

    }
    
    public function users()
    {

    if ($redirect = $this->ensureAdmin()) {
        return $redirect;
    
    }

    $userModel = new UserModel();
    $users = $userModel->findAll();

    return view('admin/users_list', [
        'users' => $users,
    ]);

    }

    public function createUser()
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        // Reuse the existing registration form/view for admin-created accounts
        return view('auth/register');
    }

    public function editUser($id)
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        // Validate ID is numeric to prevent SQL injection
        if (!is_numeric($id) || (int)$id <= 0) {
            session()->setFlashdata('error', 'Invalid user ID.');
            return redirect()->to('/admin/users');
        }

        $id = (int)$id;

        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to('/admin/users');
        }

        return view('admin/user_edit', ['user' => $user]);
    }

    public function updateUser($id)
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        // Validate ID is numeric to prevent SQL injection
        if (!is_numeric($id) || (int)$id <= 0) {
            session()->setFlashdata('error', 'Invalid user ID.');
            return redirect()->to('/admin/users');
        }

        $id = (int)$id;

        // Base validation rules
        $validationRules = [
            'name'  => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email,id,' . $id . ']',
            'role'  => 'required|in_list[admin,teacher,student]',
            'status' => 'required|in_list[active,inactive]'
        ];

        $password = $this->request->getPost('password');

        // If admin entered a new password, add rules for it
        if (!empty($password)) {
            $validationRules['password'] = 'required|min_length[6]|alpha_numeric';
            $validationRules['password_confirm'] = 'required|matches[password]';
        }

        if (!$this->validate($validationRules)) {
            $userModel = new UserModel();
            $user = $userModel->find($id);

            return view('admin/user_edit', [
                'user' => $user,
                'validation' => $this->validator,
            ]);
        }

        // Get form data
        $email = $this->request->getPost('email');

        // Step 1: Validate Email (prevent bad format) - Additional security layer
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            session()->setFlashdata('error', 'Invalid email format.');
            $userModel = new UserModel();
            $user = $userModel->find($id);
            return view('admin/user_edit', [
                'user' => $user,
                'validation' => $this->validator,
            ]);
        }

        // Step 2: Validate Password (alphanumeric only, no special characters) if provided
        if (!empty($password)) {
            if (!preg_match('/^[a-zA-Z0-9]+$/', $password)) {
                session()->setFlashdata('error', 'Password must contain only letters and numbers. No special characters allowed.');
                $userModel = new UserModel();
                $user = $userModel->find($id);
                return view('admin/user_edit', [
                    'user' => $user,
                    'validation' => $this->validator,
                ]);
            }
        }

        $userModel = new UserModel();

        // Step 3: Protect SQL (CodeIgniter's Model update() method uses prepared statements automatically)
        $data = [
            'name'  => $this->request->getPost('name'),
            'email' => $email,
            // Normalize role to lowercase without extra spaces
            'role'  => strtolower(trim((string) $this->request->getPost('role'))),
            'status' => strtolower(trim((string) $this->request->getPost('status'))),
        ];

        // Only update password if a new one was provided
        if (!empty($password)) {
            $data['password'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $userModel->update($id, $data);

        session()->setFlashdata('success', 'User updated successfully.');
        return redirect()->to('/admin/users');
    }

    public function deleteUser($id)
    {
        if ($redirect = $this->ensureAdmin()) {
            return $redirect;
        }

        // Validate ID is numeric to prevent SQL injection
        if (!is_numeric($id) || (int)$id <= 0) {
            session()->setFlashdata('error', 'Invalid user ID.');
            return redirect()->to('/admin/users');
        }

        $id = (int)$id;

        $userModel = new UserModel();
        $user = $userModel->find($id);

        if (!$user) {
            session()->setFlashdata('error', 'User not found.');
            return redirect()->to('/admin/users');
        }

        // Optional safety: prevent admin from deleting own account
        if ((int) session()->get('userID') === (int) $id) {
            session()->setFlashdata('error', 'You cannot delete your own account while logged in.');
            return redirect()->to('/admin/users');
        }

        $userModel->delete($id);

        session()->setFlashdata('success', 'User deleted successfully.');
        return redirect()->to('/admin/users');
    }
}
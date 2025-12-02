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
            ]
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

        // Base validation rules
        $validationRules = [
            'name'  => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email',
            'role'  => 'required|in_list[admin,teacher,student]'
        ];

        $password = $this->request->getPost('password');

        // If admin entered a new password, add rules for it
        if (!empty($password)) {
            $validationRules['password'] = 'required|min_length[6]';
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

        $userModel = new UserModel();

        $data = [
            'name'  => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'role'  => $this->request->getPost('role'),
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
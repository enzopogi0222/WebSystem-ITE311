<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\MaterialsModel;
use App\Models\EnrollmentModel;

class Materials extends BaseController
{
    protected $materialsModel;
    protected $enrollmentModel;

    public function __construct()
    {
        $this->materialsModel = new MaterialsModel();
        $this->enrollmentModel = new EnrollmentModel();
    }

   public function upload($course_id = null)
{
  
    if (!session()->get('isLoggedIn')) {
        session()->setFlashdata('error', 'Please login to access this page.');
        return redirect()->to(base_url('/login'));
    }

    
    $userRole = session()->get('role');
    if (!in_array($userRole, ['admin', 'teacher'])) {
        session()->setFlashdata('error', 'You do not have permission to upload materials.');
        return redirect()->to(base_url('/dashboard'));
    }

  
    if ($this->request->getMethod() === 'POST') {
        // Load Validation Library
        $validation = \Config\Services::validation();
      
        $validation->setRules([
            'material_file' => [
                'label' => 'Material File',
                'rules' => 'uploaded[material_file]|max_size[material_file,10240]|ext_in[material_file,pdf,doc,docx,ppt,pptx,txt,jpg,jpeg,png]'
            ]
        ]);

     
        if (!$validation->withRequest($this->request)->run()) {
            session()->setFlashdata('error', 'File validation failed: ' . implode(', ', $validation->getErrors()));
            return redirect()->back()->withInput();
        }

     
        $file = $this->request->getFile('material_file');
        
        if ($file->isValid() && !$file->hasMoved()) {
         
            $uploadPath = WRITEPATH . 'uploads/materials/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $newName = $file->getRandomName();
            
         
            if ($file->move($uploadPath, $newName)) {
             
                $data = [
                    'course_id' => $course_id,
                    'file_name' => $file->getClientName(),
                    'file_path' => 'uploads/materials/' . $newName,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                // Save to database using MaterialsModel
                if ($this->materialsModel->insertMaterial($data)) {
                    session()->setFlashdata('success', 'Material uploaded successfully!');
                } else {
                    session()->setFlashdata('error', 'Failed to save material information.');
                }
            } else {
                session()->setFlashdata('error', 'Failed to upload file.');
            }
        } else {
            session()->setFlashdata('error', 'Invalid file or file already moved.');
        }

    
        return redirect()->to(base_url('/materials/course/' . $course_id));
    }

 
    return view('materials/upload', [
        'course_id' => $course_id,
        'user' => [
            'name' => session()->get('username'),
            'role' => session()->get('role')
        ]
    ]);
}

   
    private function handleFileUpload($course_id)
    {
        $validation = \Config\Services::validation();
        
        $validation->setRules([
            'material_file' => [
                'label' => 'Material File',
                'rules' => 'uploaded[material_file]|max_size[material_file,10240]|ext_in[material_file,pdf,doc,docx,ppt,pptx,txt,jpg,jpeg,png]'
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            session()->setFlashdata('error', 'File validation failed: ' . implode(', ', $validation->getErrors()));
            return redirect()->back()->withInput();
        }

        $file = $this->request->getFile('material_file');
        
        if ($file->isValid() && !$file->hasMoved()) {
           
            $uploadPath = WRITEPATH . 'uploads/materials/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            
            $newName = $file->getRandomName();
            
            if ($file->move($uploadPath, $newName)) {
            
                $data = [
                    'course_id' => $course_id,
                    'file_name' => $file->getClientName(),
                    'file_path' => 'uploads/materials/' . $newName,
                    'created_at' => date('Y-m-d H:i:s')
                ];

                if ($this->materialsModel->insertMaterial($data)) {
                    session()->setFlashdata('success', 'Material uploaded successfully!');
                } else {
                    session()->setFlashdata('error', 'Failed to save material information.');
                }
            } else {
                session()->setFlashdata('error', 'Failed to upload file.');
            }
        } else {
            session()->setFlashdata('error', 'Invalid file or file already moved.');
        }

        return redirect()->to(base_url('/materials/course/' . $course_id));
    }

   
    public function delete($material_id)
    {
      
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('/login'));
        }

       
        $userRole = session()->get('role');
        if (!in_array($userRole, ['admin', 'teacher'])) {
            session()->setFlashdata('error', 'You do not have permission to delete materials.');
            return redirect()->back();
        }

        $material = $this->materialsModel->getMaterialById($material_id);
        
        if (!$material) {
            session()->setFlashdata('error', 'Material not found.');
            return redirect()->back();
        }

      
        $filePath = WRITEPATH . $material['file_path'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

       
        if ($this->materialsModel->deleteMaterial($material_id)) {
            session()->setFlashdata('success', 'Material deleted successfully!');
        } else {
            session()->setFlashdata('error', 'Failed to delete material.');
        }

        return redirect()->to(base_url('/materials/course/' . $material['course_id']));
    }

   
    public function download($material_id)
    {
     
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('/login'));
        }

   
        $material = $this->materialsModel->getMaterialById($material_id);
        
        if (!$material) {
            session()->setFlashdata('error', 'Material not found.');
            return redirect()->back();
        }

        $userId = session()->get('user_id');
        $userRole = session()->get('role');

       
        if ($userRole === 'student') {
            $isEnrolled = $this->enrollmentModel->isAlreadyEnrolled($userId, $material['course_id']);
            if (!$isEnrolled) {
                session()->setFlashdata('error', 'You must be enrolled in this course to download materials.');
                return redirect()->back();
            }
        }

     
        $filePath = WRITEPATH . $material['file_path'];
        if (!file_exists($filePath)) {
            session()->setFlashdata('error', 'File not found on server.');
            return redirect()->back();
        }

        return $this->response->download($filePath, null)->setFileName($material['file_name']);
    }

 
  
    public function course($course_id)
    {
        // Check if user is logged in
        if (!session()->get('isLoggedIn')) {
            session()->setFlashdata('error', 'Please login to access this page.');
            return redirect()->to(base_url('/login'));
        }

        $materials = $this->materialsModel->getMaterialsByCourse($course_id);

        return view('materials/list', [
            'materials' => $materials,
            'course_id' => $course_id,
            'user' => [
                'name' => session()->get('username'),
                'role' => session()->get('role')
            ]
        ]);
    }
}
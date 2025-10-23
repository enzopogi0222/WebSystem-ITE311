<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialsModel extends Model
{
    protected $table = 'materials';
    protected $primaryKey = 'id';
    protected $allowedFields = ['course_id', 'file_name', 'file_path', 'created_at'];
    protected $useTimestamps = false;

    /**
     * Insert a new material record
     * 
     * @param array $data Material data to insert
     * @return int|bool Insert ID on success, false on failure
     */
    public function insertMaterial($data)
    {
        return $this->insert($data);
    }

    /**
     * Get all materials for a specific course
     * 
     * @param int $course_id Course ID to get materials for
     * @return array Array of materials for the course
     */
    public function getMaterialsByCourse($course_id)
    {
        return $this->select('materials.*, courses.title as course_title')
                    ->join('courses', 'courses.id = materials.course_id')
                    ->where('materials.course_id', $course_id)
                    ->orderBy('materials.created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Get a single material by ID
     * 
     * @param int $id Material ID
     * @return array|null Material data or null if not found
     */
    public function getMaterialById($id)
    {
        return $this->select('materials.*, courses.title as course_title')
                    ->join('courses', 'courses.id = materials.course_id')
                    ->where('materials.id', $id)
                    ->first();
    }

    /**
     * Delete a material by ID
     * 
     * @param int $id Material ID to delete
     * @return bool True on success, false on failure
     */
    public function deleteMaterial($id)
    {
        return $this->delete($id);
    }
}
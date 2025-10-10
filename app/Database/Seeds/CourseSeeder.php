<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title' => 'Introduction to Programming',
                'description' => 'Learn the basics of programming using Python.',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Database Systems',
                'description' => 'Understand relational databases and SQL.',
                'created_at' => date('Y-m-d H:i:s'),
            ],
            [
                'title' => 'Web Development',
                'description' => 'Learn HTML, CSS, JavaScript, and PHP basics.',
                'created_at' => date('Y-m-d H:i:s'),
            ],
        ];

        // Insert all data
        $this->db->table('courses')->insertBatch($data);
    }
}

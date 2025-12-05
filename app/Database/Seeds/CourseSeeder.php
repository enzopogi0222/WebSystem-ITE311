<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class CourseSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        $data = [
            [
                'title'         => 'Introduction to Programming',
                'description'   => 'Learn the basics of programming using Python.',
                'created_at'    => $now,
                'updated_at'    => $now,
                'starting_date' => '2025-09-01 08:00:00',
                'end_date'      => '2025-12-15 17:00:00',
            ],
            [
                'title'         => 'Database Systems',
                'description'   => 'Understand relational databases and SQL.',
                'created_at'    => $now,
                'updated_at'    => $now,
                'starting_date' => '2025-09-02 08:00:00',
                'end_date'      => '2025-12-16 17:00:00',
            ],
            [
                'title'         => 'Web Development',
                'description'   => 'Learn HTML, CSS, JavaScript, and PHP basics.',
                'created_at'    => $now,
                'updated_at'    => $now,
                'starting_date' => '2025-09-03 08:00:00',
                'end_date'      => '2025-12-17 17:00:00',
            ],
        ];

        $this->db->table('courses')->insertBatch($data);
    }
}
<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [

                'email' => 'admin@lms.com',
                'password' => password_hash('enzo0222', PASSWORD_DEFAULT),
                'name' => 'Floro Lorenzo A. Gagni',
                'role' => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [

                'email' => 'instructor@lms.com',
                'password' => password_hash('enzo0222', PASSWORD_DEFAULT),
                'name' => 'Aj Roquero',
                'role' => 'instructor',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            [
                'email' => 'student@lms.com',
                'password' => password_hash('enzo0222', PASSWORD_DEFAULT),
                'name' => 'Zyf Diga',
                'role' => 'student',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
        ];

        $this->db->table('users')->insertBatch($data);
    }
}
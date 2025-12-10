<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusToEnrollmentsTable extends Migration
{
    public function up()
    {
        $fields = [
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['pending', 'approved', 'rejected'],
                'default' => 'pending',
                'after' => 'course_id'
            ]
        ];
        $this->forge->addColumn('enrollments', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('enrollments', 'status');
    }
}


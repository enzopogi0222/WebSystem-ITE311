<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsArchiveToCoursesTable extends Migration
{
    public function up()
    {
        $fields = [
            'is_archive' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'end_date',
            ],
        ];
        $this->forge->addColumn('courses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('courses', ['is_archive']);
    }
}

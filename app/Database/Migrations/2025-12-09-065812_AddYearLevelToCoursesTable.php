<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddYearLevelToCoursesTable extends Migration
{
    public function up()
    {
        $fields = [
            'year_level' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'course_code',
            ],
        ];
        $this->forge->addColumn('courses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('courses', ['year_level']);
    }
}

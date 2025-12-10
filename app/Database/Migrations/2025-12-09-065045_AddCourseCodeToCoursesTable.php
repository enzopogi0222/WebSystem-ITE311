<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCourseCodeToCoursesTable extends Migration
{
    public function up()
    {
        $fields = [
            'course_code' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'after' => 'title',
            ],
        ];
        $this->forge->addColumn('courses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('courses', ['course_code']);
    }
}

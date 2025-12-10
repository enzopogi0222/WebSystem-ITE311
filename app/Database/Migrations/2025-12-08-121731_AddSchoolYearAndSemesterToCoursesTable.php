<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddSchoolYearAndSemesterToCoursesTable extends Migration
{
    public function up()
    {
        $fields = [
            'school_year' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'title',
            ],
            'semester' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'school_year',
            ],
            'course_type' => [
                'type' => 'VARCHAR',
                'constraint' => 20,
                'null' => true,
                'after' => 'semester',
            ],
        ];
        $this->forge->addColumn('courses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('courses', ['school_year', 'semester', 'course_type']);
    }
}

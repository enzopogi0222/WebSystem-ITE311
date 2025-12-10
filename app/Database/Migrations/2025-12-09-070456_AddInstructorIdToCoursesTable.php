<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddInstructorIdToCoursesTable extends Migration
{
    public function up()
    {
        $fields = [
            'instructor_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
                'after' => 'year_level',
            ],
        ];
        $this->forge->addColumn('courses', $fields);
        
        // Add foreign key constraint
        $this->forge->addForeignKey('instructor_id', 'users', 'id', 'CASCADE', 'SET NULL');
    }

    public function down()
    {
        $this->forge->dropForeignKey('courses', 'courses_instructor_id_foreign');
        $this->forge->dropColumn('courses', ['instructor_id']);
    }
}

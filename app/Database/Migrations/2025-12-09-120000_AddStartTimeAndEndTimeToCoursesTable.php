<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStartTimeAndEndTimeToCoursesTable extends Migration
{
    public function up()
    {
        $fields = [
            'start_time' => [
                'type' => 'TIME',
                'null' => true,
                'after' => 'starting_date',
            ],
            'end_time' => [
                'type' => 'TIME',
                'null' => true,
                'after' => 'start_time',
            ],
        ];
        $this->forge->addColumn('courses', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('courses', ['start_time', 'end_time']);
    }
}


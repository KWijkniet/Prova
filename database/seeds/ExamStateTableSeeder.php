<?php

use Illuminate\Database\Seeder;

class ExamStateTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('exam_states')->insert([
            [
            'name' => "created"
            ],
            [
            'name' => "editing",
            ],
            [
            'name' => "done",
            ],
            [
            'name' => "confirmed",
            ],
        ]);
    }
}

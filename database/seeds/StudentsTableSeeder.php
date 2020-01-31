<?php

use Illuminate\Database\Seeder;

class StudentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('students')->insert([
            [
                'ov' => "99044419",
                'name' => "Ludo de Vries",
                'year' => "2017",
                'education_id' => "10",
                'domain_id' => "2"
            ],
            [
                'ov' => "99044891",
                'name' => "Kelvin Wijkniet",
                'year' => "2017",
                'education_id' => "10",
                'domain_id' => "2"
            ]
        ]);
    }
}

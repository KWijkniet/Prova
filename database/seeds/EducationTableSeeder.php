<?php

use Illuminate\Database\Seeder;

class EducationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('educations')->insert([
            [
                'name' => "Mediamanager",
                'domain_id' => "1"
            ],
            [
                'name' => "Mediaredactiemedewerker",
                'domain_id' => "1"
            ],
            [
                'name' => "Mediavormgever",
                'domain_id' => "1"
            ],
            [
                'name' => "Ruimtelijk vormgever",
                'domain_id' => "1"
            ],
            [
                'name' => "Allround medewerker IT systems and devices",
                'domain_id' => "2"
            ],
            [
                'name' => "Expert IT systems and devices",
                'domain_id' => "2"
            ],
            [
                'name' => "Front-End Developer",
                'domain_id' => "2"
            ],
            [
                'name' => "Medewerker ICT support",
                'domain_id' => "2"
            ],
            [
                'name' => "Smart Technology",
                'domain_id' => "2"
            ],
            [
                'name' => "Software Developer",
                'domain_id' => "2"
            ]
        ]);
    }
}

<?php

use Illuminate\Database\Seeder;

// Roles:
// 1 = CEO - Slemmer (Klant)
// 2 = SuperAdmin - Mr. de Jong (hoofd van opleiding)
// 3 = Admin - De docenten die over de examens gaan en alles regelen
// 4 = Examiner - Alle overige docenten

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            [
                'role_id' => "1",
                'username' => "CEO",
                'email' => "ceo@davinci.nl",
                'password' => bcrypt('test'),
                'isCEO' => true,
                'domain_id' => null,
                'education_id' => null
            ],
            [
                'role_id' => "2",
                'username' => "SuperAdmin",
                'email' => "superadmin@davinci.nl",
                'password' => bcrypt('test'),
                'isCEO' => false,
                'domain_id' => "2",
                'education_id' => null
            ],
            [
                'role_id' => "3",
                'username' => "Admin",
                'email' => "admin@davinci.nl",
                'password' => bcrypt('test'),
                'isCEO' => false,
                'domain_id' => "2",
                'education_id' => "10"
            ],
            [
                'role_id' => "4",
                'username' => "Jurn de Ruijter",
                'email' => "jderuijter@davinci.nl",
                'password' => bcrypt('test'),
                'isCEO' => false,
                'domain_id' => "2",
                'education_id' => "10"
            ],
            [
                'role_id' => "4",
                'username' => "Jeroen Slemmer",
                'email' => "jslemmer@davinci.nl",
                'password' => bcrypt('test'),
                'isCEO' => false,
                'domain_id' => "2",
                'education_id' => "10"
            ]
        ]);
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('companies')->insert(
            [
                [
                    'id' => 1,
                    'name' => 'Firefly Tech',
                    'slug' => 'firefly-tech',
                    'employer_id' => 1,
                    'email' => 'firefly123@yopmail.com',
                    'address' => 'Pokhara',
                    'description' => 'Firefly tech is an IT office located in Pokhara',

                ],
                [
                    'id' => 2,
                    'name' => 'Nipuna Prabidhik Sewa',
                    'slug' => 'nipuna-prabhidik-sewa',
                    'employer_id' => 2,
                    'email' => 'nipuna123@yopmail.com',
                    'address' => 'Pokhara',
                    'description' => 'Nipuna Prabidhik Sewa is an IT office located in Pokhara',

                ],
                [
                    'id' => 3,
                    'name' => 'Janakari Tech',
                    'slug' => 'janakari-tech',
                    'employer_id' => 3,
                    'email' => 'janakari123@yopmail.com',
                    'address' => 'Pokhara',
                    'description' => 'Janakari Tech is an IT office located in Pokhara',

                ],
            ]
        );

        DB::table('job_listings')->insert(
            [
                [
                    'id' => 1,
                    'title' => 'Laravel Developr',
                    'company_id' => 1,
                    'description' => 'Vacancy for a laravel developer',
                    'application_instruction' => 'Call us at 9800000001',

                ],
                [
                    'id' => 2,
                    'title' => 'React Developer',
                    'company_id' => 2,
                    'description' => 'Vacancy for a react developer',
                    'application_instruction' => 'Call us at 9800000002',
                ],
                [
                    'id' => 3,
                    'title' => 'UI-UX designer',
                    'company_id' => 3,
                    'description' => 'Vacancy for a graphic designer',
                    'application_instruction' => 'Call us at 9800000003',
                ],
                [
                    'id' => 4,
                    'title' => 'UI-UX designer',
                    'company_id' => 1,
                    'description' => 'Vacancy for a graphic designer',
                    'application_instruction' => 'Call us at 9800000003',
                ],
            ]
        );
    }
}

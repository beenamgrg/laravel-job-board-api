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
                    'employer_id' => 2,
                    'email' => 'firefly123@yopmail.com',
                    'phone' => '061-000001',
                    'address' => 'Pokhara',
                    'description' => 'Firefly tech is an IT office located in Pokhara',

                ],
                [
                    'id' => 2,
                    'name' => 'Nipuna Prabidhik Sewa',
                    'slug' => 'nipuna-prabhidik-sewa',
                    'employer_id' => 4,
                    'email' => 'nipuna123@yopmail.com',
                    'phone' => '061-000002',
                    'address' => 'Pokhara',
                    'description' => 'Nipuna Prabidhik Sewa is an IT office located in Pokhara',

                ],
                [
                    'id' => 3,
                    'name' => 'Janakari Tech',
                    'slug' => 'janakari-tech',
                    'employer_id' => 5,
                    'email' => 'janakari123@yopmail.com',
                    'phone' => '061-000003',
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

        DB::table('job_applications')->insert(
            [
                [
                    'id' => 1,
                    'resume' => '/job-applications/resume/1718009225.Zq1q.pdf',
                    'cover_letter' => 'This is a sample cover letter',
                    'user_id' => 3,
                    'job_id' => 1,
                    'is_approved' => 0,
                    'is_rejected' => 0,

                ],
                [
                    'id' => 2,
                    'resume' => '/job-applications/resume/1718013550.KGk2.pdf',
                    'cover_letter' => 'This is a sample cover letter',
                    'user_id' => 6,
                    'job_id' => 3,
                    'is_approved' => 0,
                    'is_rejected' => 0,

                ],
            ]
        );
    }
}

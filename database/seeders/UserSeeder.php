<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert(
            [
                [
                    'id' => 1,
                    'name' => 'Employer1',
                    'email' => 'employer1@gmail.com',
                    'role' => 'employer',
                    'password' => Hash::make('employer')
                ],
                [
                    'id' => 2,
                    'name' => 'seeker',
                    'email' => 'seeker@gmail.com',
                    'role' => 'seeker',
                    'password' => Hash::make('seeker')
                ],
                [
                    'id' => 3,
                    'name' => 'Employer2',
                    'email' => 'employer2@gmail.com',
                    'role' => 'employer',
                    'password' => Hash::make('employer')
                ],
                [
                    'id' => 4,
                    'name' => 'Employer3',
                    'email' => 'employer3@gmail.com',
                    'role' => 'employer',
                    'password' => Hash::make('employer')
                ],
                [
                    'id' => 5,
                    'name' => 'seeker2',
                    'email' => 'seeke2r@gmail.com',
                    'role' => 'seeker',
                    'password' => Hash::make('seeker')
                ],
            ]
        );
    }
}

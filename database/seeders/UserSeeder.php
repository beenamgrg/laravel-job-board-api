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
                    'name' => 'admin',
                    'email' => 'admin@gmail.com',
                    'role' => 'admin',
                    'password' => Hash::make('admin')
                ],
                [
                    'id' => 2,
                    'name' => 'seeker',
                    'email' => 'seeker@gmail.com',
                    'role' => 'seeker',
                    'password' => Hash::make('customer')
                ]
            ]
        );
    }
}

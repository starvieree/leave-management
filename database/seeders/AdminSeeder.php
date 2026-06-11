<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            [
                'email' => 'admin@mail.com'
            ],
            [
                'name' => 'Administrator',
                'password' => bcrypt('password'),
                'role' => 'admin'
            ]
        );
    }
}
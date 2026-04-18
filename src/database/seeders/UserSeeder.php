<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::updateOrCreate(
            # find by email
            ['email' => 'user1@gmail.com'],
            # attributes value.
            [
                'name'     => 'user1',
                'email'    => 'user1@gmail.com',
                'password' => Hash::make('password'),
            ]
        );
    }
}

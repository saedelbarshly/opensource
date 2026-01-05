<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserType;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name'                 => 'Admin',
            'email'                => 'admin@gmail.com',
            'phone_code'           => '966',
            'phone'                => '555555555',
            'password'             => '123456789',
            'user_type'            => UserType::ADMIN,
            'is_active'            => true,
            'is_super'             => true,
            'email_verified_at'    => now(),
            'phone_verified_at'    => now(),
        ]);
    }
}

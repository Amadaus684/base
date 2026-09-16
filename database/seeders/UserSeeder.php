<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $currentPassword = 'qwerty123';
        
        $users = [
            [
                'name' => 'owner',
                'email' => 'owner@example.com',
                'password' => $currentPassword,
                'role' => 'Owner',
            ],
            [
                'name' => 'admin',
                'email' => 'admin@example.com',
                'password' => $currentPassword,
                'role' => 'Admin',
            ],
            [
                'name' => 'author',
                'email' => 'author@example.com',
                'password' => $currentPassword,
                'role' => 'Author',
            ],
            [
                'name' => 'member',
                'email' => 'member@example.com',
                'password' => $currentPassword,
                'role' => 'Member',
            ],
        ];

        foreach ($users as $userData) {
            $role = Role::findByName($userData['role']);

            $user = User::updateOrCreate(
                [
                    'email' => $userData['email'],
                ],
                [
                    'name' => $userData['name'],
                    'password' => Hash::make($userData['password']),
                ],
            );

            $user->syncRoles([$role]);
        }
    }
}
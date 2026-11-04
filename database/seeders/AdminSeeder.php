<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // First, update the existing admin user's password to use Bcrypt
        $admin = User::where('email', 'admin@tourtravel.com')->first();

        if ($admin) {
            // Update the password to use Bcrypt hashing
            $admin->password = Hash::make('Admin@123456');
            $admin->save();
        }

        // Then create/update the admin user
        User::updateOrCreate(
            ['email' => 'admin@tourtravel.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('Admin@123456'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}

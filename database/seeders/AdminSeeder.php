<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'admin@tourtravel.com')->first();

        if ($admin) {
            $admin->email = 'info@voyantaservices.com';
            $admin->password = Hash::make('TenetiV@121');
            $admin->save();
        }

        User::updateOrCreate(
            ['email' => 'info@voyantaservices.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('TenetiV@121'),
                'is_admin' => true,
                'email_verified_at' => now(),
            ]
        );
    }
}

<?php

namespace Database\Seeders;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('SEED_ADMIN_EMAIL', 'admin@lima.ai');
        $password = env('SEED_ADMIN_PASSWORD', 'ChangeMe123!');

        $admin = User::firstOrCreate(
            ['email' => $email],
            ['name' => 'Admin', 'password' => Hash::make($password)]
        );

        NotificationPreference::firstOrCreate(
            ['user_id' => $admin->id],
            [
                'notify_new_order' => true,
                'notify_contact_message' => true,
                'notify_consultation' => true,
                'notify_mockup_request' => true,
            ]
        );
    }
}

<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@SPQ.app'],
            [
                'name' => 'Admin SPQ',
                'password' => Hash::make('changeme_immediately'),
                'role' => 'superadmin',
                'client_id' => null,
                'email_verified_at' => now(),
            ]
        );
    }
}

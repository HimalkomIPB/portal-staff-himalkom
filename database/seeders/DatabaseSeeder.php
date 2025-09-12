<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => env('APP_FILAMENT_USER')], [
            'name' => 'superadmin_kacow',
            'email' => env("APP_FILAMENT_USER"),
            'password' => bcrypt(env("APP_FILAMENT_PASSWORD")),
            'email_verified_at' => now(),
        ]);

        $this->call([RolePermissionSeeder::class]);
    }
}

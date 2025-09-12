<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $managingDirectorRole = Role::updateOrCreate([
            'name' => 'managing director'
        ]);
        $supervisorRole = Role::updateOrCreate([
            'name' => 'supervisor'
        ]);
        $bphRole = Role::updateOrCreate([
            'name' => 'bph'
        ]);
        $pjsRole = Role::updateOrCreate([
            'name' => 'pjs'
        ]);
    }
}

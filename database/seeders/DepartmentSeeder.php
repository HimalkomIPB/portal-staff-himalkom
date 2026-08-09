<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            [
                'name' => 'Research and Technology',
                'abbreviation' => 'RnT',
            ],
            [
                'name' => 'Badan Pengurus Harian',
                'abbreviation' => 'BPH',
            ],
            [
                'name' => 'Badan Pengawas',
                'abbreviation' => 'BP',
            ],
            [
                'name' => 'External',
                'abbreviation' => 'Ext',
            ],
            [
                'name' => 'Internal',
                'abbreviation' => 'Int',
            ],
            [
                'name' => 'Education',
                'abbreviation' => 'Edu',
            ],
            [
                'name' => 'Creative',
                'abbreviation' => 'Cr',
            ],
            [
                'name' => 'Finance',
                'abbreviation' => 'Fin',
            ],
            [
                'name' => 'Talent and Sport',
                'abbreviation' => 'TdS',
            ],
        ];

        foreach ($departments as $dept) {
            Department::updateOrCreate(
                ['slug' => Str::slug($dept['name'])],
                [
                    'name' => $dept['name'],
                    'abbreviation' => $dept['abbreviation'],
                    'description' => $dept['name'] . ' Department',
                ]
            );
        }
    }
}

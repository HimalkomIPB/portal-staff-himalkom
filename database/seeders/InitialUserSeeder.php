<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InitialUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ambil Department ID
        $bphDept = Department::where('name', 'Badan Pengurus Harian')->first()->id;
        $bpDept = Department::where('name', 'Badan Pengawas')->first()->id;
        $eduDept = Department::where('name', 'Education')->first()->id;
        $extDept = Department::where('name', 'External')->first()->id;
        $intDept = Department::where('name', 'Internal')->first()->id;
        $finDept = Department::where('name', 'Finance')->first()->id;
        $tdsDept = Department::where('name', 'Talent and Sport')->first()->id;
        $creaDept = Department::where('name', 'Creative')->first()->id;

        // 2. Data User beserta role & departemennya
        $usersData = [
            // --- BPH ---
            [
                'name' => 'Nugraheni Dwi Ayu Putri',
                'email' => 'ndayuputri@apps.ipb.ac.id',
                'department_id' => $bphDept,
                'roles' => ['bph'],
                'sc_departments' => []
            ],
            [
                'name' => 'Luthfi Muharram',
                'email' => 'luthfimuharram@apps.ipb.ac.id',
                'department_id' => $bphDept,
                'roles' => ['bph'],
                'sc_departments' => [] // Nanti disesuaikan dept apa yg diawasi
            ],
            [
                'name' => 'Avriell Shianne Chrisly',
                'email' => 'avriellshiannechrisly@apps.ipb.ac.id',
                'department_id' => $bphDept,
                'roles' => ['bph'],
                'sc_departments' => []
            ],
            [
                'name' => 'Yoga Cristopher Gulo',
                'email' => 'yogacristophergulo@apps.ipb.ac.id',
                'department_id' => $bphDept,
                'roles' => ['bph'],
                'sc_departments' => []
            ],
            [
                'name' => 'Andra Firmansyah Asmoro',
                'email' => 'derrandra@apps.ipb.ac.id',
                'department_id' => $bphDept,
                'roles' => ['bph'],
                'sc_departments' => [$eduDept] // Contoh: SC untuk Education
            ],
            [
                'name' => 'M. Ibnu Fadhil',
                'email' => 'fadhilibnu@apps.ipb.ac.id',
                'department_id' => $bphDept,
                'roles' => ['bph'],
                'sc_departments' => []
            ],
            // --- BP ---
            [
                'name' => 'Adhiya Radhin Fasya',
                'email' => 'arradhin@apps.ipb.ac.id',
                'department_id' => $bpDept,
                'roles' => ['bph'], // BP pakai role bph/pjs? (Sesuaikan jika perlu)
                'sc_departments' => []
            ],
            // --- MD (Managing Director) ---
            [
                'name' => 'Jeremy Tjahjana',
                'email' => 'jeremytjahjana@apps.ipb.ac.id',
                'department_id' => $eduDept,
                'roles' => ['managing director'],
                'sc_departments' => []
            ],
            [
                'name' => 'Nafil Khautal Budiono',
                'email' => 'nkhautalbudiono@apps.ipb.ac.id',
                'department_id' => $extDept,
                'roles' => ['managing director'],
                'sc_departments' => []
            ],
            [
                'name' => 'Nabil Musannif Siregar',
                'email' => 'nabilnifsiregar@apps.ipb.ac.id',
                'department_id' => $intDept,
                'roles' => ['managing director'],
                'sc_departments' => []
            ],
            [
                'name' => 'Fatimah Puspa Jani',
                'email' => 'fatimahpjani@apps.ipb.ac.id',
                'department_id' => $finDept,
                'roles' => ['managing director'],
                'sc_departments' => []
            ],
            [
                'name' => 'M. Reyhan Hermawan',
                'email' => 'mreyhanhermawan@apps.ipb.ac.id',
                'department_id' => $tdsDept,
                'roles' => ['managing director'],
                'sc_departments' => []
            ],
            [
                'name' => 'Faisal Mumtaz',
                'email' => 'faisalmumtaz@apps.ipb.ac.id',
                'department_id' => $creaDept,
                'roles' => ['managing director'],
                'sc_departments' => []
            ],
        ];

        foreach ($usersData as $data) {
            // Update jika email sudah ada, jika belum buat baru
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'department_id' => $data['department_id'],
                    // Jika password belum ada, set random 12 char
                    'password' => Hash::make(Str::random(12)), 
                ]
            );

            // Assign roles
            $user->syncRoles($data['roles']);

            // Assign SC Departments (Pivot Table)
            if (!empty($data['sc_departments'])) {
                $user->scDepartments()->sync($data['sc_departments']);
            }
        }
    }
}

<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // -------------------------------------------------------
        // 1. Reset cached roles & permissions
        // -------------------------------------------------------
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // -------------------------------------------------------
        // 2. Define all permissions
        // -------------------------------------------------------
        $permissions = [

            // --- Work Program ---
            'work-program.view',
            'work-program.create',
            'work-program.edit',
            'work-program.delete',
            'work-program.comment',

            // --- Document Management ---
            'document.view',
            'document.upload',
            'document.approve',
            'document.comment',

            // --- Request Center ---
            'request.view',          // lihat request masuk/keluar dept sendiri
            'request.create',        // buat request baru
            'request.manage',        // terima/tolak/update status (dept pengelola)
            'request.view-all',      // lihat semua request semua dept (BPH)
            'request.view-status',   // lihat status pengajuan sendiri (Sekretaris, Bendahara)

            // --- Performance & Evaluation ---
            'performance.view',       // lihat penilaian anggota dept sendiri / diawasi
            'performance.view-all',   // lihat penilaian semua dept (BPH)
            'performance.evaluate',   // isi form penilaian anggota
            'performance.view-self',  // lihat penilaian diri sendiri

            // --- Calendar / Agenda ---
            'agenda.view',
            'agenda.create-org',   // buat agenda organisasi (BPH)
            'agenda.create-dept',  // buat agenda divisi (MD, PJS, Sekretaris)
            'agenda.edit-dept',
            'agenda.delete-dept',

            // --- Finance ---
            'finance.view',        // lihat laporan keuangan
            'finance.manage',      // kelola kas, pemasukan, pengeluaran (Bendahara)

            // --- Task ---
            'task.view',           // lihat tugas yang diberikan ke user

            // --- Member / User ---
            'member.view',         // lihat daftar anggota dept sendiri
            'member.manage',       // kelola anggota divisi (MD/PJS)
            'user.manage',         // kelola user sistem (Super Admin)

            // --- Notification ---
            'notification.view',

            // --- Archive ---
            'archive.view',        // lihat arsip dept sendiri
            'archive.view-all',    // lihat semua arsip (BPH)
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(['name' => $permission]);
        }

        // -------------------------------------------------------
        // 3. Define roles & assign permissions
        // -------------------------------------------------------
        $roles = [

            'supervisor' => $permissions, // semua permission

            'bph' => [
                'work-program.view',
                'work-program.comment',
                'document.view',
                'document.approve',
                'document.comment',
                'request.view',
                'request.view-all',
                'request.create',
                'request.manage',
                'request.view-status',
                'performance.view',
                'performance.view-all',
                'performance.evaluate',
                'performance.view-self',
                'agenda.view',
                'agenda.create-org',
                'finance.view',
                'member.view',
                'task.view',
                'notification.view',
                'archive.view',
                'archive.view-all',
            ],

            'managing director' => [
                'work-program.view',
                'work-program.create',
                'work-program.edit',
                'work-program.delete',
                'work-program.comment',
                'document.view',
                'document.upload',
                'document.comment',
                'request.view',
                'request.create',
                'request.manage',
                'request.view-status',
                'performance.view',
                'performance.evaluate',
                'performance.view-self',
                'agenda.view',
                'agenda.create-dept',
                'agenda.edit-dept',
                'agenda.delete-dept',
                'finance.view',
                'member.view',
                'member.manage',
                'task.view',
                'notification.view',
                'archive.view',
            ],

            'pjs' => [
                // Sama persis dengan MD
                'work-program.view',
                'work-program.create',
                'work-program.edit',
                'work-program.delete',
                'work-program.comment',
                'document.view',
                'document.upload',
                'document.comment',
                'request.view',
                'request.create',
                'request.manage',
                'request.view-status',
                'performance.view',
                'performance.evaluate',
                'performance.view-self',
                'agenda.view',
                'agenda.create-dept',
                'agenda.edit-dept',
                'agenda.delete-dept',
                'finance.view',
                'member.view',
                'member.manage',
                'task.view',
                'notification.view',
                'archive.view',
            ],

            'sekretaris' => [
                'work-program.view',
                'document.view',
                'document.upload',
                'document.comment',
                'request.view',
                'request.create',
                'request.view-status',
                'performance.view-self',
                'agenda.view',
                'agenda.create-dept',
                'agenda.edit-dept',
                'task.view',
                'member.view',
                'notification.view',
                'archive.view',
            ],

            'bendahara' => [
                'work-program.view',
                'document.view',
                'request.view',
                'request.create',
                'request.view-status',
                'performance.view-self',
                'agenda.view',
                'finance.view',
                'finance.manage',
                'task.view',
                'member.view',
                'notification.view',
                'archive.view',
            ],

            'anggota' => [
                'agenda.view',
                'member.view',
                'task.view',
                'performance.view-self',
                'notification.view',
            ],

        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::updateOrCreate(['name' => $roleName]);
            $role->syncPermissions($rolePermissions);
        }
    }
}

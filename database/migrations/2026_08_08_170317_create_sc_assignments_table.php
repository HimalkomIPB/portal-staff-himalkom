<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Tabel ini menyimpan relasi SC (Steering Committee) ↔ Department.
     * Setiap BPH bisa menjadi SC untuk satu atau lebih department.
     * Department utama user tetap di kolom department_id pada tabel users.
     */
    public function up(): void
    {
        Schema::create('sc_assignments', function (Blueprint $table) {
            $table->foreignUlid('user_id')
                ->constrained()
                ->onDelete('cascade');
            $table->foreignUlid('department_id')
                ->constrained()
                ->onDelete('cascade');
            $table->timestamps();

            // Satu user hanya bisa jadi SC sekali per department
            $table->unique(['user_id', 'department_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_assignments');
    }
};

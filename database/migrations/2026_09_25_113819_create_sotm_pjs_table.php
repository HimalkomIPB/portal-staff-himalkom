<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sotm_pjs', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->unique(['department_id', 'user_id']); // satu user hanya bisa jadi PJ satu kali per divisi
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sotm_pjs');
    }
};

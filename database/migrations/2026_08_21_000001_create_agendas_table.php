<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('agendas', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('title');
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('jenis', ['offline', 'online'])->default('offline');
            $table->string('lokasi')->nullable(); // null jika online / tidak relevan
            $table->enum('skala', ['departemen', 'general'])->default('departemen');
            $table->text('deskripsi')->nullable();
            // null → agenda General (tidak terikat satu departemen)
            $table->ulid('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('departments')->nullOnDelete();
            // users.id juga ULID → gunakan foreignUlid
            $table->ulid('created_by');
            $table->foreign('created_by')->references('id')->on('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};

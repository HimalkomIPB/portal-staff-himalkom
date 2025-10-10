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
        Schema::create('work_programs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('description');
            $table->date('start_at');
            $table->date('finished_at');
            $table->decimal('funds', 15, 2);
            $table->string('sources_of_funds');
            $table->integer('participation_total');
            $table->string('participation_coverage');
            $table->string('lpj_url')->default('Belum diunggah');
            $table->string('spg_url')->default('Belum diunggah');
            $table->foreignUlid('department_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_programs');
    }
};

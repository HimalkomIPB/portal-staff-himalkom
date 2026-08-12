<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_evaluations', function (Blueprint $table) {
            $table->ulid('id')->primary();

            // Siapa yang menilai
            $table->string('evaluator_id');
            $table->foreign('evaluator_id')->references('id')->on('users')->cascadeOnDelete();

            // Siapa yang dinilai
            $table->string('evaluated_id');
            $table->foreign('evaluated_id')->references('id')->on('users')->cascadeOnDelete();

            // Konteks departemen
            $table->ulid('department_id');
            $table->foreign('department_id')->references('id')->on('departments')->cascadeOnDelete();

            // Peran evaluator: md atau sc
            $table->enum('evaluator_role', ['md', 'sc']);

            // Periode penilaian
            $table->tinyInteger('period_month');  // 1-12
            $table->smallInteger('period_year');

            // Skor 4 kriteria (0-100)
            $table->tinyInteger('score_attendance');   // Kehadiran (bintang × 20)
            $table->tinyInteger('score_commitment');   // Komitmen & Responsivitas
            $table->tinyInteger('score_contribution'); // Kontribusi & Etika
            $table->tinyInteger('score_initiative');   // Inisiatif & Solusi

            // Nilai akhir: rata-rata 4 kriteria
            $table->decimal('final_score', 5, 2);

            // Catatan kualitatif (opsional)
            $table->text('notes')->nullable();

            $table->timestamps();

            // Satu evaluator hanya bisa menilai satu orang sekali per periode & role
            $table->unique(
                ['evaluator_id', 'evaluated_id', 'department_id', 'evaluator_role', 'period_month', 'period_year'],
                'unique_evaluation'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_evaluations');
    }
};

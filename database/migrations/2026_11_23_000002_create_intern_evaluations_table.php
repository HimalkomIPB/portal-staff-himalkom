<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intern_evaluations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('evaluator_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('intern_id')->constrained('interns')->cascadeOnDelete();
            $table->foreignUlid('department_id')->constrained('departments')->cascadeOnDelete();
            $table->unsignedTinyInteger('period_month');
            $table->unsignedSmallInteger('period_year');
            $table->unsignedTinyInteger('score_punctuality');
            $table->unsignedTinyInteger('score_initiative');
            $table->unsignedTinyInteger('score_attendance');
            $table->unsignedTinyInteger('score_cooperation');
            $table->unsignedTinyInteger('score_professionalism');
            $table->decimal('final_score', 5, 2);
            $table->timestamps();

            $table->unique(['evaluator_id', 'intern_id', 'period_month', 'period_year'], 'intern_eval_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intern_evaluations');
    }
};

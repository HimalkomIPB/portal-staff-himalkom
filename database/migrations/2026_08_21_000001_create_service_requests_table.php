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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            
            $table->foreignUlid('requester_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('department_id')->constrained('departments')->cascadeOnDelete();
            
            $table->enum('type', ['copm', 'codm', 'komnews', 'riset']);
            $table->string('title');
            $table->text('description');
            
            // pending, accepted, rejected, in_progress, revision, completed, cancelled
            $table->string('status')->default('pending');
            
            // The assigned MD from the managing department
            $table->foreignUlid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            
            $table->date('due_date')->nullable();
            
            // URL or path for final deliverable
            $table->string('final_file_path')->nullable();
            
            // User approval
            $table->boolean('is_approved_by_requester')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};

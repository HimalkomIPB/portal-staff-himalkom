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
        Schema::create('proposals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('uploader_id')->constrained('users')->onDelete('cascade');
            $table->foreignUlid('reviewer_id')->nullable()->constrained('users')->onDelete('cascade')->nullOnDelete();
            $table->string('title', 255);
            $table->string('file_path', 255);
            $table->enum('status', ['pending', 'approved', 'rejected']);
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};

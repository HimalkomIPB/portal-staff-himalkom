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
        Schema::table('work_programs', function (Blueprint $table) {
            $table->enum('status', ['pending', 'accepted', 'reviewed'])->default('pending')->after('department_id');
            $table->foreignUlid('proposal_id')->nullable()->constrained('proposals')->onDelete('set null')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_programs', function (Blueprint $table) {
            $table->dropForeign('work_programs_proposal_id_foreign');
            $table->dropColumn(['status', 'proposal_id']);
        });
    }
};

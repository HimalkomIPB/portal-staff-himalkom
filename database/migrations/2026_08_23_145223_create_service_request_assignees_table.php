<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('service_request_assignees', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('service_request_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Migrate existing assigned_to data
        $requests = DB::table('service_requests')->whereNotNull('assigned_to')->get();
        foreach ($requests as $request) {
            DB::table('service_request_assignees')->insert([
                'service_request_id' => $request->id,
                'user_id' => $request->assigned_to,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropForeign(['assigned_to']);
            $table->dropColumn('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->foreignUlid('assigned_to')->nullable()->constrained('users')->nullOnDelete();
        });
        
        $assignees = DB::table('service_request_assignees')->get();
        foreach ($assignees as $assignee) {
            DB::table('service_requests')->where('id', $assignee->service_request_id)->update(['assigned_to' => $assignee->user_id]);
        }

        Schema::dropIfExists('service_request_assignees');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loa_rejection_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loa_request_id')->constrained('loa_requests')->cascadeOnDelete();
            // Which stage was rejected (dept_head, saso, campus_director)
            $table->string('stage_key', 30);
            // Stage label for display (e.g. "SASO Officer")
            $table->string('stage_label', 60);
            // Who rejected and when
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->dateTime('rejected_at');
            // The stated reason
            $table->text('reason')->nullable();
            // Which resubmission cycle this belongs to (0 = first rejection, 1 = after 1st resubmit, etc.)
            $table->unsignedTinyInteger('resubmit_cycle')->default(0);
            $table->timestamps();
        });

        // Drop the single-column approach introduced in migration 220000
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('previous_rejected_by');
            $table->dropColumn(['previous_rejection_reason', 'previous_rejected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loa_rejection_history');

        Schema::table('loa_requests', function (Blueprint $table) {
            $table->text('previous_rejection_reason')->nullable()->after('rejection_reason');
            $table->dateTime('previous_rejected_at')->nullable()->after('previous_rejection_reason');
            $table->foreignId('previous_rejected_by')->nullable()->constrained('users')->nullOnDelete()->after('previous_rejected_at');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            // Stores the rejection history so staff can see it after a resubmission
            $table->text('previous_rejection_reason')->nullable()->after('rejection_reason');
            $table->dateTime('previous_rejected_at')->nullable()->after('previous_rejection_reason');
            $table->foreignId('previous_rejected_by')->nullable()->constrained('users')->nullOnDelete()->after('previous_rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('previous_rejected_by');
            $table->dropColumn(['previous_rejection_reason', 'previous_rejected_at']);
        });
    }
};

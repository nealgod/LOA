<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            // Hashed resubmit token — 7-day link sent in the rejection email
            $table->string('resubmit_token_hash', 64)->nullable()->unique()->after('rejection_reason');
            $table->dateTime('resubmit_token_expires_at')->nullable()->after('resubmit_token_hash');
            // Tracks how many times the student has resubmitted (controls .1, .2 suffix)
            $table->unsignedTinyInteger('resubmit_count')->default(0)->after('resubmit_token_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dropColumn(['resubmit_token_hash', 'resubmit_token_expires_at', 'resubmit_count']);
        });
    }
};

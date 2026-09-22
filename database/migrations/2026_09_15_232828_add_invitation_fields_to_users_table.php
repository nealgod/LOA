<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // SHA-256 hash of the one-time invitation token (never stored in plain text)
            $table->string('invitation_token', 64)->nullable()->unique()->after('remember_token');
            $table->timestamp('invitation_sent_at')->nullable()->after('invitation_token');
            // Null = account not yet activated; set when the user completes the setup form
            $table->timestamp('invitation_accepted_at')->nullable()->after('invitation_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['invitation_token', 'invitation_sent_at', 'invitation_accepted_at']);
        });
    }
};

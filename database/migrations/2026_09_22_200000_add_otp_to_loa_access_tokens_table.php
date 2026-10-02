<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_access_tokens', function (Blueprint $table) {
            // Nullable so existing rows are unaffected
            $table->string('otp_hash', 64)->nullable()->after('ip_address');
            $table->dateTime('otp_expires_at')->nullable()->after('otp_hash');
            $table->boolean('otp_verified')->default(false)->after('otp_expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('loa_access_tokens', function (Blueprint $table) {
            $table->dropColumn(['otp_hash', 'otp_expires_at', 'otp_verified']);
        });
    }
};

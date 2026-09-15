<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_access_tokens', function (Blueprint $table) {
            // Change from TIMESTAMP (which MySQL auto-sets DEFAULT CURRENT_TIMESTAMP)
            // to DATETIME so the value we pass is always stored exactly as given.
            $table->dateTime('expires_at')->nullable(false)->change();
            $table->dateTime('used_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('loa_access_tokens', function (Blueprint $table) {
            $table->timestamp('expires_at')->nullable(false)->change();
            $table->timestamp('used_at')->nullable()->change();
        });
    }
};

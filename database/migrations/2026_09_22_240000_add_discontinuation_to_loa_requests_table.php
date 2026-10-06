<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dateTime('discontinued_at')->nullable()->after('rejection_reason');
            $table->foreignId('discontinued_by')->nullable()->constrained('users')->nullOnDelete()->after('discontinued_at');
            $table->text('discontinuation_reason')->nullable()->after('discontinued_by');
        });
    }

    public function down(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('discontinued_by');
            $table->dropColumn(['discontinued_at', 'discontinuation_reason']);
        });
    }
};

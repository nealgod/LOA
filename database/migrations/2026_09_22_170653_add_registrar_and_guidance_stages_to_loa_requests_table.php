<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            // Stage 4 — Registrar (after Campus Director)
            $table->string('registrar_status', 20)->nullable()->index()->after('campus_director_by');
            $table->timestamp('registrar_at')->nullable()->after('registrar_status');
            $table->foreignId('registrar_by')->nullable()->after('registrar_at')->constrained('users')->restrictOnDelete();

            // Stage 5 — Guidance (after Registrar)
            $table->string('guidance_status', 20)->nullable()->index()->after('registrar_by');
            $table->timestamp('guidance_at')->nullable()->after('guidance_status');
            $table->foreignId('guidance_by')->nullable()->after('guidance_at')->constrained('users')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('guidance_by');
            $table->dropIndex(['guidance_status']);
            $table->dropColumn(['guidance_status', 'guidance_at']);

            $table->dropConstrainedForeignId('registrar_by');
            $table->dropIndex(['registrar_status']);
            $table->dropColumn(['registrar_status', 'registrar_at']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Default to 'guidance' (lowest privilege) — never 'administrator'.
            // A wrong default of 'administrator' would silently grant the Policy's
            // before() bypass to any row inserted without an explicit role.
            $table->string('role', 40)->default('guidance')->after('password');
            $table->foreignId('department_id')->nullable()->after('role')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn('role');
        });
    }
};

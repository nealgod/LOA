<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->string('dept_head_status', 20)->nullable()->index()->after('status');
            $table->timestamp('dept_head_at')->nullable()->after('dept_head_status');
            $table->foreignId('dept_head_by')->nullable()->after('dept_head_at')->constrained('users')->restrictOnDelete();

            $table->string('saso_status', 20)->nullable()->index()->after('dept_head_by');
            $table->timestamp('saso_at')->nullable()->after('saso_status');
            $table->foreignId('saso_by')->nullable()->after('saso_at')->constrained('users')->restrictOnDelete();

            $table->string('campus_director_status', 20)->nullable()->index()->after('saso_by');
            $table->timestamp('campus_director_at')->nullable()->after('campus_director_status');
            $table->foreignId('campus_director_by')->nullable()->after('campus_director_at')->constrained('users')->restrictOnDelete();

            $table->timestamp('rejected_at')->nullable()->after('campus_director_by');
            $table->foreignId('rejected_by')->nullable()->after('rejected_at')->constrained('users')->restrictOnDelete();
            $table->text('rejection_reason')->nullable()->after('rejected_by');
        });

        \App\Models\LoaRequest::query()
            ->where('status', 'submitted')
            ->whereNull('dept_head_status')
            ->update(['dept_head_status' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('loa_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('rejected_by');
            $table->dropColumn([
                'rejection_reason',
                'rejected_at',
            ]);

            $table->dropConstrainedForeignId('campus_director_by');
            $table->dropIndex(['campus_director_status']);
            $table->dropColumn([
                'campus_director_status',
                'campus_director_at',
            ]);

            $table->dropConstrainedForeignId('saso_by');
            $table->dropIndex(['saso_status']);
            $table->dropColumn([
                'saso_status',
                'saso_at',
            ]);

            $table->dropConstrainedForeignId('dept_head_by');
            $table->dropIndex(['dept_head_status']);
            $table->dropColumn([
                'dept_head_status',
                'dept_head_at',
            ]);
        });
    }
};

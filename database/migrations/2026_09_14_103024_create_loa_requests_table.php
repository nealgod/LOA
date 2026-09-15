<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loa_requests', function (Blueprint $table) {
            $table->id();
            $table->string('control_number', 40)->nullable()->unique();
            $table->foreignId('loa_access_token_id')->constrained()->restrictOnDelete();
            $table->string('student_id', 40);
            $table->string('full_name');
            $table->string('email');
            $table->date('start_date')->nullable();
            $table->date('return_date')->nullable();
            $table->text('reason')->nullable();
            $table->string('parent_full_name')->nullable();
            $table->string('parent_relationship')->nullable();
            $table->string('parent_phone', 30)->nullable();
            $table->string('status', 40)->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loa_requests');
    }
};

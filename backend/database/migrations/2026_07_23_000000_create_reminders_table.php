<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 160);
            $table->date('expires_at');
            $table->unsignedSmallInteger('remind_days_before')->default(7);
            $table->string('channel', 32)->default('email');
            $table->string('category', 64)->nullable();
            $table->string('status', 24)->default('active');
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('renewed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status', 'expires_at']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};

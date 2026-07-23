<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reminders', function (Blueprint $table): void {
            $table->text('notes')->nullable();
            $table->string('priority', 16)->default('normal');
            $table->string('recurrence', 24)->default('none');
            $table->timestamp('snoozed_until')->nullable();
            $table->softDeletes();
        });

        Schema::create('reminder_schedules', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('reminder_id')->constrained()->cascadeOnDelete();
            $table->string('channel', 32);
            $table->timestamp('scheduled_at');
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->string('status', 24)->default('pending');
            $table->string('idempotency_key', 96)->unique();
            $table->text('last_error')->nullable();
            $table->timestamps();
            $table->index(['status', 'scheduled_at']);
        });

        Schema::create('reminder_notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('reminder_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('reminder_schedule_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('channel', 32);
            $table->string('subject');
            $table->text('body');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('sent_at');
            $table->timestamps();
            $table->index(['user_id', 'read_at', 'created_at']);
        });

        Schema::create('notification_attempts', function (Blueprint $table): void {
            $table->id();
            $table->foreignUuid('reminder_schedule_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number');
            $table->string('provider', 64);
            $table->string('status', 24);
            $table->text('response')->nullable();
            $table->timestamp('attempted_at');
            $table->timestamps();
            $table->unique(['reminder_schedule_id', 'attempt_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_attempts');
        Schema::dropIfExists('reminder_notifications');
        Schema::dropIfExists('reminder_schedules');
        Schema::table('reminders', function (Blueprint $table): void {
            $table->dropSoftDeletes();
            $table->dropColumn(['notes', 'priority', 'recurrence', 'snoozed_until']);
        });
    }
};

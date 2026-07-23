<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reminders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('created_by')->nullable();
            $table->uuid('assigned_to')->nullable();
            $table->uuid('category_id')->nullable();
            $table->uuid('document_id')->nullable();

            $table->string('title');
            $table->text('description')->nullable();
            $table->string('reference_number')->nullable(); // e.g. document/policy no.
            $table->string('issuer')->nullable();           // issuing authority
            $table->date('expiry_date');
            $table->date('issue_date')->nullable();

            $table->enum('status', ['active', 'completed', 'renewed', 'archived'])->default('active');

            // Recurrence
            $table->enum('recurrence', ['none', 'monthly', 'yearly', 'custom'])->default('none');
            $table->unsignedSmallInteger('recurrence_interval')->nullable(); // for custom
            $table->enum('recurrence_unit', ['day', 'week', 'month', 'year'])->nullable();

            $table->timestamp('completed_at')->nullable();
            $table->timestamp('snoozed_until')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('assigned_to')->references('id')->on('users')->nullOnDelete();
            $table->foreign('category_id')->references('id')->on('categories')->nullOnDelete();
            $table->foreign('document_id')->references('id')->on('documents')->nullOnDelete();

            $table->index(['organization_id', 'status']);
            $table->index(['organization_id', 'expiry_date']);
        });

        Schema::create('reminder_tag', function (Blueprint $table) {
            $table->uuid('reminder_id');
            $table->uuid('tag_id');
            $table->primary(['reminder_id', 'tag_id']);
            $table->foreign('reminder_id')->references('id')->on('reminders')->cascadeOnDelete();
            $table->foreign('tag_id')->references('id')->on('tags')->cascadeOnDelete();
        });

        // A reminder has one or more notification schedules (offset + channel).
        Schema::create('reminder_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('reminder_id');
            $table->unsignedSmallInteger('offset_days'); // days before expiry (0 = on the day)
            $table->enum('channel', ['email', 'in_app', 'web_push', 'sms', 'whatsapp', 'webhook'])->default('email');
            $table->timestamp('send_at');
            $table->enum('status', ['pending', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error')->nullable();
            $table->timestamps();

            $table->foreign('reminder_id')->references('id')->on('reminders')->cascadeOnDelete();
            // Hot path for the scheduler: find due, pending notifications.
            $table->index(['status', 'send_at']);
            $table->index('reminder_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reminder_notifications');
        Schema::dropIfExists('reminder_tag');
        Schema::dropIfExists('reminders');
    }
};

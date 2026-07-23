<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_connections', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->uuid('organization_id');
            $table->enum('provider', ['google', 'outlook']);
            $table->text('access_token')->nullable();   // encrypted at rest
            $table->text('refresh_token')->nullable();   // encrypted at rest
            $table->timestamp('expires_at')->nullable();
            $table->string('calendar_id')->nullable();
            $table->boolean('sync_enabled')->default(true);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->unique(['user_id', 'provider']);
        });

        // External event ids per reminder+connection — prevents duplicate events.
        Schema::create('calendar_events', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('reminder_id');
            $table->uuid('calendar_connection_id');
            $table->string('external_event_id');
            $table->timestamps();

            $table->foreign('reminder_id')->references('id')->on('reminders')->cascadeOnDelete();
            $table->foreign('calendar_connection_id')->references('id')->on('calendar_connections')->cascadeOnDelete();
            $table->unique(['reminder_id', 'calendar_connection_id']);
        });

        Schema::create('calendar_sync_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('calendar_connection_id')->nullable();
            $table->uuid('reminder_id')->nullable();
            $table->enum('status', ['success', 'failed', 'skipped']);
            $table->string('action')->nullable();  // create | update | delete
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index('calendar_connection_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendar_sync_logs');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('calendar_connections');
    }
};

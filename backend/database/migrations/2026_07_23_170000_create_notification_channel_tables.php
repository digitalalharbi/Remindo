<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Per-user channel preferences + quiet hours + fallback order.
        Schema::create('notification_preferences', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->unique();
            $table->json('channels')->nullable();        // enabled channels
            $table->boolean('quiet_hours_enabled')->default(false);
            $table->unsignedTinyInteger('quiet_start')->nullable(); // hour 0-23 (local)
            $table->unsignedTinyInteger('quiet_end')->nullable();
            $table->json('fallback_order')->nullable();   // e.g. ["email","web_push","sms"]
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        // Paid credit wallets for metered channels (sms, whatsapp, ai).
        Schema::create('credit_wallets', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->enum('channel', ['sms', 'whatsapp', 'ai']);
            $table->integer('balance')->default(0);
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->unique(['organization_id', 'channel']);
        });

        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('wallet_id');
            $table->integer('delta');                // + purchase, - usage
            $table->string('reason');
            $table->uuid('reference_id')->nullable();
            $table->timestamps();

            $table->foreign('wallet_id')->references('id')->on('credit_wallets')->cascadeOnDelete();
            $table->index('wallet_id');
        });

        // Admin-managed purchasable credit packs (+ provider pricing reference).
        Schema::create('credit_packs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('channel', ['sms', 'whatsapp', 'ai']);
            $table->string('name');
            $table->unsignedInteger('credits');
            $table->unsignedInteger('price');        // minor units
            $table->string('currency', 3)->default('SAR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Outgoing webhooks.
        Schema::create('webhook_endpoints', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->string('url');
            $table->text('secret');                  // encrypted; used to sign payloads
            $table->json('events')->nullable();       // subscribed event types
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
        });

        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('webhook_endpoint_id');
            $table->string('event');
            $table->json('payload');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();

            $table->foreign('webhook_endpoint_id')->references('id')->on('webhook_endpoints')->cascadeOnDelete();
            $table->index('webhook_endpoint_id');
        });

        // Web push subscriptions (browser PushSubscription).
        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->string('endpoint', 1000);
            $table->string('public_key')->nullable();
            $table->string('auth_token')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhook_endpoints');
        Schema::dropIfExists('credit_packs');
        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('credit_wallets');
        Schema::dropIfExists('notification_preferences');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->unsignedSmallInteger('trial_days')->default(0)->after('sort_order');
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('cancel_at_period_end')->default(false)->after('status');
            $table->timestamp('trial_ends_at')->nullable()->after('cancel_at_period_end');
            $table->unsignedTinyInteger('payment_attempts')->default(0)->after('trial_ends_at');
            $table->uuid('coupon_id')->nullable()->after('payment_attempts');
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('invoice_id');
            $table->uuid('organization_id');
            $table->unsignedInteger('amount');
            $table->string('currency', 3);
            $table->string('reason')->nullable();
            $table->string('provider_reference')->nullable();
            $table->timestamps();

            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
        });

        // Idempotency ledger for gateway webhooks — a replayed event is a no-op.
        Schema::create('processed_webhooks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('provider');
            $table->string('event_id');
            $table->timestamps();

            $table->unique(['provider', 'event_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('processed_webhooks');
        Schema::dropIfExists('refunds');
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['cancel_at_period_end', 'trial_ends_at', 'payment_attempts', 'coupon_id']);
        });
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn('trial_days');
        });
    }
};

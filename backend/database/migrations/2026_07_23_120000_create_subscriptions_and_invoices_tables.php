<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('plan_id');
            $table->enum('interval', ['monthly', 'yearly'])->default('monthly');
            $table->enum('status', ['trialing', 'active', 'past_due', 'canceled'])->default('active');
            $table->string('provider')->default('sandbox');
            $table->string('provider_reference')->nullable();
            $table->timestamp('current_period_start')->nullable();
            $table->timestamp('current_period_end')->nullable();
            $table->timestamp('canceled_at')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('plan_id')->references('id')->on('plans');
            $table->index(['organization_id', 'status']);
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('subscription_id')->nullable();
            $table->string('number')->unique();
            $table->enum('status', ['paid', 'open', 'void', 'refunded'])->default('paid');
            $table->unsignedInteger('subtotal');       // minor units
            $table->unsignedInteger('tax')->default(0);
            $table->unsignedInteger('total');
            $table->string('currency', 3)->default('SAR');
            $table->string('provider')->default('sandbox');
            $table->string('provider_reference')->nullable();
            $table->json('lines')->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->nullOnDelete();
            $table->index(['organization_id', 'issued_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('subscriptions');
    }
};

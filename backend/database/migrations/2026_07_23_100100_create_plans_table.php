<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();          // free | personal | professional | business
            $table->json('name');                      // localized { en, ar, es, tr }
            $table->json('description')->nullable();
            $table->unsignedInteger('price_monthly')->default(0); // minor units, base currency
            $table->unsignedInteger('price_yearly')->default(0);
            $table->string('currency', 3)->default('SAR');
            $table->integer('reminder_limit')->default(5);   // -1 = unlimited
            $table->integer('user_limit')->default(1);
            $table->integer('ai_operations_limit')->default(0);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('organizations', function (Blueprint $table) {
            $table->foreign('plan_id')->references('id')->on('plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropForeign(['plan_id']);
        });
        Schema::dropIfExists('plans');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', ['personal', 'team'])->default('personal');
            $table->uuid('owner_id');
            $table->uuid('plan_id')->nullable();
            $table->string('country', 2)->nullable();
            $table->string('timezone', 64)->default('UTC');
            $table->string('currency', 3)->default('SAR');
            $table->timestamp('trial_ends_at')->nullable();
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->cascadeOnDelete();
            $table->index('owner_id');
        });

        Schema::create('organization_user', function (Blueprint $table) {
            $table->uuid('organization_id');
            $table->uuid('user_id');
            $table->enum('role', ['owner', 'admin', 'member'])->default('member');
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->primary(['organization_id', 'user_id']);
            $table->index('user_id');
        });

        // current_organization_id FK (added after organizations exists)
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('current_organization_id')->references('id')->on('organizations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_organization_id']);
        });
        Schema::dropIfExists('organization_user');
        Schema::dropIfExists('organizations');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('phone', 32)->nullable()->after('email');
            $table->string('country', 2)->default('SA')->after('phone');
            $table->string('timezone', 64)->default('Asia/Riyadh')->after('country');
            $table->string('locale', 8)->default('ar')->after('timezone');
            $table->string('currency', 3)->default('SAR')->after('locale');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('disabled_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['phone', 'country', 'timezone', 'locale', 'currency', 'last_login_at', 'disabled_at']);
        });
    }
};

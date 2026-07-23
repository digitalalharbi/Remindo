<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Captured copies of outgoing mail for the preview environment, viewable
        // by super admins under Admin → Mail log. Never used in production.
        Schema::create('mail_previews', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('to')->nullable();
            $table->string('cc')->nullable();
            $table->string('subject')->nullable();
            $table->string('from')->nullable();
            $table->longText('html')->nullable();
            $table->longText('text')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mail_previews');
    }
};

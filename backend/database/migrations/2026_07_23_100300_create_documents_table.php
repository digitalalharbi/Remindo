<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('organization_id');
            $table->uuid('uploaded_by')->nullable();
            $table->string('disk')->default('local');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime', 128);
            $table->unsignedBigInteger('size');
            $table->enum('scan_status', ['pending', 'clean', 'infected', 'skipped'])->default('pending');
            $table->enum('extraction_status', ['none', 'pending', 'completed', 'failed'])->default('none');
            $table->json('extracted')->nullable(); // AI-extracted fields, reviewed before use
            $table->timestamps();

            $table->foreign('organization_id')->references('id')->on('organizations')->cascadeOnDelete();
            $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            $table->index('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

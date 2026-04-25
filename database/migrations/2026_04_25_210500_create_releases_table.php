<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('releases', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->string('version')->nullable();
            $table->string('suggested_version')->nullable();
            $table->string('release_type', 30)->nullable();
            $table->longText('notes')->nullable();
            $table->string('status', 20)->default('detected');
            $table->json('changes_detected')->nullable();
            $table->json('files_affected')->nullable();
            $table->string('risk_level', 20)->default('low');
            $table->string('source_commit_sha', 120)->nullable()->unique();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('releases');
    }
};

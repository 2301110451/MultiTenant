<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployment_snapshots', function (Blueprint $table): void {
            $table->id();
            $table->string('version', 64)->unique();
            $table->string('artifact_digest', 191)->nullable()->index();
            $table->string('artifact_uri', 500)->nullable();
            $table->string('code_reference', 191)->nullable()->index();
            $table->string('lockfile_hash', 191)->nullable();
            $table->string('config_hash', 191)->nullable();
            $table->json('metadata')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_at_snapshot')->nullable();
            $table->boolean('is_stable')->default(false)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_snapshots');
    }
};

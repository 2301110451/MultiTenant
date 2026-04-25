<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('global_update_audit_logs')) {
            return;
        }

        Schema::create('global_update_audit_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 60);
            $table->string('status', 20);
            $table->string('message', 500)->nullable();
            $table->string('scope', 30)->nullable();
            $table->string('update_type', 30)->nullable();
            $table->string('version', 50)->nullable();
            $table->unsignedBigInteger('github_release_id')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['action', 'status']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('global_update_audit_logs');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployment_candidates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('update_event_id')->constrained('update_events')->cascadeOnDelete();
            $table->string('risk_level', 16)->index();
            $table->unsignedTinyInteger('risk_score')->default(0);
            $table->text('change_summary')->nullable();
            $table->json('affected_modules')->nullable();
            $table->string('blast_radius', 32)->nullable();
            $table->string('status', 32)->default('pending_review')->index();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('rejected_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->string('decision_note', 500)->nullable();
            $table->timestamps();

            $table->unique('update_event_id');
            $table->index(['risk_level', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_candidates');
    }
};

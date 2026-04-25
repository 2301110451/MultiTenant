<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployment_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deployment_candidate_id')->constrained('deployment_candidates')->cascadeOnDelete();
            $table->foreignId('snapshot_id')->nullable()->constrained('deployment_snapshots')->nullOnDelete();
            $table->string('status', 32)->default('pending_validation')->index();
            $table->string('environment', 32)->default('staging')->index();
            $table->string('strategy', 32)->default('blue_green');
            $table->unsignedBigInteger('requested_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->string('validation_workflow_run_id', 64)->nullable()->index();
            $table->json('validation_report')->nullable();
            $table->json('health_metrics')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->timestamp('deployed_at')->nullable();
            $table->timestamp('rolled_back_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['environment', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployment_runs');
    }
};

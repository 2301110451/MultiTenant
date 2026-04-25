<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rollback_runs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('deployment_run_id')->nullable()->constrained('deployment_runs')->nullOnDelete();
            $table->foreignId('to_snapshot_id')->constrained('deployment_snapshots')->cascadeOnDelete();
            $table->string('trigger_type', 32)->default('manual');
            $table->string('status', 32)->default('started')->index();
            $table->unsignedBigInteger('triggered_by')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rollback_runs');
    }
};

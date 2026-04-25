<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('update_events', function (Blueprint $table): void {
            $table->id();
            $table->string('source', 32)->default('github');
            $table->string('delivery_id', 128)->unique();
            $table->string('event_type', 64);
            $table->string('ref', 255)->nullable();
            $table->string('commit_sha', 64)->nullable()->index();
            $table->string('tag', 128)->nullable()->index();
            $table->json('payload');
            $table->json('normalized');
            $table->string('status', 32)->default('received')->index();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->text('processing_error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('update_events');
    }
};

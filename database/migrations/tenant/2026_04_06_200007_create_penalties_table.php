<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('penalties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('damage_report_id')->nullable()->constrained('damages')->nullOnDelete();
            $table->foreignId('reservation_id')->nullable()->constrained('reservations')->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->unsignedInteger('days_overdue')->default(0);
            $table->string('computed_rule')->nullable();
            $table->string('status')->default('unpaid');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('penalties');
    }
};

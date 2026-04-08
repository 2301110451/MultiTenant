<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_applications', function (Blueprint $table) {
            $table->id();
            $table->string('barangay_name');
            $table->foreignId('plan_id')->nullable()->constrained()->nullOnDelete();
            $table->string('secretary_email');
            $table->text('secretary_password_encrypted');
            $table->string('captain_email');
            $table->text('captain_password_encrypted');
            $table->text('notes')->nullable();
            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('provisioned_tenant_id')->nullable()->constrained('tenants')->nullOnDelete();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_applications');
    }
};

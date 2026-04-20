<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->create('tenant_settings', function (Blueprint $table) {
            $table->id();
            $table->string('branding_name')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('accent_color', 20)->nullable();
            $table->boolean('compact_layout')->default(false);
            $table->json('module_toggles')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('tenant_settings');
    }
};

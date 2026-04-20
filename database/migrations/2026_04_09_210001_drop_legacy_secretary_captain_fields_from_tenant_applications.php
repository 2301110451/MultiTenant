<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_applications', function (Blueprint $table) {
            $table->dropColumn([
                'secretary_email',
                'secretary_password_encrypted',
                'captain_email',
                'captain_password_encrypted',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('tenant_applications', function (Blueprint $table) {
            $table->string('secretary_email')->nullable();
            $table->text('secretary_password_encrypted')->nullable();
            $table->string('captain_email')->nullable();
            $table->text('captain_password_encrypted')->nullable();
        });
    }
};

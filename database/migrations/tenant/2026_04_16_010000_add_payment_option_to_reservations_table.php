<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('reservations', function (Blueprint $table): void {
            $table->string('payment_option')->nullable()->after('purpose');
        });
    }

    public function down(): void
    {
        Schema::connection('tenant')->table('reservations', function (Blueprint $table): void {
            $table->dropColumn('payment_option');
        });
    }
};

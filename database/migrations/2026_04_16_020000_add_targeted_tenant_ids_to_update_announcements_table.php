<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('update_announcements', function (Blueprint $table): void {
            $table->json('targeted_tenant_ids')->nullable()->after('audience');
        });
    }

    public function down(): void
    {
        Schema::table('update_announcements', function (Blueprint $table): void {
            $table->dropColumn('targeted_tenant_ids');
        });
    }
};

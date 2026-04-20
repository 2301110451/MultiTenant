<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_applications', function (Blueprint $table) {
            $table->string('tenant_admin_email')->nullable()->after('plan_id');
            $table->text('tenant_admin_password_encrypted')->nullable()->after('tenant_admin_email');
            $table->string('staff_email')->nullable()->after('tenant_admin_password_encrypted');
            $table->text('staff_password_encrypted')->nullable()->after('staff_email');
        });

        DB::table('tenant_applications')
            ->whereNull('tenant_admin_email')
            ->update([
                'tenant_admin_email' => DB::raw('secretary_email'),
                'tenant_admin_password_encrypted' => DB::raw('secretary_password_encrypted'),
                'staff_email' => DB::raw('captain_email'),
                'staff_password_encrypted' => DB::raw('captain_password_encrypted'),
            ]);
    }

    public function down(): void
    {
        Schema::table('tenant_applications', function (Blueprint $table) {
            $table->dropColumn([
                'tenant_admin_email',
                'tenant_admin_password_encrypted',
                'staff_email',
                'staff_password_encrypted',
            ]);
        });
    }
};

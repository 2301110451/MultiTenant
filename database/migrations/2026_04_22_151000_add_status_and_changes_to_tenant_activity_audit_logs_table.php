<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenant_activity_audit_logs', function (Blueprint $table): void {
            if (! Schema::hasColumn('tenant_activity_audit_logs', 'status')) {
                $table->string('status', 20)->default('success')->after('event_key');
                $table->index(['status', 'created_at']);
            }

            if (! Schema::hasColumn('tenant_activity_audit_logs', 'before_values')) {
                $table->json('before_values')->nullable()->after('target_label');
            }

            if (! Schema::hasColumn('tenant_activity_audit_logs', 'after_values')) {
                $table->json('after_values')->nullable()->after('before_values');
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenant_activity_audit_logs', function (Blueprint $table): void {
            if (Schema::hasColumn('tenant_activity_audit_logs', 'status')) {
                $table->dropIndex('tenant_activity_audit_logs_status_created_at_index');
                $table->dropColumn('status');
            }

            if (Schema::hasColumn('tenant_activity_audit_logs', 'before_values')) {
                $table->dropColumn('before_values');
            }

            if (Schema::hasColumn('tenant_activity_audit_logs', 'after_values')) {
                $table->dropColumn('after_values');
            }
        });
    }
};

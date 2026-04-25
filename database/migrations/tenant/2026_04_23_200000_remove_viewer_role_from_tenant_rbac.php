<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $conn = 'tenant';

        DB::connection($conn)->table('users')
            ->where('role', 'viewer')
            ->update(['role' => 'resident']);

        $viewerRoleId = DB::connection($conn)->table('roles')->where('name', 'viewer')->value('id');
        if ($viewerRoleId === null) {
            return;
        }

        $residentRoleId = DB::connection($conn)->table('roles')->where('name', 'resident')->value('id');
        if ($residentRoleId !== null) {
            $viewerUserIds = DB::connection($conn)->table('role_user')
                ->where('role_id', $viewerRoleId)
                ->pluck('user_id');

            foreach ($viewerUserIds as $userId) {
                DB::connection($conn)->table('role_user')->insertOrIgnore([
                    'role_id' => $residentRoleId,
                    'user_id' => $userId,
                ]);
            }
        }

        DB::connection($conn)->table('role_user')->where('role_id', $viewerRoleId)->delete();
        DB::connection($conn)->table('permission_role')->where('role_id', $viewerRoleId)->delete();
        DB::connection($conn)->table('roles')->where('id', $viewerRoleId)->delete();
    }

    public function down(): void
    {
        $conn = 'tenant';

        $exists = DB::connection($conn)->table('roles')->where('name', 'viewer')->exists();
        if ($exists) {
            return;
        }

        $viewerRoleId = DB::connection($conn)->table('roles')->insertGetId([
            'name' => 'viewer',
            'guard_name' => 'tenant',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $permissionIds = DB::connection($conn)->table('permissions')
            ->whereIn('name', ['facilities.view', 'reservations.view', 'reports.view'])
            ->pluck('id');

        foreach ($permissionIds as $permissionId) {
            DB::connection($conn)->table('permission_role')->insertOrIgnore([
                'role_id' => $viewerRoleId,
                'permission_id' => $permissionId,
            ]);
        }
    }
};

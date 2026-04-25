<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Staff could see Settings in the sidebar (canManageTenant) but lacked RBAC
     * permissions, causing 403. Grant settings here for every existing tenant DB.
     *
     */
    public function up(): void
    {
        $conn = 'tenant';

        $attach = [
            'staff' => ['settings.view', 'settings.update'],
        ];

        foreach ($attach as $roleName => $permissionNames) {
            $roleId = DB::connection($conn)->table('roles')->where('name', $roleName)->value('id');
            if ($roleId === null) {
                continue;
            }

            foreach ($permissionNames as $permissionName) {
                $permissionId = DB::connection($conn)->table('permissions')->where('name', $permissionName)->value('id');
                if ($permissionId === null) {
                    continue;
                }

                $exists = DB::connection($conn)->table('permission_role')
                    ->where('role_id', $roleId)
                    ->where('permission_id', $permissionId)
                    ->exists();

                if (! $exists) {
                    DB::connection($conn)->table('permission_role')->insert([
                        'permission_id' => $permissionId,
                        'role_id' => $roleId,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        $conn = 'tenant';

        $rows = [
            ['staff', 'settings.view'],
            ['staff', 'settings.update'],
        ];

        foreach ($rows as [$roleName, $permissionName]) {
            $roleId = DB::connection($conn)->table('roles')->where('name', $roleName)->value('id');
            $permissionId = DB::connection($conn)->table('permissions')->where('name', $permissionName)->value('id');
            if ($roleId === null || $permissionId === null) {
                continue;
            }

            DB::connection($conn)->table('permission_role')
                ->where('role_id', $roleId)
                ->where('permission_id', $permissionId)
                ->delete();
        }
    }
};

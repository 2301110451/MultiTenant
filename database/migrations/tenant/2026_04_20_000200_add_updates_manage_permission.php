<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $name = 'updates.manage';
        $permissionId = DB::connection('tenant')->table('permissions')
            ->where('name', $name)
            ->where('guard_name', 'tenant')
            ->value('id');

        if ($permissionId === null) {
            $permissionId = DB::connection('tenant')->table('permissions')->insertGetId([
                'name' => $name,
                'guard_name' => 'tenant',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (['tenant_admin', 'staff'] as $roleName) {
            $roleId = DB::connection('tenant')->table('roles')->where('name', $roleName)->value('id');
            if ($roleId === null) {
                continue;
            }

            DB::connection('tenant')->table('permission_role')->insertOrIgnore([
                'role_id' => $roleId,
                'permission_id' => $permissionId,
            ]);
        }
    }
};

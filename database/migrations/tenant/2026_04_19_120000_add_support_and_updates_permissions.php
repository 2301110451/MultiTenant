<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $names = ['support.view', 'updates.view'];

        foreach ($names as $name) {
            $exists = DB::connection('tenant')->table('permissions')
                ->where('name', $name)
                ->where('guard_name', 'tenant')
                ->exists();
            if ($exists) {
                continue;
            }
            DB::connection('tenant')->table('permissions')->insert([
                'name' => $name,
                'guard_name' => 'tenant',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $roleNames = ['tenant_admin', 'staff', 'resident'];
        foreach ($roleNames as $roleName) {
            $roleId = DB::connection('tenant')->table('roles')->where('name', $roleName)->value('id');
            if ($roleId === null) {
                continue;
            }
            $permIds = DB::connection('tenant')->table('permissions')
                ->whereIn('name', $names)
                ->pluck('id');
            foreach ($permIds as $permissionId) {
                DB::connection('tenant')->table('permission_role')->insertOrIgnore([
                    'role_id' => $roleId,
                    'permission_id' => $permissionId,
                ]);
            }
        }
    }
};

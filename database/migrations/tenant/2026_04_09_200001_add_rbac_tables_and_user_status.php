<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('tenant')->table('users', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('phone');
        });

        Schema::connection('tenant')->create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('tenant');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::connection('tenant')->create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('guard_name')->default('tenant');
            $table->timestamps();
            $table->unique(['name', 'guard_name']);
        });

        Schema::connection('tenant')->create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['role_id', 'user_id']);
        });

        Schema::connection('tenant')->create('permission_role', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['permission_id', 'role_id']);
        });

        Schema::connection('tenant')->create('permission_user', function (Blueprint $table) {
            $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->primary(['permission_id', 'user_id']);
        });

        $defaultPermissions = [
            'facilities.view', 'facilities.create', 'facilities.update', 'facilities.delete',
            'reservations.view', 'reservations.create', 'reservations.update', 'reservations.delete',
            'reports.view',
            'users.view', 'users.create', 'users.update', 'users.delete', 'users.activate',
            'settings.view', 'settings.update',
        ];

        foreach ($defaultPermissions as $permission) {
            DB::connection('tenant')->table('permissions')->insert([
                'name' => $permission,
                'guard_name' => 'tenant',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $roles = [
            'tenant_admin' => $defaultPermissions,
            'staff' => [
                'facilities.view', 'facilities.create', 'facilities.update',
                'reservations.view', 'reservations.create', 'reservations.update',
                'reports.view', 'users.view',
            ],
            'viewer' => [
                'facilities.view', 'reservations.view', 'reports.view',
            ],
            'resident' => [
                'facilities.view',
                'reservations.view', 'reservations.create', 'reservations.update',
            ],
        ];

        foreach ($roles as $roleName => $permissions) {
            $roleId = DB::connection('tenant')->table('roles')->insertGetId([
                'name' => $roleName,
                'guard_name' => 'tenant',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $permissionIds = DB::connection('tenant')->table('permissions')
                ->whereIn('name', $permissions)
                ->pluck('id')
                ->all();

            foreach ($permissionIds as $permissionId) {
                DB::connection('tenant')->table('permission_role')->insert([
                    'permission_id' => $permissionId,
                    'role_id' => $roleId,
                ]);
            }
        }

        // Legacy role migration selected by project owner:
        // secretary -> tenant_admin, captain -> staff.
        DB::connection('tenant')->table('users')->where('role', 'secretary')->update(['role' => 'tenant_admin']);
        DB::connection('tenant')->table('users')->where('role', 'captain')->update(['role' => 'staff']);
        DB::connection('tenant')->table('users')->where('role', 'barangay_captain')->update(['role' => 'staff']);
        DB::connection('tenant')->table('users')->where('role', 'custodian')->update(['role' => 'staff']);

        $users = DB::connection('tenant')->table('users')->select('id', 'role')->get();
        foreach ($users as $user) {
            $roleId = DB::connection('tenant')->table('roles')
                ->where('name', (string) $user->role)
                ->value('id');

            if ($roleId === null) {
                continue;
            }

            DB::connection('tenant')->table('role_user')->insertOrIgnore([
                'role_id' => $roleId,
                'user_id' => $user->id,
            ]);
        }
    }

    public function down(): void
    {
        Schema::connection('tenant')->dropIfExists('permission_user');
        Schema::connection('tenant')->dropIfExists('permission_role');
        Schema::connection('tenant')->dropIfExists('role_user');
        Schema::connection('tenant')->dropIfExists('permissions');
        Schema::connection('tenant')->dropIfExists('roles');

        Schema::connection('tenant')->table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};

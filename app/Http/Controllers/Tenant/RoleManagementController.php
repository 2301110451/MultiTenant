<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\TenantAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RoleManagementController extends Controller
{
    public function __construct(
        private TenantAuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeRoleAccess($request, 'viewAny');
        $actor = $request->user('tenant');
        $roles = Role::query()->with('permissions')->where('name', '!=', 'viewer')->orderBy('name')->paginate(20);
        $permissions = Permission::query()->orderBy('name')->get();
        $modal = (string) old('_modal_context', (string) $request->query('modal', ''));
        $editRoleId = (int) old('_modal_target_id', (int) $request->query('role', 0));
        $editRole = null;
        if ($modal === 'edit-role' && $editRoleId > 0) {
            $candidate = Role::query()->with('permissions')->find($editRoleId);
            if ($candidate && Gate::forUser($actor)->allows('update', $candidate)) {
                $editRole = $candidate;
            }
        }
        $canCreate = Gate::forUser($actor)->allows('create', Role::class);

        return view('tenant.roles.index', [
            'roles' => $roles,
            'permissions' => $permissions,
            'modal' => $modal,
            'editRole' => $editRole,
            'canCreate' => $canCreate,
        ]);
    }

    public function create(Request $request): View
    {
        $this->authorizeRoleAccess($request, 'create');
        $permissions = Permission::query()->orderBy('name')->get();

        return view('tenant.roles.create', compact('permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeRoleAccess($request, 'create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:tenant.roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:tenant.permissions,id'],
        ]);

        DB::connection('tenant')->transaction(function () use ($data): void {
            $role = Role::query()->create([
                'name' => strtolower(trim($data['name'])),
                'guard_name' => 'tenant',
            ]);

            $role->permissions()->sync($data['permissions'] ?? []);
        });

        $this->audit->log($request, 'tenant_role.created', Role::class, null, [
            'name' => strtolower(trim($data['name'])),
            'permission_count' => count($data['permissions'] ?? []),
        ]);

        return redirect()->route('tenant.roles.index')->with('status', 'Role created.');
    }

    public function edit(Request $request, Role $role): View
    {
        $this->authorizeRoleAccess($request, 'update', $role);
        $permissions = Permission::query()->orderBy('name')->get();
        $role->load('permissions');

        return view('tenant.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $this->authorizeRoleAccess($request, 'update', $role);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:tenant.roles,name,'.$role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:tenant.permissions,id'],
        ]);

        DB::connection('tenant')->transaction(function () use ($role, $data): void {
            $role->update([
                'name' => strtolower(trim($data['name'])),
            ]);

            $role->permissions()->sync($data['permissions'] ?? []);
        });

        $this->audit->log($request, 'tenant_role.updated', Role::class, (int) $role->id, [
            'name' => strtolower(trim($data['name'])),
            'permission_count' => count($data['permissions'] ?? []),
        ]);

        return redirect()->route('tenant.roles.index')->with('status', 'Role updated.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->authorizeRoleAccess($request, 'delete', $role);

        if (in_array($role->name, ['tenant_admin', 'staff', 'resident'], true)) {
            return redirect()->route('tenant.roles.index')->with('status', 'Default roles cannot be deleted.');
        }

        $role->delete();

        $this->audit->log($request, 'tenant_role.deleted', Role::class, (int) $role->id, [
            'name' => $role->name,
        ]);

        return redirect()->route('tenant.roles.index')->with('status', 'Role deleted.');
    }

    private function authorizeRoleAccess(Request $request, string $ability, ?Role $role = null): void
    {
        $actor = $request->user('tenant');
        abort_unless($actor, 403);
        Gate::forUser($actor)->authorize($ability, $role ?? Role::class);
    }
}

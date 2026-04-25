<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\TenantAuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserManagementController extends Controller
{
    public function __construct(
        private TenantAuditLogger $audit,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeUsers($request, 'viewAny');
        $actor = $request->user('tenant');

        $users = User::query()->with('roles')->latest()->paginate(20);
        $roles = Role::query()->where('name', '!=', 'viewer')->orderBy('name')->get();
        $modal = (string) old('_modal_context', (string) $request->query('modal', ''));
        $editUserId = (int) old('_modal_target_id', (int) $request->query('user', 0));
        $editUser = null;
        if ($modal === 'edit-user' && $editUserId > 0) {
            $candidate = User::query()->with('roles')->find($editUserId);
            if ($candidate && Gate::forUser($actor)->allows('update', $candidate)) {
                $editUser = $candidate;
            }
        }
        $canCreate = Gate::forUser($actor)->allows('create', User::class);

        return view('tenant.users.index', compact('users', 'roles', 'modal', 'editUser', 'canCreate'));
    }

    public function create(Request $request): View
    {
        $this->authorizeUsers($request, 'create');

        $roles = Role::query()->where('name', '!=', 'viewer')->orderBy('name')->get();

        return view('tenant.users.create', compact('roles'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeUsers($request, 'create');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:tenant.users,email'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'exists:tenant.roles,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::connection('tenant')->transaction(function () use ($data): void {
            $role = Role::query()->findOrFail((int) $data['role_id']);

            $user = User::query()->create([
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'password' => Hash::make($data['password']),
                'role' => $role->name,
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);

            $user->roles()->sync([$role->id]);
        });

        $this->audit->log($request, 'tenant_user.created', User::class, null, [
            'email' => strtolower($data['email']),
            'role_id' => (int) $data['role_id'],
        ]);

        return redirect()->route('tenant.users.index')->with('status', 'User created.');
    }

    public function edit(Request $request, User $user): View
    {
        $this->authorizeUsers($request, 'update', $user);

        $roles = Role::query()->where('name', '!=', 'viewer')->orderBy('name')->get();

        return view('tenant.users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUsers($request, 'update', $user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:tenant.users,email,'.$user->id],
            'password' => ['nullable', 'string', 'confirmed', Password::defaults()],
            'role_id' => ['required', 'exists:tenant.roles,id'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::connection('tenant')->transaction(function () use ($data, $user): void {
            $role = Role::query()->findOrFail((int) $data['role_id']);

            $payload = [
                'name' => $data['name'],
                'email' => strtolower($data['email']),
                'role' => $role->name,
                'is_active' => (bool) ($data['is_active'] ?? false),
            ];

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $user->update($payload);
            $user->roles()->sync([$role->id]);
        });

        $this->audit->log($request, 'tenant_user.updated', User::class, (int) $user->id, [
            'email' => strtolower($data['email']),
            'role_id' => (int) $data['role_id'],
        ]);

        return redirect()->route('tenant.users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeUsers($request, 'delete', $user);

        if ((int) $request->user('tenant')?->id === (int) $user->id) {
            return redirect()->route('tenant.users.index')->with('status', 'You cannot delete your own account.');
        }

        $user->delete();

        $this->audit->log($request, 'tenant_user.deleted', User::class, (int) $user->id);

        return redirect()->route('tenant.users.index')->with('status', 'User deleted.');
    }

    private function authorizeUsers(Request $request, string $ability, ?User $target = null): void
    {
        $actor = $request->user('tenant');
        abort_unless($actor, 403);
        Gate::forUser($actor)->authorize($ability, $target ?? User::class);
    }
}

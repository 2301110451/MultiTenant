<?php

namespace App\Http\Controllers\Auth;

use App\Enums\TenantRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RecaptchaService;
use App\Support\Tenancy;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        if (Tenancy::isCentralHost(request()->getHost())) {
            abort(403, 'Registration is not available on the central administration portal.');
        }

        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        if (Tenancy::isCentralHost($request->getHost())) {
            abort(403, 'Registration is not available on the central administration portal.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'g-recaptcha-response' => [app(RecaptchaService::class)->enabled() ? 'required' : 'nullable'],
        ]);

        if (app(RecaptchaService::class)->enabled()) {
            $ok = app(RecaptchaService::class)
                ->verifyV3($request->string('g-recaptcha-response')->toString(), 'tenant_register', $request->ip());

            if (! $ok) {
                throw ValidationException::withMessages([
                    'g-recaptcha-response' => 'Captcha verification failed. Please try again.',
                ]);
            }
        }

        $user = User::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => TenantRole::Resident,
        ]);

        $user->syncRbacRoleFromColumn();

        event(new Registered($user));

        Auth::guard('tenant')->login($user);

        return redirect(route('dashboard', absolute: false));
    }
}

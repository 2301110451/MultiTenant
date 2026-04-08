<?php

namespace App\Http\Controllers\Auth;

use App\Enums\TenantRole;
use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantGoogleOAuthRedirectService;
use App\Support\Tenancy;
use App\Support\TenantSuspendedView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\Response;

class TenantGoogleAuthController extends Controller
{
    public function __construct(
        private TenantGoogleOAuthRedirectService $googleOAuthRedirects,
    ) {}

    public function redirect(): RedirectResponse
    {
        if (Tenancy::isCentralHost(request()->getHost())) {
            abort(403, 'Google authentication is only available for tenant portals.');
        }

        $state = $this->encodeTenantState(request()->getHost());

        $redirectUri = $this->googleRedirectUri();

        return Socialite::driver('google')
            ->stateless()
            ->redirectUrl($redirectUri)
            ->with(['state' => $state])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        $tenantHost = $this->decodeTenantState((string) $request->query('state'));

        if ($tenantHost === null || Tenancy::isCentralHost($tenantHost)) {
            abort(422, 'Google sign-in session is invalid. Please start again from your barangay login page.');
        }

        $tenant = $this->tenantForPortalHost($tenantHost);
        if ($tenant === null) {
            return $this->redirectToTenantLogin($tenantHost, 'This barangay portal could not be found.');
        }

        if ($tenant->status !== 'active') {
            return TenantSuspendedView::response($tenant);
        }

        $tenant->configureTenantConnection();

        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->redirectUrl($this->googleRedirectUri())
                ->user();
        } catch (\Throwable) {
            return $this->redirectToTenantLogin($tenantHost, 'Google sign-in failed. Please try again.');
        }
        $email = Str::lower((string) $googleUser->getEmail());

        if ($email === '') {
            return $this->redirectToTenantLogin($tenantHost, 'Google account did not provide an email address.');
        }

        $user = $this->firstOrCreateTenantUserFromGoogle($email, $googleUser->getName());

        $token = Str::random(64);
        Cache::put(
            $this->googleFinalizeCacheKey($token),
            [
                'tenant' => strtolower($tenantHost),
                'email' => $user->email,
                'name' => $googleUser->getName(),
            ],
            now()->addMinutes(5),
        );

        $portalBase = rtrim(Tenancy::tenantPortalUrl($tenantHost), '/');
        $finalizePath = route('tenant.google.finalize', ['token' => $token], false);

        return redirect()->to($portalBase.$finalizePath);
    }

    public function finalize(Request $request): RedirectResponse
    {
        if (Tenancy::isCentralHost($request->getHost())) {
            abort(403, 'Google authentication finalization is only available for tenant portals.');
        }

        $token = (string) $request->query('token', '');
        if ($token === '') {
            return redirect()->route('login')
                ->withErrors(['email' => 'Google sign-in session is invalid. Please try again.']);
        }

        $data = Cache::pull($this->googleFinalizeCacheKey($token));
        if (! is_array($data)) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Google sign-in session expired. Please try again.']);
        }

        $tenantHost = strtolower((string) ($data['tenant'] ?? ''));
        $email = Str::lower((string) ($data['email'] ?? ''));

        if ($tenantHost === '' || $email === '') {
            return redirect()->route('login')
                ->withErrors(['email' => 'Google sign-in session is invalid. Please try again.']);
        }

        if (strtolower($request->getHost()) !== $tenantHost) {
            return $this->redirectToTenantLogin($tenantHost, 'Please complete Google sign-in on the correct barangay portal.');
        }

        $user = $this->firstOrCreateTenantUserFromGoogle(
            $email,
            isset($data['name']) ? (string) $data['name'] : null,
        );

        Auth::guard('tenant')->login($user);
        $request->session()->regenerate();

        return redirect()->to($this->googleOAuthRedirects->pathAfterLogin($user));
    }

    private function firstOrCreateTenantUserFromGoogle(string $email, ?string $googleName): User
    {
        return User::query()->firstOrCreate(
            ['email' => $email],
            [
                'name' => $this->displayNameForNewGoogleUser($email, $googleName),
                'password' => Hash::make(Str::random(40)),
                'role' => TenantRole::Resident,
            ],
        );
    }

    private function displayNameForNewGoogleUser(string $email, ?string $googleName): string
    {
        if ($googleName !== null && trim($googleName) !== '') {
            return $googleName;
        }

        $local = trim((string) Str::of($email)->before('@')->replace(['.', '_', '-'], ' '));

        return Str::title($local !== '' ? $local : 'Resident User');
    }

    private function tenantForPortalHost(string $host): ?Tenant
    {
        $domain = Domain::query()->where('domain', strtolower($host))->first();

        return $domain?->tenant()->first();
    }

    private function redirectToTenantLogin(string $tenantHost, string $message): RedirectResponse
    {
        $base = rtrim(Tenancy::tenantPortalUrl($tenantHost), '/');

        return redirect()->to($base.'/login')
            ->withErrors(['email' => $message]);
    }

    private function googleFinalizeCacheKey(string $token): string
    {
        return 'tenant_google_finalize:'.$token;
    }

    private function googleStateCacheKey(string $token): string
    {
        return 'tenant_google_state:'.$token;
    }

    private function googleRedirectUri(): string
    {
        $configured = trim((string) config('services.google.redirect', ''));

        if ($configured !== '') {
            return $configured;
        }

        return route('tenant.google.callback', [], true);
    }

    private function encodeTenantState(string $host): string
    {
        $token = Str::random(64);

        Cache::put(
            $this->googleStateCacheKey($token),
            ['tenant' => strtolower($host), 'ts' => now()->timestamp],
            now()->addMinutes(15),
        );

        return $token;
    }

    private function decodeTenantState(string $state): ?string
    {
        if ($state === '') {
            return null;
        }

        $data = Cache::pull($this->googleStateCacheKey($state));
        if (! is_array($data)) {
            return null;
        }

        $tenant = strtolower((string) ($data['tenant'] ?? ''));
        $ts = (int) ($data['ts'] ?? 0);

        if ($tenant === '' || $ts <= 0 || now()->diffInMinutes(\Carbon\Carbon::createFromTimestamp($ts), false) < -15) {
            return null;
        }

        return $tenant;
    }
}

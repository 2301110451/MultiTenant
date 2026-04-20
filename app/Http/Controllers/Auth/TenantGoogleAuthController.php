<?php

namespace App\Http\Controllers\Auth;

use App\Enums\TenantRole;
use App\Http\Controllers\Controller;
use App\Models\Domain;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantGoogleOAuthRedirectService;
use App\Support\Tenancy;
use App\Support\TenantGoogleOAuthRedirectUri;
use App\Support\TenantSuspendedView;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;

class TenantGoogleAuthController extends Controller
{
    private const SESSION_TENANT_HOST = 'google_oauth_tenant_host';

    private const OAUTH_STATE_CACHE_PREFIX = 'tenant_google_oauth:v1:';

    private const OAUTH_STATE_TTL_SECONDS = 600;

    public function __construct(
        private TenantGoogleOAuthRedirectService $googleOAuthRedirects,
    ) {}

    public function redirect(): RedirectResponse
    {
        if (Tenancy::isCentralHost(request()->getHost())) {
            abort(403, 'Google authentication is only available for tenant portals.');
        }

        if (! $this->googleOAuthConfigured()) {
            return redirect()->route('login')
                ->withErrors([
                    'email' => 'Google sign-in is not configured. In .env set GOOGLE_CLIENT_ID and GOOGLE_CLIENT_SECRET from Google Cloud Console → APIs & Services → Credentials (OAuth 2.0 Client). This is not the same as MAIL_PASSWORD for sending email.',
                ]);
        }

        $request = request();
        $host = strtolower($request->getHost());

        // Session backup when authorize + callback share the same host (e.g. production domains).
        $request->session()->put(self::SESSION_TENANT_HOST, $host);

        // Google often rejects *.localhost (and similar) as redirect URIs. We use 127.0.0.1 + port for OAuth
        // when appropriate and carry the real tenant host in `state` (opaque id → cache).
        $oauthState = Str::random(40);
        Cache::put(
            self::OAUTH_STATE_CACHE_PREFIX.$oauthState,
            ['tenant_host' => $host],
            now()->addSeconds(self::OAUTH_STATE_TTL_SECONDS)
        );

        try {
            // `with()` only for this response; callback uses a fresh driver (avoid merging `state` into token POST).
            return Socialite::driver('google')
                ->stateless()
                ->redirectUrl(TenantGoogleOAuthRedirectUri::resolve($request))
                ->with(['state' => $oauthState])
                ->redirect();
        } catch (\Throwable $e) {
            Log::warning('tenant_google_oauth_redirect_failed', [
                'host' => $host,
                'message' => $e->getMessage(),
            ]);
            report($e);

            return redirect()->route('login')
                ->withErrors([
                    'email' => 'Google sign-in could not start. Check GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET, run php artisan config:clear, and add this exact redirect URI in Google Cloud: '.TenantGoogleOAuthRedirectUri::resolve($request),
                ]);
        }
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')
                ->stateless()
                ->with([])
                ->redirectUrl(TenantGoogleOAuthRedirectUri::resolve($request))
                ->user();
        } catch (InvalidStateException $e) {
            return $this->redirectOAuthLoginWithError(
                $request,
                'Your Google sign-in session expired or cookies are blocked. Please sign in with email and password.',
            );
        } catch (\Throwable $e) {
            Log::warning('tenant_google_oauth_token_failed', [
                'callback_host' => $request->getHost(),
                'message' => $e->getMessage(),
            ]);
            report($e);

            return $this->redirectOAuthLoginWithError(
                $request,
                'Google sign-in failed. Confirm the redirect URI in Google Cloud matches this app (including host and port), then try again.',
            );
        }

        $tenantHost = $this->resolveTenantHostFromOAuth($request);

        if ($tenantHost === '') {
            return $this->redirectOAuthLoginWithError(
                $request,
                'Google sign-in could not verify your barangay portal. For local *.localhost tenants, add http://127.0.0.1:YOUR_PORT/auth/google/callback to Authorized redirect URIs in Google Cloud Console, then try again.',
            );
        }

        $tenant = $this->tenantForPortalHost($tenantHost);
        if ($tenant === null) {
            return $this->redirectToTenantLogin($tenantHost, 'This barangay portal could not be found.');
        }

        if ($tenant->status !== 'active') {
            return TenantSuspendedView::response($tenant);
        }

        $tenant->configureTenantConnection();

        $email = Str::lower((string) $googleUser->getEmail());

        if ($email === '') {
            return $this->redirectToTenantLogin($tenantHost, 'Google account did not provide an email address.');
        }

        $user = $this->findOrCreateTenantUserFromGoogle($email, $googleUser->getName());
        $user->syncRbacRoleFromColumn();
        $user->load('roles');

        if (! $user->is_active) {
            return $this->redirectToTenantLogin($tenantHost, 'This account is inactive. Please contact your Tenant Admin.');
        }

        Auth::guard('tenant')->login($user);
        $request->session()->regenerate();

        return redirect()->to($this->googleOAuthRedirects->pathAfterLogin($user));
    }

    public function finalize(Request $request): RedirectResponse
    {
        return redirect()->route('login')
            ->withErrors(['email' => 'Please sign in from the login page.']);
    }

    private function redirectOAuthLoginWithError(Request $request, string $message): RedirectResponse
    {
        $tenantHost = $this->peekTenantHostFromOAuthState($request);
        if ($tenantHost !== '') {
            return $this->redirectToTenantLogin($tenantHost, $message);
        }

        return redirect()->route('login')->withErrors(['email' => $message]);
    }

    /**
     * Read tenant host from cache without consuming it (used when OAuth fails before resolveTenantHostFromOAuth runs).
     */
    private function peekTenantHostFromOAuthState(Request $request): string
    {
        $state = trim((string) $request->query('state', ''));
        if ($state === '') {
            return '';
        }

        $data = Cache::get(self::OAUTH_STATE_CACHE_PREFIX.$state);
        if (! is_array($data)) {
            return '';
        }

        $host = strtolower(trim((string) ($data['tenant_host'] ?? '')));

        return $host;
    }

    /**
     * Resolve tenant portal host: OAuth `state` → cache (set on redirect), then session, then request host.
     */
    private function resolveTenantHostFromOAuth(Request $request): string
    {
        $state = trim((string) $request->query('state', ''));
        if ($state !== '') {
            $data = Cache::pull(self::OAUTH_STATE_CACHE_PREFIX.$state);
            if (is_array($data)) {
                $host = strtolower(trim((string) ($data['tenant_host'] ?? '')));
                if ($host !== '') {
                    return $host;
                }
            }
        }

        $fromSession = strtolower(trim((string) $request->session()->pull(self::SESSION_TENANT_HOST, '')));
        if ($fromSession !== '') {
            return $fromSession;
        }

        $h = strtolower($request->getHost());
        if (! Tenancy::isCentralHost($h)) {
            return $h;
        }

        return '';
    }

    /**
     * Existing barangay accounts keep their role (admin, staff, viewer, resident). New Google sign-ups become residents.
     */
    private function findOrCreateTenantUserFromGoogle(string $email, ?string $googleName): User
    {
        $existing = User::query()->where('email', $email)->first();

        if ($existing !== null) {
            if (($existing->name === null || trim((string) $existing->name) === '') && $googleName !== null && trim($googleName) !== '') {
                $existing->forceFill(['name' => $googleName])->save();
            }

            return $existing->fresh();
        }

        return User::query()->create([
            'name' => $this->displayNameForNewGoogleUser($email, $googleName),
            'email' => $email,
            'password' => Hash::make(Str::random(40)),
            'role' => TenantRole::Resident,
            'is_active' => true,
        ]);
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

    private function googleOAuthConfigured(): bool
    {
        $id = trim((string) config('services.google.client_id', ''));
        $secret = trim((string) config('services.google.client_secret', ''));

        return $id !== '' && $secret !== '';
    }
}

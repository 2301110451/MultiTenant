<?php

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Single source of truth for Socialite redirect_uri (must match Google Cloud Authorized redirect URIs exactly).
 */
final class TenantGoogleOAuthRedirectUri
{
    public static function resolve(Request $request): string
    {
        $configured = trim((string) config('services.google.redirect', ''));

        if ($configured !== '') {
            return self::normalize($configured);
        }

        $host = strtolower($request->getHost());
        $path = route('tenant.google.callback', [], false);

        if (self::shouldUseLoopback($host)) {
            $portPart = self::loopbackPortPart($request);

            return self::normalize('http://127.0.0.1'.$portPart.$path);
        }

        return self::normalize(rtrim($request->getSchemeAndHttpHost(), '/').$path);
    }

    public static function normalize(string $uri): string
    {
        return rtrim(trim($uri), '/');
    }

    public static function shouldUseLoopback(string $host): bool
    {
        if (in_array($host, ['127.0.0.1', 'localhost', '::1', '[::1]'], true)) {
            return true;
        }

        $suffix = strtolower(trim((string) config('tenancy.tenant_domain_suffix', 'localhost')));

        return $suffix !== '' && str_ends_with($host, '.'.$suffix);
    }

    public static function loopbackPortPart(Request $request): string
    {
        $h = strtolower($request->getHost());
        if ($request->getScheme() === 'http' && (int) $request->getPort() === 80
            && in_array($h, ['127.0.0.1', 'localhost'], true)) {
            return '';
        }

        $port = (int) $request->getPort();

        if ($port && ! in_array($port, [80, 443], true)) {
            return ':'.$port;
        }

        $fromAppUrl = parse_url((string) config('app.url'), PHP_URL_PORT);
        if ($fromAppUrl && ! in_array((int) $fromAppUrl, [80, 443], true)) {
            return ':'.(int) $fromAppUrl;
        }

        $fromEnv = (int) config('services.google.loopback_port', 0);
        if ($fromEnv > 0 && ! in_array($fromEnv, [80, 443], true)) {
            return ':'.$fromEnv;
        }

        return ':8000';
    }
}

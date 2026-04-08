<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Facades\URL;

/**
 * Signed and absolute URLs that must target the central app (not tenant hosts).
 * Laravel otherwise uses the current request root, which breaks links generated on tenant domains.
 */
final class CentralUrl
{
    public static function root(): string
    {
        $url = (string) config('tenancy.central_app_url', '');
        if ($url === '') {
            $url = (string) config('app.url');
        }

        return rtrim($url, '/');
    }

    public static function temporarySignedRoute(string $name, DateTimeInterface $expiration, array $parameters = [], bool $absolute = true): string
    {
        $root = self::root();
        URL::forceRootUrl($root);

        try {
            return URL::temporarySignedRoute($name, $expiration, $parameters, $absolute);
        } finally {
            URL::forceRootUrl(null);
        }
    }
}

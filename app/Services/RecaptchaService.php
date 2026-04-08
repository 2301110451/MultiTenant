<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class RecaptchaService
{
    public function enabled(): bool
    {
        return (bool) config('services.recaptcha.enabled')
            && (string) config('services.recaptcha.site_key') !== ''
            && (string) config('services.recaptcha.secret_key') !== '';
    }

    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        if (! is_string($token) || trim($token) === '') {
            return false;
        }

        $response = Http::asForm()
            ->timeout(8)
            ->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => (string) config('services.recaptcha.secret_key'),
                'response' => $token,
                'remoteip' => $ip,
            ]);

        if (! $response->ok()) {
            return false;
        }

        return (bool) $response->json('success', false);
    }
}

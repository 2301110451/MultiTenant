<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
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

        $response = $this->postSiteVerify([
            'secret' => (string) config('services.recaptcha.secret_key'),
            'response' => $token,
            'remoteip' => $ip,
        ]);

        if (! $response->ok()) {
            return false;
        }

        return (bool) $response->json('success', false);
    }

    public function verifyV3(?string $token, string $expectedAction, ?string $ip = null): bool
    {
        if (! $this->enabled()) {
            return true;
        }

        if (! is_string($token) || trim($token) === '') {
            return false;
        }

        $response = $this->postSiteVerify([
            'secret' => (string) config('services.recaptcha.secret_key'),
            'response' => $token,
            'remoteip' => $ip,
        ]);

        if (! $response->ok()) {
            return false;
        }

        if (! (bool) $response->json('success', false)) {
            return false;
        }

        $action = (string) $response->json('action', '');
        if ($action !== $expectedAction) {
            return false;
        }

        $score = (float) $response->json('score', 0.0);
        $minScore = (float) config('services.recaptcha.min_score', 0.5);

        return $score >= $minScore;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function postSiteVerify(array $body): Response
    {
        return Http::asForm()
            ->timeout(8)
            ->withOptions($this->recaptchaTlsOptions())
            ->post('https://www.google.com/recaptcha/api/siteverify', $body);
    }

    /**
     * @return array{verify: bool|string}
     */
    private function recaptchaTlsOptions(): array
    {
        $ca = (string) config('services.recaptcha.ca_bundle', '');
        if ($ca !== '' && is_readable($ca)) {
            return ['verify' => $ca];
        }

        $verifySsl = (bool) config('services.recaptcha.http_ssl_verify', true);
        if (! $verifySsl && app()->isLocal()) {
            return ['verify' => false];
        }

        return ['verify' => true];
    }
}

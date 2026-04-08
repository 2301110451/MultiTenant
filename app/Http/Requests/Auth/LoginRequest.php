<?php

namespace App\Http\Requests\Auth;

use App\Services\RecaptchaService;
use App\Support\Tenancy;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];

        if ($this->shouldValidateTenantRecaptcha()) {
            $rules['g-recaptcha-response'] = ['required', 'string'];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        if (! $this->shouldValidateTenantRecaptcha()) {
            return;
        }

        $validator->after(function ($validator): void {
            $ok = app(RecaptchaService::class)
                ->verify($this->string('g-recaptcha-response')->toString(), $this->ip());

            if (! $ok) {
                $validator->errors()->add('g-recaptcha-response', 'Captcha verification failed. Please try again.');
            }
        });
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(?string $guard = null): void
    {
        $this->ensureIsNotRateLimited($guard);

        $guard = $guard ?? Auth::getDefaultDriver();

        if (! Auth::guard($guard)->attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey($guard));

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey($guard));
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(?string $guard = null): void
    {
        $guard = $guard ?? Auth::getDefaultDriver();

        if (! RateLimiter::tooManyAttempts($this->throttleKey($guard), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey($guard));

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(?string $guard = null): string
    {
        $guard = $guard ?? Auth::getDefaultDriver();

        return Str::transliterate(Str::lower($this->string('email')).'|'.$guard.'|'.$this->ip());
    }

    private function shouldValidateTenantRecaptcha(): bool
    {
        if (Tenancy::isCentralHost($this->getHost())) {
            return false;
        }

        return app(RecaptchaService::class)->enabled();
    }
}

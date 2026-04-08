<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:test {email? : Inbox to receive the test message}', function (?string $email = null) {
    $driver = (string) config('mail.default');

    if ($driver !== 'smtp') {
        $this->warn('MAIL_MAILER is "'.$driver.'". For real email use MAIL_MAILER=smtp in .env, then php artisan config:clear');

        return self::FAILURE;
    }

    $user = (string) config('mail.mailers.smtp.username');
    $pass = (string) config('mail.mailers.smtp.password');

    if ($user === '' || $pass === '') {
        $this->error('MAIL_USERNAME or MAIL_PASSWORD is empty.');
        $this->line('1. Open .env in the project root.');
        $this->line('2. Set MAIL_USERNAME to your full Gmail address (example: you@gmail.com).');
        $this->line('3. Set MAIL_PASSWORD to a Google App Password (16 characters), not your normal Gmail password.');
        $this->line('   Create one: https://myaccount.google.com/apppasswords (2-Step Verification must be on).');
        $this->line('4. Set MAIL_FROM_ADDRESS to the same Gmail as MAIL_USERNAME.');
        $this->line('5. Run: php artisan config:clear');
        $this->line('6. Run this command again with your email: php artisan mail:test you@gmail.com');

        return self::FAILURE;
    }

    $masked = strlen($user) > 4 ? substr($user, 0, 3).'***'.substr($user, -2) : '***';
    $this->info('Using SMTP as: '.$masked.' (password length: '.strlen($pass).' chars)');

    if ($email === null || $email === '') {
        $this->comment('Config looks OK. Pass an email to send a test message, e.g. php artisan mail:test you@gmail.com');

        return self::SUCCESS;
    }

    try {
        Mail::raw('If you received this, Laravel mail is configured correctly.', function ($message) use ($email) {
            $message->to($email)->subject('Brgy Reservation — mail test');
        });
    } catch (\Throwable $e) {
        $this->error($e->getMessage());

        return self::FAILURE;
    }

    $this->info('Test email sent to '.$email.'. Check inbox and spam folder.');

    return self::SUCCESS;
})->purpose('Check SMTP settings and optionally send a test email');

<?php

namespace App\Services;

use App\Enums\TenantRole;
use App\Mail\BarangayApprovedMail;
use App\Mail\SubscriptionIntentExtensionApprovedMail;
use App\Mail\SubscriptionIntentRejectedMail;
use App\Mail\TenantPortalSuspendedMail;
use App\Mail\UnsubscribeIntentSubmittedMail;
use App\Models\CentralUser;
use App\Models\Tenant;
use App\Models\User;
use App\Support\CentralUrl;
use App\Support\Tenancy;
use Illuminate\Support\Facades\Mail;

class BarangayOfficerNotifier
{
    /**
     * @param  list<string>  $recipientEmails
     */
    public function notifyApproval(
        string $barangayName,
        string $domain,
        array $recipientEmails,
        ?string $tenantAdminEmail = null,
        ?string $tenantAdminPassword = null,
        ?string $staffEmail = null,
        ?string $staffPassword = null,
    ): void
    {
        $this->assertSmtpCredentialsConfigured();

        $domain = strtolower(trim($domain));
        $portalUrl = Tenancy::tenantPortalUrl($domain);
        $unique = array_values(array_unique(array_filter(array_map('strtolower', $recipientEmails))));

        foreach ($unique as $email) {
            Mail::to($email)->send(new BarangayApprovedMail(
                $barangayName,
                $domain,
                $portalUrl,
                $tenantAdminEmail ? strtolower($tenantAdminEmail) : null,
                $tenantAdminPassword,
                $staffEmail ? strtolower($staffEmail) : null,
                $staffPassword,
            ));
        }
    }

    /**
     * Notify tenant admin/staff that tenant portal URL was suspended from central.
     */
    public function notifyPortalSuspended(Tenant $tenant, string $domain): void
    {
        $this->assertSmtpCredentialsConfigured();

        $domain = strtolower(trim($domain));
        $portalUrl = Tenancy::tenantPortalUrl($domain);
        $tenant->configureTenantConnection();
        $emails = User::query()
            ->whereIn('role', [TenantRole::TenantAdmin, TenantRole::Staff])
            ->pluck('email')
            ->all();

        if ($emails === []) {
            return;
        }

        $unique = array_values(array_unique(array_filter(array_map('strtolower', $emails))));

        $subscriptionActionUrl = CentralUrl::temporarySignedRoute(
            'central.subscription-intent.show',
            now()->addDays(30),
            ['tenant' => $tenant->id]
        );

        foreach ($unique as $email) {
            Mail::to($email)->send(new TenantPortalSuspendedMail(
                $tenant->name,
                $domain,
                $portalUrl,
                $subscriptionActionUrl,
            ));
        }
    }

    public function notifyOfficersFromTenantDatabase(Tenant $tenant, string $domain): void
    {
        $tenant->configureTenantConnection();
        $emails = User::query()
            ->whereIn('role', [TenantRole::TenantAdmin, TenantRole::Staff])
            ->pluck('email')
            ->all();

        if ($emails === []) {
            return;
        }

        $this->notifyApproval($tenant->name, $domain, $emails);
    }

    /**
     * Tenant admin/staff: extension request approved by central — portal may be used again.
     */
    public function notifySubscriptionExtensionApproved(Tenant $tenant, string $domain): void
    {
        $this->assertSmtpCredentialsConfigured();

        $domain = strtolower(trim($domain));
        $portalUrl = Tenancy::tenantPortalUrl($domain);
        $tenant->configureTenantConnection();
        $emails = User::query()
            ->whereIn('role', [TenantRole::TenantAdmin, TenantRole::Staff])
            ->pluck('email')
            ->all();

        if ($emails === []) {
            return;
        }

        $unique = array_values(array_unique(array_filter(array_map('strtolower', $emails))));

        foreach ($unique as $email) {
            Mail::to($email)->send(new SubscriptionIntentExtensionApprovedMail($tenant->name, $domain, $portalUrl));
        }
    }

    /**
     * Tenant admin/staff: subscription request (extend or unsubscribe) rejected by central.
     */
    public function notifySubscriptionIntentRejected(Tenant $tenant, string $domain, string $intentType): void
    {
        $this->assertSmtpCredentialsConfigured();

        $domain = strtolower(trim($domain));
        $portalUrl = Tenancy::tenantPortalUrl($domain);
        $intentLabel = $intentType === 'extend' ? 'Extension' : 'Unsubscribe';

        $tenant->configureTenantConnection();
        $emails = User::query()
            ->whereIn('role', [TenantRole::TenantAdmin, TenantRole::Staff])
            ->pluck('email')
            ->all();

        if ($emails === []) {
            return;
        }

        $unique = array_values(array_unique(array_filter(array_map('strtolower', $emails))));

        foreach ($unique as $email) {
            Mail::to($email)->send(new SubscriptionIntentRejectedMail(
                $tenant->name,
                $domain,
                $portalUrl,
                $intentLabel,
            ));
        }
    }

    /**
     * Notify central admins that a suspended tenant asked for full unsubscribe.
     */
    public function notifyCentralAdminsOfUnsubscribeIntent(Tenant $tenant, ?string $message): void
    {
        $this->assertSmtpCredentialsConfigured();

        $tenant->loadMissing('domains');
        $emails = CentralUser::query()->pluck('email')->all();

        if ($emails === []) {
            return;
        }

        $unique = array_values(array_unique(array_filter(array_map('strtolower', $emails))));

        foreach ($unique as $email) {
            Mail::to($email)->send(new UnsubscribeIntentSubmittedMail($tenant, $message));
        }
    }

    public static function mailNotDeliveredToInboxNotice(): ?string
    {
        $driver = (string) config('mail.default');

        if ($driver === 'log') {
            return 'Email is not sent to real inboxes while MAIL_MAILER=log (messages go to storage/logs/laravel.log only). Set MAIL_MAILER=smtp and your SMTP credentials in .env to deliver mail.';
        }

        if ($driver === 'array') {
            return 'Email is captured in memory only (MAIL_MAILER=array). Use MAIL_MAILER=smtp for real delivery.';
        }

        return null;
    }

    /**
     * Gmail returns "530 5.7.0 Authentication Required" when MAIL_USERNAME / MAIL_PASSWORD are missing or wrong.
     */
    private function assertSmtpCredentialsConfigured(): void
    {
        if (config('mail.default') !== 'smtp') {
            return;
        }

        $username = (string) config('mail.mailers.smtp.username');
        $password = (string) config('mail.mailers.smtp.password');

        if ($username !== '' && $password !== '') {
            return;
        }

        throw new \RuntimeException(
            'Gmail rejected the connection because SMTP login is not configured (error 530 = authentication required). '
            .'In your .env file set MAIL_USERNAME to your full Gmail address, MAIL_PASSWORD to a Google App Password '
            .'(not your normal Gmail password; enable 2-Step Verification, then create one at https://myaccount.google.com/apppasswords). '
            .'Set MAIL_FROM_ADDRESS to that same Gmail address. Save the file, then run: php artisan config:clear'
        );
    }
}

<?php

namespace App\Support;

use App\Models\Plan;
use App\Models\TenantSetting;
use App\Models\User;
use Throwable;

final class TenantAppearance
{
    /** Canonical default accent — Blue-600, WCAG AA on white (4.5:1). */
    public const DEFAULT_ACCENT = '#2563EB';

    /** Canonical default sidebar background. */
    public const DEFAULT_SIDEBAR = '#0F172A';

    /** Canonical default page background. */
    public const DEFAULT_BG = '#F8FAFC';

    /**
     * Returns the canonical CSS variable map for the default theme.
     * This is the single source of truth for “what does a new tenant look like”.
     *
     * @return array<string, string>
     */
    public static function defaultThemeTokens(): array
    {
        return [
            '--tenant-accent' => self::DEFAULT_ACCENT,
            '--tenant-accent-soft' => 'rgba(37, 99, 235, 0.07)',
            '--tenant-accent-border' => 'rgba(37, 99, 235, 0.17)',
            '--tenant-nav-active-start' => '#1D4ED8',
            '--tenant-nav-active-end' => self::DEFAULT_ACCENT,
            '--tenant-nav-shadow' => 'rgba(37, 99, 235, 0.28)',
            '--bg-sidebar' => self::DEFAULT_SIDEBAR,
            '--bg-primary' => self::DEFAULT_BG,
        ];
    }

    /**
     * Tailwind class sets keyed by subscription plan slug.
     *
     * @return array<string, string>
     */
    public static function theme(?User $viewer = null): array
    {
        $plan = Tenancy::tenantPlan();
        $slug = $plan ? strtolower($plan->slug) : 'basic';
        $tenant = Tenancy::currentTenant();
        $settings = null;
        if ($tenant) {
            try {
                $settings = TenantSetting::query()->first();
            } catch (Throwable) {
                // Keep auth pages and shell UI available even if tenant DB is not ready yet.
                $settings = null;
            }
        }

        $theme = match ($slug) {
            'premium' => [
                'slug' => 'premium',
                'label' => $plan?->name ?? 'Premium',
                'sidebar' => 'bg-slate-900',
                'sidebarBorder' => 'border-slate-800',
                'brandIcon' => 'bg-gradient-to-br from-yellow-500 to-yellow-700 shadow-yellow-900/50',
                'brandSub' => 'text-yellow-400',
                'navActive' => 'bg-gradient-to-r from-yellow-700 to-yellow-500 text-white shadow shadow-yellow-900/40',
                'navIdle' => 'text-slate-400 hover:text-white hover:bg-slate-800',
                'breadcrumbAccent' => 'text-yellow-600',
                'avatar' => 'bg-yellow-600',
                'heroGradient' => 'from-slate-900 via-yellow-950 to-slate-900',
                'heroAccent' => 'text-yellow-400',
                'button' => 't-btn-primary',
                'panelRing' => 'ring-yellow-500/20',
                'badge' => 'bg-yellow-400/20 text-yellow-700 border-yellow-500/30',
            ],
            'standard' => [
                'slug' => 'standard',
                'label' => $plan?->name ?? 'Standard',
                'sidebar' => 'bg-slate-900',
                'sidebarBorder' => 'border-slate-800',
                'brandIcon' => 'bg-sky-600 shadow-sky-900/50',
                'brandSub' => 'text-sky-400',
                'navActive' => 'bg-sky-600 text-white shadow shadow-sky-900/40',
                'navIdle' => 'text-slate-400 hover:text-white hover:bg-slate-800',
                'breadcrumbAccent' => 'text-sky-600',
                'avatar' => 'bg-sky-600',
                'heroGradient' => 'from-slate-900 via-sky-950 to-slate-900',
                'heroAccent' => 'text-sky-400',
                'button' => 't-btn-primary',
                'panelRing' => 'ring-sky-500/20',
                'badge' => 'bg-sky-400/20 text-sky-300 border-sky-500/30',
            ],
            default => [
                'slug' => 'basic',
                'label' => $plan?->name ?? 'Basic',
                'sidebar' => 'bg-slate-900',
                'sidebarBorder' => 'border-slate-800',
                'brandIcon' => 'bg-blue-600 shadow-blue-900/50',
                'brandSub' => 'text-blue-400',
                'navActive' => 'bg-blue-600 text-white shadow shadow-blue-900/40',
                'navIdle' => 'text-slate-400 hover:text-white hover:bg-slate-800',
                'breadcrumbAccent' => 'text-blue-600',
                'avatar' => 'bg-blue-600',
                'heroGradient' => 'from-slate-900 via-blue-950 to-slate-900',
                'heroAccent' => 'text-blue-400',
                'button' => 't-btn-primary',
                'panelRing' => 'ring-blue-500/20',
                'badge' => 'bg-blue-400/20 text-blue-300 border-blue-500/30',
            ],
        };

        $theme['branding_name'] = $settings?->branding_name;
        // Portal settings (tenant DB) = barangay-wide defaults for accent, page bg, and sidebar.
        $theme['accent_color'] = $settings?->accent_color;
        $theme['background_color'] = $settings?->background_color;
        $theme['sidebar_background_color'] = $settings?->sidebar_background_color;
        $theme['compact_layout'] = (bool) ($settings?->compact_layout ?? false);
        $theme['module_toggles'] = $settings?->module_toggles ?? [];

        // Per-user color overrides are intentionally disabled so tenant admin
        // portal settings apply immediately and consistently to all tenant users.

        return $theme;
    }

    /**
     * CSS custom property assignments for inline `style=""` when a tenant picks a custom accent.
     * Fills nav gradient, soft surfaces, and borders so the whole portal matches the chosen color.
     *
     * @return list<string>
     */
    public static function tenantAccentStyleFragments(?string $accentHex): array
    {
        if ($accentHex === null || $accentHex === '') {
            return [];
        }

        $hex = strtoupper(trim($accentHex));
        if (! preg_match('/^#[0-9A-F]{6}$/', $hex)) {
            return [];
        }

        $r = hexdec(substr($hex, 1, 2));
        $g = hexdec(substr($hex, 3, 2));
        $b = hexdec(substr($hex, 5, 2));

        $rs = (int) max(0, min(255, (int) round($r * 0.78)));
        $gs = (int) max(0, min(255, (int) round($g * 0.78)));
        $bs = (int) max(0, min(255, (int) round($b * 0.78)));
        $start = sprintf('#%02X%02X%02X', $rs, $gs, $bs);

        return [
            '--tenant-accent: '.$hex,
            '--tenant-nav-active-start: '.$start,
            '--tenant-nav-active-end: '.$hex,
            sprintf('--tenant-nav-shadow: rgba(%d,%d,%d,0.32)', $r, $g, $b),
            sprintf('--tenant-accent-soft: rgba(%d,%d,%d,0.11)', $r, $g, $b),
            sprintf('--tenant-accent-border: rgba(%d,%d,%d,0.2)', $r, $g, $b),
        ];
    }

    public static function planAllowsReports(): bool
    {
        $plan = Tenancy::tenantPlan();
        $planAllows = $plan instanceof Plan && $plan->allows('reports');
        $tenant = Tenancy::currentTenant();
        $settings = null;
        if ($tenant) {
            try {
                $settings = TenantSetting::query()->first();
            } catch (Throwable) {
                $settings = null;
            }
        }
        $moduleEnabled = (bool) (($settings?->module_toggles['reports'] ?? true));

        return $planAllows && $moduleEnabled;
    }

    public static function planSummaryBadges(): array
    {
        $plan = Tenancy::tenantPlan();
        if (! $plan) {
            return [];
        }

        $badges = [];
        $badges[] = $plan->name;
        if ($plan->monthly_reservation_limit === null) {
            $badges[] = 'Unlimited reservations';
        } else {
            $badges[] = $plan->monthly_reservation_limit.' reservations / mo';
        }
        if ($plan->allows('reports')) {
            $badges[] = 'Analytics';
        }
        if ($plan->allows('qr_checkin')) {
            $badges[] = 'QR check-in';
        }
        if ($plan->allows('payments')) {
            $badges[] = 'Payments';
        }

        return $badges;
    }
}

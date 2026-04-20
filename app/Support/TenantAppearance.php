<?php

namespace App\Support;

use App\Models\Plan;
use App\Models\TenantSetting;
use App\Models\User;
use Throwable;

final class TenantAppearance
{
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
                'brandIcon' => 'bg-amber-500 shadow-amber-900/50',
                'brandSub' => 'text-amber-400',
                'navActive' => 'bg-amber-500 text-white shadow shadow-amber-900/40',
                'navIdle' => 'text-slate-400 hover:text-white hover:bg-slate-800',
                'breadcrumbAccent' => 'text-amber-600',
                'avatar' => 'bg-amber-500',
                'heroGradient' => 'from-slate-900 via-amber-950 to-slate-900',
                'heroAccent' => 'text-amber-400',
                'button' => 't-btn-primary',
                'panelRing' => 'ring-amber-500/20',
                'badge' => 'bg-amber-400/20 text-amber-300 border-amber-500/30',
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

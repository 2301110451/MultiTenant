<?php

namespace App\Support;

use App\Models\Plan;

final class TenantAppearance
{
    /**
     * Tailwind class sets keyed by subscription plan slug.
     *
     * @return array<string, string>
     */
    public static function theme(): array
    {
        $plan = Tenancy::tenantPlan();
        $slug = $plan ? strtolower($plan->slug) : 'basic';

        return match ($slug) {
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
                'button' => 'bg-amber-500 hover:bg-amber-600 shadow-amber-600/30',
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
                'button' => 'bg-sky-600 hover:bg-sky-700 shadow-sky-600/30',
                'panelRing' => 'ring-sky-500/20',
                'badge' => 'bg-sky-400/20 text-sky-300 border-sky-500/30',
            ],
            default => [
                'slug' => 'basic',
                'label' => $plan?->name ?? 'Basic',
                'sidebar' => 'bg-slate-900',
                'sidebarBorder' => 'border-slate-800',
                'brandIcon' => 'bg-indigo-600 shadow-indigo-900/50',
                'brandSub' => 'text-indigo-400',
                'navActive' => 'bg-indigo-600 text-white shadow shadow-indigo-900/40',
                'navIdle' => 'text-slate-400 hover:text-white hover:bg-slate-800',
                'breadcrumbAccent' => 'text-indigo-600',
                'avatar' => 'bg-indigo-600',
                'heroGradient' => 'from-slate-900 via-indigo-950 to-slate-900',
                'heroAccent' => 'text-indigo-400',
                'button' => 'bg-indigo-600 hover:bg-indigo-700 shadow-indigo-600/30',
                'panelRing' => 'ring-indigo-500/20',
                'badge' => 'bg-indigo-400/20 text-indigo-300 border-indigo-500/30',
            ],
        };
    }

    public static function planAllowsReports(): bool
    {
        $plan = Tenancy::tenantPlan();

        return $plan instanceof Plan && $plan->allows('reports');
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

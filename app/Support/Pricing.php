<?php

namespace App\Support;

use App\Models\Plan;

final class Pricing
{
    public static function enforcementEnabled(): bool
    {
        return (bool) config('pricing.enforcement_enabled', true);
    }

    /**
     * @return array<string, bool>
     */
    public static function featureMap(?Plan $plan = null): array
    {
        $plan ??= Tenancy::tenantPlan();
        $slug = strtolower((string) ($plan?->slug ?? 'basic'));

        $defaults = config("pricing.tiers.{$slug}.features", []);
        if (! is_array($defaults)) {
            $defaults = [];
        }

        $stored = self::normalizeStoredFeatures($plan?->features);

        return array_replace($defaults, $stored);
    }

    public static function allows(string $feature, ?Plan $plan = null): bool
    {
        $feature = self::canonicalKey($feature);
        $map = self::featureMap($plan);

        return (bool) ($map[$feature] ?? false);
    }

    public static function monthlyReservationLimit(?Plan $plan = null): ?int
    {
        $plan ??= Tenancy::tenantPlan();
        if ($plan && $plan->monthly_reservation_limit !== null) {
            return (int) $plan->monthly_reservation_limit;
        }

        $slug = strtolower((string) ($plan?->slug ?? 'basic'));
        $limit = config("pricing.tiers.{$slug}.monthly_reservation_limit");

        return $limit === null ? null : (int) $limit;
    }

    private static function canonicalKey(string $feature): string
    {
        return match ($feature) {
            'qr' => 'qr_checkin',
            'payments' => 'integrated_payments',
            default => $feature,
        };
    }

    /**
     * @return array<string, bool>
     */
    private static function normalizeStoredFeatures(mixed $features): array
    {
        if (! is_array($features) || $features === []) {
            return [];
        }

        $normalized = [];
        if (array_is_list($features)) {
            foreach ($features as $feature) {
                if (! is_string($feature) || $feature === '') {
                    continue;
                }

                $normalized[self::canonicalKey($feature)] = true;
            }

            return $normalized;
        }

        foreach ($features as $key => $value) {
            if (! is_string($key) || $key === '') {
                continue;
            }

            $normalized[self::canonicalKey($key)] = (bool) $value;
        }

        return $normalized;
    }
}

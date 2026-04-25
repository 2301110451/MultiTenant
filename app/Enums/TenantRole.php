<?php

namespace App\Enums;

enum TenantRole: string
{
    case TenantAdmin = 'tenant_admin';
    case Staff = 'staff';
    case Resident = 'resident';

    /**
     * Map stored DB strings (including legacy aliases) to a valid role. Never returns null.
     */
    public static function resolveFromStored(?string $value): self
    {
        if ($value === null || $value === '') {
            return self::Resident;
        }

        $v = strtolower(trim($value));

        return match ($v) {
            'secretary', 'tenant_admin' => self::TenantAdmin,
            'barangay_captain', 'captain', 'staff', 'custodian' => self::Staff,
            'viewer' => self::Resident,
            'resident', 'user' => self::Resident,
            default => self::tryFrom($v) ?? self::Resident,
        };
    }
}

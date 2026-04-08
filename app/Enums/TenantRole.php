<?php

namespace App\Enums;

enum TenantRole: string
{
    case Secretary = 'secretary';
    case Captain = 'captain';
    case Custodian = 'custodian';
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
            'barangay_captain', 'captain' => self::Captain,
            'secretary' => self::Secretary,
            'custodian' => self::Custodian,
            'resident', 'user' => self::Resident,
            default => self::tryFrom($v) ?? self::Resident,
        };
    }
}

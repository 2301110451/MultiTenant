<?php

namespace App\Enums;

enum FacilityKind: string
{
    case Facility = 'facility';
    case Equipment = 'equipment';

    public function label(): string
    {
        return match ($this) {
            self::Facility => 'Facility',
            self::Equipment => 'Equipment',
        };
    }

    /** Large card icon (no photo uploads — category only). */
    public function emoji(): string
    {
        return match ($this) {
            self::Facility => '🏛️',
            self::Equipment => '📦',
        };
    }
}

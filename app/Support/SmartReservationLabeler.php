<?php

namespace App\Support;

class SmartReservationLabeler
{
    public static function label(int $hours, bool $hasEquipment, bool $isWeekend): string
    {
        if ($hours >= 8 && $hasEquipment && $isWeekend) {
            return 'priority-full-day';
        }

        if ($hours >= 4 && $hasEquipment) {
            return 'extended-with-equipment';
        }

        if ($isWeekend) {
            return 'weekend-standard';
        }

        return 'standard';
    }
}

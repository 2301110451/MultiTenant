<?php

namespace App\Services;

use App\Models\DamageReport;
use App\Models\Penalty;

class PenaltyService
{
    public function computeFromDamage(DamageReport $damage): Penalty
    {
        $equipment = $damage->equipment;
        $daysOverdue = 0;
        $amount = (float) ($damage->estimated_cost ?? 0);

        if ($amount <= 0 && $equipment) {
            $amount = (float) $equipment->penalty_per_day;
        }

        return Penalty::query()->create([
            'damage_report_id' => $damage->id,
            'reservation_id' => $damage->reservation_id,
            'amount' => max($amount, 0),
            'days_overdue' => $daysOverdue,
            'computed_rule' => 'damage_estimated_cost_or_equipment_penalty_per_day',
            'status' => 'unpaid',
        ]);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DamageReport extends Model
{
    protected $connection = 'tenant';

    protected $table = 'damages';

    protected $fillable = [
        'equipment_id',
        'reservation_id',
        'reported_by',
        'description',
        'severity',
        'estimated_cost',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'estimated_cost' => 'decimal:2',
        ];
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function reservation(): BelongsTo
    {
        return $this->belongsTo(Reservation::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function penalties(): HasMany
    {
        return $this->hasMany(Penalty::class);
    }
}

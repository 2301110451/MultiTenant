<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantApplication extends Model
{
    protected $connection = 'mysql';

    protected $fillable = [
        'barangay_name',
        'plan_id',
        'tenant_admin_email',
        'tenant_admin_password_encrypted',
        'staff_email',
        'staff_password_encrypted',
        'notes',
        'status',
        'reviewed_at',
        'reviewed_by',
        'provisioned_tenant_id',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(CentralUser::class, 'reviewed_by');
    }

    public function provisionedTenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'provisioned_tenant_id');
    }
}

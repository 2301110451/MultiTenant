<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantSetting extends Model
{
    protected $connection = 'tenant';

    protected $fillable = [
        'branding_name',
        'logo_path',
        'accent_color',
        'background_color',
        'sidebar_background_color',
        'compact_layout',
        'module_toggles',
    ];

    protected function casts(): array
    {
        return [
            'compact_layout' => 'boolean',
            'module_toggles' => 'array',
        ];
    }
}

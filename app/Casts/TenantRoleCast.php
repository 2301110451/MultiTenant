<?php

namespace App\Casts;

use App\Enums\TenantRole;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class TenantRoleCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): TenantRole
    {
        return TenantRole::resolveFromStored(is_string($value) ? $value : null);
    }

    /**
     * @return array<string, string>
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): array
    {
        $role = $value instanceof TenantRole
            ? $value
            : TenantRole::resolveFromStored(is_string($value) ? $value : null);

        return ['role' => $role->value];
    }
}

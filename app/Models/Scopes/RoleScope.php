<?php

namespace App\Models\Scopes;

use App\Enums\UserType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Database\Eloquent\Builder;

class RoleScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = auth('api')->user();

        if (!$user) {
            return;
        }

        $prefix = in_array($user->user_type, [UserType::VENDOR, UserType::ADMIN])
            ? 'admin'
            : 'vendor';

        $builder->where('prefix', $prefix);

        if ($prefix === 'vendor') {
            $builder->where('vendor_id', $user->vendor_id);
        }
    }
}

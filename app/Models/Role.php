<?php

namespace App\Models;

use App\Models\Scopes\RoleScope;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;


class Role extends Model implements TranslatableContract
{
    use Translatable;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    public $translatable = ['name'];

    protected static function booted(): void
    {
        static::addGlobalScope(new RoleScope);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }
}

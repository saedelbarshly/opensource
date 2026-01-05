<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Builder;

class Permission extends Model implements TranslatableContract
{
    use Translatable;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    public $translatable = ['name'];

     protected static function booted()
    {
        static::addGlobalScope('prefix', function (Builder $builder) {
            $builder->where('prefix', extractPrefixFromAction(request()->route()?->getActionName() ?? 'Admin'));
        });
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    public function getAdminPermissionsAttribute()
    {
        // $admin =
        return $this->where('back_route_name', 'like', 'admin%')
            ->get();
    }
    public function scopeActive($query){
        return $query->where('is_active', 1);
    }
}

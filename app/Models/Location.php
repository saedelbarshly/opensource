<?php

namespace App\Models;

use App\Models\City;
use App\Models\User;
use App\Models\Country;
use App\Filter\LocationFilter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Location extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function scopeFilter($query, LocationFilter $filter)
    {
        return $filter->apply($query);
    }
}

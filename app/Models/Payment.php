<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected static function booted()
    {
        static::creating(function (Payment $transaction) {
            $transaction->uuid = (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function payable(): MorphTo
    {
        return $this->morphTo('payable', 'payable_type', 'payable_id', 'id');
    }

    // public function orders(): HasMany
    // {
    //     return $this->hasMany(Order::class);
    // }
}

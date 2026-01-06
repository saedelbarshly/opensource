<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuthenticationVerification extends Model
{
    protected $guarded = ['id','created_at','updated_at','deleted_at'];
    protected $casts = [
        'reset_code_expires_at' => 'boolean'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

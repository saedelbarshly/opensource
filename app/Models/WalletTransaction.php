<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
     protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function modelable()
    {
        return $this->morphTo();
    }
}

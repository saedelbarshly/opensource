<?php

namespace Modules\Media\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Media extends Model
{
    protected $guarded = ['id', 'created_at', 'updated_at'];

    protected $keyType = 'string';
    public $incrementing = false;

    protected $casts = [
        'metadata' => 'array',
    ];

    public function modelable(): MorphTo
    {
        return $this->morphTo();
    }
}

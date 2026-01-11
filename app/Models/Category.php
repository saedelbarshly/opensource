<?php

namespace App\Models;

use Modules\Media\Traits\MediaTrait;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class Category extends Model implements TranslatableContract
{
    use Translatable,MediaTrait;
    protected $guarded   = ['id', 'deleted_at','created_at', 'updated_at'];
    protected $casts = [
        'is_active' => 'boolean',
        'is_returnable' => 'boolean',
        'is_taxable' => 'boolean',
    ];
    public $translatedAttributes = ['name'];

     protected array $mediaColumns = [
        'image' => [
            'is_single'  => true,
            'type'       => 'image',
            'option'     => 'image',
            'default'    => null,
        ]
    ];

    // scopes
    public function scopeActive($query){
        return $query->where('is_active', 1);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'specialties');
    }
}

<?php

namespace App\Models;
use Modules\Media\Traits\MediaTrait;
use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class Brand extends Model implements TranslatableContract
{
    use Translatable, MediaTrait;
    protected $guarded   = ['id', 'deleted_at','created_at', 'updated_at'];
    public $translatedAttributes = ['name'];

    protected array $mediaColumns = [
        'image' => [
            'is_single'  => true,
            'type'       => 'image',
            'option'     => 'image',
            'default'    => null,
        ]
    ];

    public function scopeActive($query){
        return $query->where('is_active', 1);
    }

    public function products(){
        return $this->hasMany(Product::class);
    }
}

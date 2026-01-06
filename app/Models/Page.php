<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;


class Page extends Model implements TranslatableContract
{
    use  Translatable;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    public $translatedAttributes = ['name'];

    protected array $mediaColumns = [
        'gallery' => [
            'is_single'  => false,
            'type'       => 'image',
            'option'     => 'gallery',
            'default'    => null,
        ]
    ];
    public function scopeActive($query){
        return $query->where('is_active', 1);
    }

}

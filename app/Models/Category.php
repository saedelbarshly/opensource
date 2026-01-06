<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Translatable;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;

class Category extends Model implements TranslatableContract
{
    use Translatable;
    protected $guarded   = ['id', 'deleted_at','created_at', 'updated_at'];
    protected $casts = [
        'is_active' => 'boolean',
        'is_returnable' => 'boolean',
        'is_taxable' => 'boolean',
    ];
    public $translatedAttributes = ['name'];

    // scopes
    public function scopeActive($query){
        return $query->where('is_active', 1);
    }
}

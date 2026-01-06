<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Astrotomic\Translatable\Contracts\Translatable as TranslatableContract;
use Astrotomic\Translatable\Translatable;


class Faq extends Model implements TranslatableContract
{
    use  Translatable;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];
    public $translatedAttributes = ['question', 'answer'];


    public function scopeActive($query){
        return $query->where('is_active', 1);
    }

}

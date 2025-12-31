<?php

namespace App\Models;

use App\Services\Dashboard\Sluggable;
use Illuminate\Database\Eloquent\Model;


class CityTranslation extends Sluggable
{
    public $timestamps = false;
    protected $guarded = ['id', 'created_at', 'updated_at', 'deleted_at'];

    protected static function boot()
    {
        parent::boot();
        static::saving(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->name ?? $model->title;
            }
        });
    }
    public function sluggable(): array
    {
        return [
            'name' => 'slug',
        ];
    }

}

<?php

namespace Modules\Media\Traits;

use Modules\Media\Models\Media;
use Modules\Media\Observers\UploadMediaObserver;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait MediaTrait
{
    public static function bootMediaTrait()
    {
        static::observe(UploadMediaObserver::class);
    }

    protected function getMediaColumnsAttribute()
    {
        return $this->mediaColumns;
    }

    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'model');
    }

    public function getMediaAttribute($options = null)
    {
        return Media::where('model_id', $this->id)
            ->where('model_type', get_class($this))
            ->get()
            ->map(function ($media) {
                return [
                    'id'        => $media->id,
                    'path'      => asset('storage/' . $media->path . '/' . $media->name),
                    'type'      => $media->type,
                    'option'    => $media->option
                ];
            });
    }

   public function __get($key)
   {
       if (isset($this->mediaColumns[$key])) {
           $isSingle = $this->mediaColumns[$key] === 0;

           $query = $this->media()->where('option', $key)->latest();

           if ($isSingle) {
               $media = $query->first();

               return $media ? [
                   'id'     => $media->id,
                   'path'   => asset('storage/' . trim($media->path, '/') . '/' . $media->name),
                   'type'   => $media->type,
                   'option' => $media->option,
               ] : null;
           }

           // Multiple images (return collection)
           return $query->get()->map(function ($media) {
               return [
                   'id'     => $media->id,
                   'path'   => asset('storage/' . trim($media->path, '/') . '/' . $media->name),
                   'type'   => $media->type,
                   'option' => $media->option,
               ];
           });
       }

       return parent::__get($key);
   }

    /**
     * Optionally, a helper to get all media grouped by option.
     */
   public function getAllMediaByOption(): array
   {
       $media = [];

       foreach ($this->mediaColumns as $option => $isMultiple) {
           $media[$option] = $this->{$option};
       }

       return $media;
   }


}

<?php

namespace Modules\Media\Observers;
use Illuminate\Support\Str;

use Modules\Media\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaObserver
{
    public function creating(Media $model): void
    {
        if (empty($media->id)) {
            $model->id = (string) Str::uuid();
        }
    }

    public function deleting(Media $model): void
    {
        if (Storage::disk($model->disk)->exists( $model->path . '/' . $model->name)) {
            Storage::disk($model->disk)->delete($model->path . '/' . $model->name);
        }
    }
}

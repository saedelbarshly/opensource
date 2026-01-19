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

    // public function deleting(Media $model): void
    // {
    //     if (Storage::disk($model->disk)->exists( $model->path . '/' . $model->name)) {
    //         Storage::disk($model->disk)->delete($model->path . '/' . $model->name);
    //     }
    // }

    public function deleting(Media $model): void
    {
        $disk = Storage::disk($model->disk ?? 'public');
        $basePath = $model->path;

        // Delete main file
        if ($model->name && $disk->exists($basePath . '/' . $model->name)) {
            $disk->delete($basePath . '/' . $model->name);
        }

        // Delete thumbnail
        if ($model->has_thumbnail && $model->thumbnail_name) {
            $thumbnailPath = $basePath . '/thumbnails/' . $model->thumbnail_name;
            if ($disk->exists($thumbnailPath)) {
                $disk->delete($thumbnailPath);
            }
        }

        // Delete HLS files
        $hlsFields = ['hls_name', 'hls_240p_name', 'hls_360p_name', 'hls_480p_name', 'hls_720p_name', 'hls_1080p_name'];
        foreach ($hlsFields as $field) {
            if ($model->$field) {
                $hlsPath = $basePath . '/hls/' . $model->$field;
                if ($disk->exists($hlsPath)) {
                    $disk->delete($hlsPath);
                }
            }
        }
    }
}

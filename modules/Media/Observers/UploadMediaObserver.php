<?php

namespace Modules\Media\Observers;


use Modules\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;

class UploadMediaObserver
{
    // public function saved(Model $model): void
    // {
    //     $mediaInput = request()->input('media', []);
    //     if (empty($mediaInput)) {
    //         return;
    //     }

    //     $flattened = [];
    //     foreach ($mediaInput as $key => $value) {
    //         if (is_array($value)) {
    //             foreach ($value as $id) {
    //                 $flattened[] = ['option' => $key, 'id' => $id];
    //             }
    //         } else {
    //             $flattened[] = ['option' => $key, 'id' => $value];
    //         }
    //     }

    //     foreach ($flattened as $item) {
    //         $option = $item['option'];
    //         $id = $item['id'];
    //         if (!array_key_exists($option, $model->mediaColumns)) {
    //             continue;
    //         }

    //         $isSingle = $model->mediaColumns[$option] === 0;

    //         if ($isSingle) {
    //             $existing = Media::where('model_type', get_class($model))
    //                 ->where('model_id', $model->id)
    //                 ->where('option', $option)
    //                 ->first();

    //             if ($existing) {
    //                 if ($existing->id != $id) {
    //                     $existing->delete();
    //                 }
    //             }
    //         }

    //         $media = Media::find($id);
    //         if ($media) {
    //             $media->update([
    //                 'model_id'    => $model->id,
    //                 'is_attached' => true,
    //                 'option'      => $option,
    //                 'uploaded_by' => auth('api')->id(),
    //             ]);
    //         }
    //     }
    // }


    public function saved(Model $model): void
    {
        $mediaColumns = $model->mediaColumns;


        if (empty($mediaColumns)) {
            return;
        }

        $mediaInput = request()->only(array_keys($mediaColumns));
        if (empty($mediaInput)) {
            return;
        }

        foreach ($mediaInput as $option => $value) {
            $isSingle = $mediaColumns[$option]['is_single'];
            $option = $mediaColumns[$option]['option'];

            if ($isSingle) {
                Media::where('model_type', get_class($model))
                    ->where('model_id', $model->id)
                    ->where('option', $option)
                    ->delete();
            }

            $ids = is_array($value) ? $value : [$value];

            foreach ($ids as $id) {
                $media = Media::find($id);
                if ($media) {
                    $media->update([
                        'model_id'    => $model->id,
                        'is_attached' => true,
                        'option'      => $option,
                        'uploaded_by' => auth('api')->id(),
                    ]);
                }
            }
        }
    }


    public function deleting(Model $model): void
    {
        if (method_exists($model, 'media') && $model->media()->exists()) {
            $model->media()->each(function ($media) {
                $media->delete();
            });
        }
    }
}

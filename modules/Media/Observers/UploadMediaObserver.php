<?php
namespace Modules\Media\Observers;

use Modules\Media\Models\Media;
use Illuminate\Database\Eloquent\Model;

class UploadMediaObserver
{
    public function saved(Model $model): void
    {
        $mediaColumns = $model->mediaColumns;


        if (empty($mediaColumns)) {
            return;
        }

        $mediaInput = request()->only(array_keys($mediaColumns));


        // Filter out null/empty values to prevent deleting existing media
        $mediaInput = array_filter($mediaInput, fn($value) => !is_null($value) && $value !== '');

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

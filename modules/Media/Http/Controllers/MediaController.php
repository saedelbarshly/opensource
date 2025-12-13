<?php

namespace Modules\Media\Http\Controllers;

use Exception;
use Illuminate\Support\Str;
use Modules\Media\Models\Media;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Modules\Media\Services\MediaService;
use App\Http\Requests\Api\General\Media\StoreRequest;

class MediaController extends Controller
{
    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        try {
            if (!$request->hasFile('file')) {
                return json(null, trans('No file provided'), 'fail', 400);
            }
            $file = $request->file('file');
            $model = $request->input('model', 'general');
            $attachmentType = $request->input('media_type', 'file');

            $fileName = match ($attachmentType) {
                'image' => MediaService::uploadImg(
                    $file,
                    'images',
                    'image',
                    $request->input('width'),
                    $request->input('height'),
                    $request->input('quality', 85)
                ),
                'video' => MediaService::uploadVideo(
                    $file,
                    'videos',
                    'video',
                    $request->boolean('generate_thumbnail', true)
                ),
                'pdf' => MediaService::uploadPdf(
                    $file,
                    'files',
                    'pdf',
                    $request->boolean('generate_thumbnail', true)
                ),
                'audio' => MediaService::uploadAudio($file),
                default => MediaService::uploadFile($file)
            };


            $directoryMap = [
                'image' => 'images',
                'video' => 'videos',
                'audio' => 'audio',
                'file' => 'files'
            ];

            $directory = $directoryMap[$attachmentType] ?? 'files';
            $actualFileName = $fileName;

            // Handle different return types
            if (is_array($fileName) && isset($fileName['filename'])) {
                $actualFileName = $fileName['filename'];
                $thumbnailName = $fileName['thumbnail'] ?? null;
                $mediaInfo = MediaService::getMediaInfo($actualFileName, $directory);
                $mediaInfo['thumbnail_generated'] = !empty($thumbnailName);

                if ($thumbnailName) {
                    $mediaInfo['thumbnail_url'] = MediaService::getFileUrl($thumbnailName, 'thumbnails/' . $directory);
                }
            } else {
                $actualFileName = is_array($fileName) ? $fileName[0]['filename'] ?? $fileName : $fileName;
                $mediaInfo = MediaService::getMediaInfo($actualFileName, $directory);
            }

            if ($request->input('deleted_media')) {
                $media = Media::findOrFail($request->input('deleted_media'));
                $media->delete();
            }

            $media = Media::create([
                'model_type'        => 'App\\Models\\' . Str::studly(Str::singular($model)),
                'name'              => $actualFileName,
                'type'              => $mediaInfo['type'],
                'path'              => $directory,
                'size'              => $mediaInfo['size'],
                'extension'         => $mediaInfo['extension'],
                'width'             => $mediaInfo['metadata']['width'] ?? null,
                'height'            => $mediaInfo['metadata']['height'] ?? null,
                'duration'          => $mediaInfo['metadata']['duration'] ?? null,
                'quality'           => $request->input('quality', null),
                'metadata'          => $mediaInfo['metadata'] ?? null,
                'thumbnail_name'    => $thumbnailName ?? null,
                'has_thumbnail'     => isset($thumbnailName) ? !empty($thumbnailName) : false,
            ]);

            DB::commit();
            return json([
                'id'                  => $media->id,
                'name'                => $actualFileName,
                'url'                 => $mediaInfo['url'],
                'type'                => $mediaInfo['type'],
                'size'                => $mediaInfo['size'],
                'thumbnail_url'       => $mediaInfo['thumbnail'] ?? $mediaInfo['thumbnail_url'] ?? null,
                'thumbnail_generated' => $mediaInfo['thumbnail_generated'] ?? false,
                'metadata'            => $mediaInfo['metadata'] ?? null
            ], trans('File uploaded successfully'), 'success', 200);
        } catch (Exception $e) {
            DB::rollBack();
            return json(null, trans('Something went wrong, please try again'), 'fail', 422);
        }
    }

    public function destroy($id)
    {
        $media = Media::findOrFail($id);
        $media->delete();
        return json(null, trans('Deleted successfully'), 'success', 200);
    }
}

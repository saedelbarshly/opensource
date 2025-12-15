<?php

namespace Modules\Media\Http\Controllers;

use Exception;
use App\Services\ModelService;
use Modules\Media\Models\Media;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Modules\Media\Services\MediaService;
use Modules\Media\Http\Requests\StoreRequest;

class MediaController extends Controller
{

    public function store(StoreRequest $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            if (!$request->hasFile('file')) {
                return json(null, trans('No file provided'), 'fail', 400);
            }

            $file = $request->file('file');
            $model = $request->input('model', 'general');
            $attachmentType = $request->input('media_type', 'file');

            $fileResult = match ($attachmentType) {
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
                    $request->boolean('generate_thumbnail', false),
                    $request->boolean('generate_slh', false)
                ),
                'pdf' => MediaService::uploadPdf(
                    $file,
                    'files',
                    'pdf',
                    $request->boolean('generate_thumbnail', false)
                ),
                'audio' => MediaService::uploadAudio($file),
                default => MediaService::uploadFile($file),
            };

            $directoryMap = [
                'image' => 'images',
                'video' => 'videos',
                'audio' => 'audio',
                'pdf'   => 'files',
                'file'  => 'files',
            ];

            $directory = $directoryMap[$attachmentType] ?? 'files';
            $thumbnailName = null;
            $hlsName = null;
            $hls240pName = null;
            $hls360pName = null;
            $hls480pName = null;
            $hls720pName = null;
            $hls1080pName = null;



            if (is_array($fileResult)) {
                $actualFileName = $fileResult['filename'] ?? null;
                $thumbnailName = $fileResult['thumbnail_name'] ?? null;
                $hlsName = $fileResult['hls_name'] ?? null;
                $hls240pName = $fileResult['hls_240p_name'] ?? null;
                $hls360pName = $fileResult['hls_360p_name'] ?? null;
                $hls480pName = $fileResult['hls_480p_name'] ?? null;
                $hls720pName = $fileResult['hls_720p_name'] ?? null;
                $hls1080pName = $fileResult['hls_1080p_name'] ?? null;
            } else {
                $actualFileName = $fileResult;
            }

            if (!$actualFileName) {
                throw new Exception('File name could not be determined');
            }

            $mediaInfo = MediaService::getMediaInfo($actualFileName, $directory);


            if ($request->input('deleted_media')) {
                $oldMedia = Media::findOrFail($request->input('deleted_media'));
                $oldMedia->delete();
            }


            $media = Media::create([
                'model_type'       => 'App\\Models\\' . $model,
                'name'             => $actualFileName,
                'type'             => $mediaInfo['type'],
                'path'             => $directory,
                'size'             => $mediaInfo['size'],
                'extension'        => $mediaInfo['extension'],
                'width'            => $mediaInfo['metadata']['width'] ?? null,
                'height'           => $mediaInfo['metadata']['height'] ?? null,
                'duration'         => $mediaInfo['metadata']['duration'] ?? null,
                'quality'          => $request->input('quality', null),
                'metadata'         => $mediaInfo['metadata'] ?? null,
                'thumbnail_name'   => $thumbnailName ? basename($thumbnailName) : null,
                'has_thumbnail'    => !empty($thumbnailName),
                'has_hls'          => !empty($hlsName),
                'hls_name'         => !empty($hlsName) ? basename($hlsName) : null,
                'hls_240p_name'    => !empty($hls240pName) ? basename($hls240pName) : null,
                'hls_360p_name'    => !empty($hls360pName) ? basename($hls360pName) : null,
                'hls_480p_name'    => !empty($hls480pName) ? basename($hls480pName) : null,
                'hls_720p_name'    => !empty($hls720pName) ? basename($hls720pName) : null,
                'hls_1080p_name'   => !empty($hls1080pName) ? basename($hls1080pName) : null,

            ]);

            DB::commit();

            return json([
                'id'              => $media->id,
                'name'            => $actualFileName,
                'type'            => $mediaInfo['type'],
                'thumbnail_name'  => $thumbnailName ? asset($thumbnailName) : null,
                'hls_name'        => $hlsName ? asset($hlsName) : null,
                'hls_240p_name'   => $hls240pName ? asset($hls240pName) : null,
                'hls_360p_name'   => $hls360pName ? asset($hls360pName) : null,
                'hls_480p_name'   => $hls480pName ? asset($hls480pName) : null,
                'hls_720p_name'   => $hls720pName ? asset($hls720pName) : null,
                'hls_1080p_name'  => $hls1080pName ? asset($hls1080pName) : null,
            ], __('File uploaded successfully'));
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Upload failed: ' . $e->getMessage());
            return json(null, __('Something went wrong, please try again'), 'fail', 422);
        }
    }

    public function getModels(ModelService $modelDiscovery)
    {
        return json($modelDiscovery->all());
    }
    public function destroy($id)
    {
        $media = Media::findOrFail($id);
        $media->delete();
        return json(null, trans('Deleted successfully'), 'success', 200);
    }
}

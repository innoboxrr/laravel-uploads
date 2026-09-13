<?php

namespace Innoboxrr\LaravelUploads\Http\Requests\Upload;

use Innoboxrr\LaravelUploads\Models\Upload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

class DisplayRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        //
    }

    public function authorize()
    {

        return true;

    }

    public function rules()
    {
        return [
            //
        ];
    }

    public function messages()
    {
        return [
            //
        ];
    }

    public function attributes()
    {
        return [
            //
        ];
    }

    protected function passedValidation()
    {
        //
    }

    public function handle($upload_uuid, $filename = null)
    {
        // Se cachean solo escalares. Laravel 13 publica la config de cache con
        // serializable_classes => false: un modelo cacheado vuelve como
        // __PHP_Incomplete_Class y la segunda visita respondia 500.
        $location = Cache::remember(Upload::displayCacheKey($upload_uuid), 60, function () use ($upload_uuid) {
            $upload = Upload::where('uuid', $upload_uuid)->firstOrFail();

            return ['disk' => $upload->disk, 'path' => $upload->path];
        });

        $disk = Storage::disk($location['disk']);

        if (! $disk->exists($location['path'])) {
            abort(404);
        }

        $mimeType = $disk->mimeType($location['path']);
        $size = $disk->size($location['path']);

        return response()->stream(function () use ($disk, $location) {
            $stream = $disk->readStream($location['path']);
            fpassthru($stream);
            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $mimeType,
            'Content-Length' => $size,
            'Cache-Control' => 'public, max-age=2628000',
            'Content-Disposition' => 'inline; filename="' . basename($location['path']) . '"'
        ]);
    }
}

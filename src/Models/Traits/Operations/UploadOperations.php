<?php

namespace Innoboxrr\LaravelUploads\Models\Traits\Operations;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

trait UploadOperations
{

    public function buildCreatable($path, $file, $visibility = 'public', $userId =  null, $disk = 'local', $uploadableType = null, $uploadableId = null)
    {

        if (!($file instanceof UploadedFile)) {
            throw new \InvalidArgumentException("El parámetro file debe ser una instancia de UploadedFile.");
        }

        return [
            'uuid' => (string) Str::uuid(),
            'filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'path' => $path,
            'disk' => $disk,
            'visibility' => $visibility,
            'uploadable_type' => $uploadableType,
            'uploadable_id' => $uploadableId,
            'user_id' => $userId,
        ];
    }

    public static function getUploadPath(string $uri)
    {
        try {
            $fakeRequest = Request::create($uri, 'GET');
            $route = Route::getRoutes()->match($fakeRequest);
            $parameters = $route->parameters();
            if (!isset($parameters['upload_uuid'])) {
                return null;
            }
            $upload = self::where('uuid', $parameters['upload_uuid'])->first();
            return $upload ? $upload->path : null;
        } catch (\Exception $e) {
            return null;
        }
    }

}
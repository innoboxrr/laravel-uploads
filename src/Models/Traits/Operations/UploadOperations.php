<?php

namespace Innoboxrr\LaravelUploads\Models\Traits\Operations;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

trait UploadOperations
{

    public function buildCreatable($path, $file, $visibility = 'public', $userId =  null, $disk = 'local')
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
            'user_id' => $userId,
        ];
    }

}
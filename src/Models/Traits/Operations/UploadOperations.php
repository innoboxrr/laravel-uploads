<?php

namespace Innoboxrr\LaravelUploads\Models\Traits\Operations;

use Illuminate\Http\UploadedFile;

trait UploadOperations
{

    public function buildCreatable($path, $file, $status = 'public', $userId =  null, $cloud = 'aws')
    {

        if (!($file instanceof UploadedFile)) {
            throw new \InvalidArgumentException("El parámetro file debe ser una instancia de UploadedFile.");
        }

        $userId = $userId ?? user()->id;

        return [
            'cloud' => $cloud,
            'uri' => $path,
            'mime' => $file->getClientMimeType(),
            'extension' => $file->getClientOriginalExtension(),
            'size' => $file->getSize(),
            'status' => $status,
            'user_id' => $userId
        ];
    }

}
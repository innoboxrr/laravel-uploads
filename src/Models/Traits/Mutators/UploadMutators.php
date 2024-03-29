<?php

namespace Innoboxrr\LaravelUploads\Models\Traits\Mutators;

trait UploadMutators
{

	public function getUrlAttribute()
    {

        return route('lu.upload.display', [
            'upload_uuid' => $this->uuid,
            'filename' => $this->filename,
        ]);

    }

    public function getUriAttribute()
    {
        $uri = route('lu.upload.display', [
            'upload_uuid' => $this->uuid,
            'filename' => $this->filename,
        ], false);

        return $uri;
    }


}
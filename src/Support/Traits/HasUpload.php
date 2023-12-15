<?php

namespace Innoboxrr\LaravelUploads\Support\Traits;

trait HasUpload
{
    
    public function upload()
    {
        return $this->morphOne(\Innoboxrr\LaravelUploads\Models\Upload::class, 'uploadable');
    }

}


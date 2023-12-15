<?php

namespace Innoboxrr\LaravelUploads\Support\Traits;

trait HasUploads
{
    
    public function uploads()
    {
        return $this->morphMany(\Innoboxrr\LaravelUploads\Models\Upload::class, 'uploadable');
    }

}


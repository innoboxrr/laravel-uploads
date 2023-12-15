<?php

namespace Innoboxrr\LaravelUploads\Models\Traits\Mutators;

trait UploadMutators
{

	public function getUrlAttribute()
    {

        return $this->getUrl();

    }

}
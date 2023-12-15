<?php

namespace Innoboxrr\LaravelUploads\Models\Traits\Relations;

// use \Znck\Eloquent\Traits\BelongsToThrough; // Docs: https://github.com/staudenmeir/belongs-to-through
// use \Staudenmeir\EloquentHasManyDeep\HasRelationships; // Docs: https://github.com/staudenmeir/eloquent-has-many-deep

trait UploadRelations
{
	
    public function user()
    {
        return $this->belongsTo(config('laravel-uploads.user_class'));
    }

    public function uploadable()
    {
        return $this->morphTo();
    }

}
<?php

namespace Innoboxrr\LaravelUploads\Models\Traits\Storage;

// use Innoboxrr\LaravelUploads\Models\UploadMeta;

trait UploadStorage
{

    public function upload($request) 
    {

        $request->file('file')->storeAs($this->getUploadPath(), $this->filename);

    }

    public function updateModel($request)
    {
     
        $this->update($request->only($this->updatable));

        return $this;

    }

    /*
    public function updateModelMetas($request)
    {

        $this->update_metas($request, UploadMeta::class, 'upload_id')->updatePayload();

        return $this;

    }
    */

    public function deleteModel()
    {

        $this->delete();

    }

    public function restoreModel()
    {

        $this->restore();

    }

    public function forceDeleteModel()
    {

        abort(403);

        $this->forceDelete();
        
    }

}
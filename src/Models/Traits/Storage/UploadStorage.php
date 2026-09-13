<?php

namespace Innoboxrr\LaravelUploads\Models\Traits\Storage;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

trait UploadStorage
{

    public function upload($request)
    {

        $upload = $this->create($request->only($this->creatable));

        return $upload;

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

        $this->forgetDisplayCache();

    }

    public function restoreModel()
    {

        $this->restore();

        $this->forgetDisplayCache();

    }

    /*
     * Quien puede hacerlo lo decide UploadPolicy::forceDelete. Aqui solo se
     * cumple lo que promete el README: el registro y el archivo guardado.
     */
    public function forceDeleteModel()
    {

        if ($this->disk && $this->path) {
            Storage::disk($this->disk)->delete($this->path);
        }

        $this->forceDelete();

        $this->forgetDisplayCache();

    }

    public static function displayCacheKey(string $uuid): string
    {

        return "laravel-uploads.display.{$uuid}";

    }

    protected function forgetDisplayCache(): void
    {

        if ($this->uuid) {
            Cache::forget(static::displayCacheKey($this->uuid));
        }

    }

}

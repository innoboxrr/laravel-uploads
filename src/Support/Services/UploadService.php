<?php

namespace Innoboxrr\LaravelUploads\Support\Services;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class UploadService
{

	protected $disk;

	protected $file;

	protected $params;

	protected $validations;

	protected $visibility;

	protected $dir;


	public function __construct($file = null, $params = [], $validations = [])
	{
		$this->disk = config('laravel-uploads.disk', 'local');

		$this->file = $file;

		$this->dir = (config('app.env') == 'production') ? config('laravel-uploads.production_dir', 'files') : config('laravel-uploads.development_dir', 'test');

		$this->visibility = Arr::get($params, 'visibility', 'public');

		// Asegúrate de que $validations es un arreglo
    	$this->validations = is_array($validations) ? $validations : [];
	}

	protected function validate()
    {
        // Validador de Laravel
        $validator = Validator::make(
            ['file' => $this->file],
            ['file' => $this->validations]
        );

        // Verificar si la validación falla
        if ($validator->fails()) {
            // Puedes lanzar una excepción o manejar el error como prefieras
            throw new ValidationException($validator);
        }
    }

	public function compressImage()
    {
        if (! config('laravel-uploads.compress_images', true)) {
            return;
        }

        if (!in_array($this->getMimeType(), ['image/jpeg', 'image/png', 'image/gif'])) {
            return;
        }

        $manager = $this->imageManager();

        if (! $manager) {
            return;
        }

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $filePath = storage_path('app/temp/' . $this->file->hashName());

        $manager->read($this->file)
            ->scaleDown(width: config('laravel-uploads.compress_images_max_width'))
            ->encodeByExtension($this->getExtension(), progressive: true, quality: config('laravel-uploads.compress_images_quality'))
            ->save($filePath);

        $this->file = new File($filePath);

        return $filePath;
    }

    /*
     * Sin el facade Image: en Laravel 13 la clave `image` del contenedor es del
     * componente de imagen del framework, que usa la API de Intervention 4 y
     * pisa la de intervention/image-laravel. Con Intervention 3 cada subida de
     * imagen respondia 500. Sin GD ni Imagick se sube el original.
     */
    protected function imageManager(): ?ImageManager
    {
        if (extension_loaded('gd')) {
            return new ImageManager(new GdDriver());
        }

        if (extension_loaded('imagick')) {
            return new ImageManager(new ImagickDriver());
        }

        return null;
    }

    public function upload()
    {
        try {
            if (!$this->file || !$this->file->isValid()) {
                throw new \Exception("Archivo no válido o no encontrado.");
            }

            $this->validate();

            $tempFilepath = $this->compressImage();

            // Sube el archivo comprimido o el original según el resultado de la compresión
            $path = Storage::disk($this->disk)->putFile($this->dir, $this->file);
            $this->setVisibility($path, $this->visibility);

            // Elimina el archivo temporal si existe
            if ($tempFilepath) {
                unlink($tempFilepath);
            }

            if (!$path) {
                throw new \Exception("No se pudo subir el archivo.");
            }

            return $path;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getFile()
    {
    	return $this->file;
    }

    public function getMimeType()
    {
        if($this->file) {
            return $this->file->getClientMimeType();
        }
        return null;
    }

    public function getSize()
    {
        if($this->file) {
            return $this->file->getSize();
        }
        return null;
    }

    public function getExtension()
    {
        if($this->file) {
            return $this->file->getClientOriginalExtension();
        }
        return null;
    }

    public function getUrl($path)
    {
        return Storage::disk($this->disk)->url($path);
    }

    public function getTemporaryUrl($path, $duration = 60)
    {
        // Asegúrate de que estés usando un disco que soporte URLs temporales
        if (!method_exists(Storage::disk($this->disk), 'temporaryUrl')) {
            throw new \Exception("El disco {$this->disk} no soporta URLs temporales.");
        }

        // Calcula la fecha de expiración
        $expiration = Carbon::now()->addMinutes($duration);

        // Genera la URL temporal
        return Storage::disk($this->disk)->temporaryUrl($path, $expiration);
    }

    public function setVisibility($path, $visibility)
	{
	    Storage::disk($this->disk)->setVisibility($path, $visibility);
	}

	public function delete($path)
	{
	    Storage::disk($this->disk)->delete($path);
	}

	/*
	 * getMetadata() era de Flysystem 1 y no existe en Flysystem 3, el que
	 * trae Laravel desde la 9. Se arma lo mismo con las llamadas actuales.
	 */
	public function getFileInfo($path)
	{
		$disk = Storage::disk($this->disk);

		return [
			'path' => $path,
			'size' => $disk->size($path),
			'mime_type' => $disk->mimeType($path),
			'last_modified' => $disk->lastModified($path),
			'visibility' => $disk->getVisibility($path),
		];
	}

}

<?php

namespace Innoboxrr\LaravelUploads\Support\Services;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Intervention\Image\Laravel\Facades\Image;

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

		$this->dir = (config('app.env') == 'production') ? 'files' : 'test';

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
        if (!in_array($this->getMimeType(), ['image/jpeg', 'image/png', 'image/gif'])) {
            return;
        }

        if (!is_dir(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'));
        }

        $filePath = storage_path('app/temp/' . $this->file->hashName());

        Image::read($this->file)
            ->scaleDown(width: config('laravel-uploads.compress_images_max_width'))
            ->encodeByExtension($this->getExtension(), progressive: true, quality: config('laravel-uploads.compress_images_quality'))
            ->save($filePath);

        $this->file = new File($filePath);

        return $filePath;
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

	public function getFileInfo($path)
	{
		return Storage::disk($this->disk)->getMetadata($path);
	}

}
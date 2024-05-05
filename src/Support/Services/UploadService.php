<?php

namespace Innoboxrr\LaravelUploads\Support\Services;

use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Intervention\Image\ImageManagerStatic as Image;

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

	public function compressImage($imagePath)
    {
        // Comprueba si el archivo es una imagen basado en el tipo MIME
        $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $mimeType = $this->getMimeType();
        if (!in_array($mimeType, $allowedMimeTypes)) {
            // No es una imagen, no se realiza la compresión
            return $imagePath;
        }

        // Obtén la extensión del archivo
        $extension = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

        // Comprime y optimiza la imagen
        $compressedImagePath = $imagePath . '_compressed.' . $extension;
        $image = Image::make($imagePath);
        $image->resize(config('laravel-uploads.compress_images_max_width'), null, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $image->encode($extension, config('laravel-uploads.compress_images_quality'));
        Storage::disk($this->disk)->put($compressedImagePath, $image->__toString());

        return $compressedImagePath;
    }

    public function upload()
    {
        try {
            if (!$this->file || !$this->file->isValid()) {
                throw new \Exception("Archivo no válido o no encontrado.");
            }

            $this->validate();

            // Obtiene la ruta del archivo temporal
            $tempFilePath = $this->file->getPathname();

            // Comprime y optimiza la imagen si es una imagen
            $compressedFilePath = $this->compressImage($tempFilePath);

            // Sube el archivo comprimido o el original según el resultado de la compresión
            $path = Storage::disk($this->disk)->putFile($this->dir, new File($compressedFilePath ?? $tempFilePath));
            $this->setVisibility($path, $this->visibility);

            // Borra el archivo comprimido temporal si se creó
            if (isset($compressedFilePath)) {
                Storage::disk($this->disk)->delete($compressedFilePath);
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
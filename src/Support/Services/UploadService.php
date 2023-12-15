<?php

namespace Innoboxrr\LaravelUploads\Support\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class UploadService 
{

	protected $disk;

	protected $file;

	protected $params;

	protected $validations;

	protected $visibility;

	protected $dir;


	public function __construct($file, $params = [], $validations = [])
	{
		$this->disk = Arr::get($params, 'disk', 's3');

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

	public function upload()
    {
        try {

            $this->validate();

            $path = Storage::disk($this->disk)->putFile($this->dir, $this->file, ['visibility' => $this->visibility]);

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
        return $this->file->getClientMimeType();
    }

    public function getSize()
    {
        return $this->file->getSize();
    }

    public function getExtension()
    {
        return $this->file->getClientOriginalExtension();
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
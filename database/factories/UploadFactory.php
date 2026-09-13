<?php

namespace Innoboxrr\LaravelUploads\Database\Factories;

/*
 * Docs: https://fakerphp.github.io/
 */

use Illuminate\Support\Str;
use Innoboxrr\LaravelUploads\Models\Upload;
use Illuminate\Database\Eloquent\Factories\Factory;

/*
 * user_id no tiene valor por defecto: la tabla lo exige y apunta a la tabla
 * users del host, que el paquete no puede fabricar.
 */
class UploadFactory extends Factory
{

    protected $model = Upload::class;

    public function definition()
    {
        return [
            'uuid' => (string) Str::uuid(),
            'filename' => 'archivo.txt',
            'mime_type' => 'text/plain',
            'extension' => 'txt',
            'size' => 0,
            'path' => 'test/' . Str::random(40) . '.txt',
            'disk' => config('laravel-uploads.disk', 's3'),
            'visibility' => 'public',
        ];
    }

}

<?php

namespace Innoboxrr\LaravelUploads\Http\Requests\Upload;

use Innoboxrr\LaravelUploads\Models\Upload;
use Innoboxrr\LaravelUploads\Http\Resources\Models\UploadResource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Innoboxrr\LaravelUploads\Support\Services\UploadService;

class UploadRequest extends FormRequest
{

    protected function prepareForValidation()
    {

    }

    public function authorize()
    {

        return $this->user()->can('upload', Upload::class);

    }

    public function rules()
    {
        $mimes = config('laravel-uploads.allowed_mimes', []);

        $rules = [
            'required',
            'file',
            'max:' . (int) config('laravel-uploads.max_size', 10240),
        ];

        // Una lista vacia no restringe el tipo: es decision explicita de quien
        // publica la config, no un descuido que deba romper la subida.
        if (! empty($mimes)) {
            $rules[] = 'mimes:' . (is_array($mimes) ? implode(',', $mimes) : $mimes);
        }

        return [
            'file' => $rules,
            'visibility' => ['nullable', Rule::in(['public', 'private'])],
            'uploadable_type' => ['nullable', 'string', 'max:255'],
            'uploadable_id' => ['nullable', 'max:255'],
        ];
    }

    public function messages()
    {
        return [
            //
        ];
    }

    public function attributes()
    {
        return [
            //
        ];
    }

    protected function passedValidation()
    {

        $file = $this->file('file');

        $filePath = (new UploadService($file, ['visibility' => $this->visibility ?? 'public']))->upload();

        $upload = (new Upload);

        $this->merge(
            $upload->buildCreatable(
                $filePath,
                $file,
                $this->visibility ?? 'public',
                $this->user()->getAuthIdentifier(),
                config('laravel-uploads.disk', 's3'),
                $this->uploadable_type ?? null,
                $this->uploadable_id ?? null,
            )
        );

    }

    public function handle()
    {

        $upload = (new Upload)->upload($this);

        $response = new UploadResource($upload);

        return $response;

    }

}

<?php

namespace Innoboxrr\LaravelUploads\Http\Requests\Upload;

use Innoboxrr\LaravelUploads\Models\Upload;
use Innoboxrr\LaravelUploads\Http\Resources\Models\UploadResource;
use Illuminate\Foundation\Http\FormRequest;
use Innoboxrr\LaravelUploads\Support\Services\UploadService;

class UploadRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        //
    }

    public function authorize()
    {

        return $this->user()->can('upload', Upload::class);

    }

    public function rules()
    {
        return [
            //
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
        
        $filePath = (new UploadService($file))->upload();

        $upload = (new Upload);

        $this->merge($upload->buildCreatable($filePath, $file, $this->status));

    }

    public function handle()
    {

        $upload = (new Upload)->upload($this);

        $response = new UploadResource($upload);

        return $response;

    }
    
}

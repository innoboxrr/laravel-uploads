<?php

namespace Innoboxrr\LaravelUploads\Http\Requests\Upload;

use Innoboxrr\LaravelUploads\Models\Upload;
use Innoboxrr\LaravelUploads\Http\Resources\Models\UploadResource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RestoreRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        //
    }

    public function authorize()
    {
        
        $upload = Upload::withTrashed()->findOrFail($this->upload_id);
        
        return $this->user()->can('restore', $upload);

    }

    public function rules()
    {
        return [
            'upload_id' => 'required|numeric'
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
        //
    }

    public function handle()
    {

        $upload = Upload::withTrashed()->findOrFail($this->upload_id);

        $upload->restoreModel();

        $response = new UploadResource($upload);

        return $response;

    }
    
}

<?php

namespace Innoboxrr\LaravelUploads\Http\Requests\Upload;

use Innoboxrr\LaravelUploads\Models\Upload;
use Innoboxrr\LaravelUploads\Http\Resources\Models\UploadResource;
use Illuminate\Foundation\Http\FormRequest;

class ForceDeleteRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        //
    }

    public function authorize()
    {

        $upload = Upload::withTrashed()->findOrFail($this->upload_id);
        
        return $this->user()->can('forceDelete', $upload);

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

        $upload->forceDeleteModel();

        $response = new UploadResource($upload);

        return $response;

    }
    
}

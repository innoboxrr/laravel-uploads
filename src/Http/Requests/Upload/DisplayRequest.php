<?php

namespace Innoboxrr\LaravelUploads\Http\Requests\Upload;

use Innoboxrr\LaravelUploads\Models\Upload;
use Innoboxrr\LaravelUploads\Http\Resources\Models\UploadResource;
use Innoboxrr\LaravelUploads\Http\Events\Upload\Events\UpdateEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DisplayRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        //
    }

    public function authorize()
    {
        
        $upload = Upload::findOrFail($this->upload_id);

        return $this->user()->can('display', $upload);

    }

    public function rules()
    {
        return [
            //
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

        $upload = Upload::findOrFail($this->upload_id);

        $path = $upload->path;
        if (!Storage::disk($upload->disk)->exists($path)) {
            abort(404);
        }

        return Storage::disk($upload->disk)->download($path, $upload->filename);

    }

}

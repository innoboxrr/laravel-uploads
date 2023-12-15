<?php

namespace Innoboxrr\LaravelUploads\Http\Requests\Upload;

use Innoboxrr\LaravelUploads\Models\Upload;
use Innoboxrr\LaravelUploads\Http\Resources\Models\UploadResource;
use Innoboxrr\LaravelUploads\Http\Events\Upload\Events\UpdateEvent;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class DisplayRequest extends FormRequest
{

    protected function prepareForValidation()
    {
        //
    }

    public function authorize()
    {
        
        return true;

    }

    public function rules()
    {
        return [
            //
            // 'upload_id' => 'required|numeric'
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

    public function handle($upload_id, $filename)
    {

        $upload = Upload::where('uuid', $upload_id)->firstOrFail();

        $path = $upload->path;

        if (!Storage::disk($upload->disk)->exists($path)) {
            abort(404);
        }

        $fileContents = Storage::disk($upload->disk)->get($path);
        $mimeType = Storage::disk($upload->disk)->mimeType($path);

        return response($fileContents, 200)
            ->header('Content-Type', $mimeType);

    }

}

<?php

namespace Innoboxrr\LaravelUploads\Http\Requests\Upload;

use Innoboxrr\LaravelUploads\Models\Upload;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        if (!Storage::disk($upload->disk)->exists($upload->path)) {
            abort(404);
        }

        $mimeType = Storage::disk($upload->disk)->mimeType($upload->path);
        $stream = Storage::disk($upload->disk)->readStream($upload->path);

        return response()->stream(function () use ($stream) {
            fpassthru($stream);
        }, 200, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'max-age=26280000',
        ]);
    }

}

<?php

namespace Innoboxrr\LaravelUploads\Http\Controllers;

use Innoboxrr\LaravelUploads\Http\Requests\Upload\UploadRequest;
use Innoboxrr\LaravelUploads\Http\Requests\Upload\DisplayRequest;
use Innoboxrr\LaravelUploads\Http\Requests\Upload\DeleteRequest;
use Innoboxrr\LaravelUploads\Http\Requests\Upload\RestoreRequest;
use Innoboxrr\LaravelUploads\Http\Requests\Upload\ForceDeleteRequest;

class UploadController extends Controller
{
    
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function upload(UploadRequest $request)
    {
        return $request->handle();   
    }

    public function display(DisplayRequest $request, $user_id, $filename)
    {
        return $request->handle($user_id, $filename);   
    }

    public function delete(DeleteRequest $request)
    {
        return $request->handle();   
    }

    public function restore(RestoreRequest $request)
    {
        return $request->handle();   
    }

    public function forceDelete(ForceDeleteRequest $request)
    {
        return $request->handle();   
    }

}

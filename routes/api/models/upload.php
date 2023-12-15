<?php

use Illuminate\Support\Facades\Route;

Route::post('file', 'UploadController@uploadFile')
	->name('file');

Route::get('{upload_uuid}/display/{filename?}', 'UploadController@display')
	->name('display');

Route::delete('delete', 'UploadController@delete')
	->name('delete');

Route::post('restore', 'UploadController@restore')
	->name('restore');

Route::delete('force-delete', 'UploadController@forceDelete')
	->name('force.delete');


<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

Route::get('/', function(Request $request) {

    if (Upload::canUpload($request->ip()) !== true) {
        return view('cannotupload', [
            'u' => $request->get('u')
        ]);
    }
    else {
        return redirect()->route('upload.create');
    }

})->name('homepage');

Route::prefix('upload')->middleware(['web', 'upload'])->group(function() {
    Route::get('/', [
        'uses'      => 'UploadController@create',
        'as'        => 'upload.create'
    ]);

    Route::post('/file', [
        'uses'      => 'UploadController@store',
        'as'        => 'upload.store'
    ]);

    Route::post('/complete', [
        'uses'      => 'UploadController@complete',
        'as'        => 'upload.complete'
    ]);
});

Route::prefix('bundle')->group(function() {
    Route::get('/{bundle}', [
        'uses'      => 'BundleController@preview',
        'as'        => 'bundle.preview'
    ]);

    Route::get('/{bundle}/download', [
        'uses'      => 'BundleController@download',
        'as'        => 'bundle.download'
    ]);

    Route::get('/{bundle}/file/{file}/download', [
        'uses'      => 'BundleController@download',
        'as'        => 'file.download'
    ]);

    Route::get('/{bundle}/delete', [
        'uses'      => 'BundleController@delete',
        'as'        => 'bundle.delete'
    ]);
});

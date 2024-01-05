<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\WebController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\BundleController;
use App\Http\Middleware\UploadAccess;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


/**
Public route for login
*/
Route::controller(WebController::class)->group(function() {
	Route::get('/login', 'login')->name('login');
	Route::post('/login', 'doLogin')->name('login.post');
	Route::get('/logout', 'logout')->name('logout');
});

/**
Upload routes
*/
Route::middleware(['can.upload'])->group(function() {
	Route::get('/', [WebController::class, 'homepage'])->name('homepage');
	Route::post('/new', [WebController::class, 'newBundle'])->name('bundle.new');

	Route::prefix('/upload/{bundle}')->controller(UploadController::class)->name('upload.')->group(function() {
		Route::get('/', 'createBundle')->name('create.show');

		Route::middleware(['access.owner'])->group(function() {
			Route::post('/', 'storeBundle')->name('create.store');
			Route::get('/metadata', 'getMetadata')->name('metadata.get');
			Route::post('/file', 'uploadFile')->name('file.store');
			Route::delete('/file', 'deleteFile')->name('file.delete');
			Route::post('/complete', 'completeBundle')->name('complete');
			Route::delete('/delete', 'deleteBundle')->name('bundle.delete');
		});
	});

});

/**
Download routes
*/
Route::middleware(['access.guest'])->prefix('/bundle/{bundle}')->controller(BundleController::class)->name('bundle.')->group(function() {
	Route::get('/preview', 'previewBundle')->name('preview');
	Route::post('/zip', 'prepareZip')->name('zip.make');
	Route::get('/download', 'downloadZip')->name('zip.download');
});

<?php

namespace App\Http\Controllers;

use Exception;
use App\Helpers\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UploadController extends Controller
{
	public function createBundle(Request $request, String $bundleId = null) {
		$metadata = Upload::getMetadata($bundleId);

		abort_if(empty($metadata), 404);

		return view('upload', [
			'metadata'  	=> $metadata ?? null,
			'baseUrl'		=> config('app.url')
		]);
	}

	public function getMetadata(Request $request, String $bundleId) {
		return response()->json(Upload::getMetadata($bundleId));
	}

	// The upload form
	public function storeBundle(Request $request, String $bundleId) {

		$original = Upload::getMetadata($bundleId);

		$metadata = [
			'expiry'		=> $request->expiry ?? null,
			'password'  	=> $request->password ?? null,
			'title'			=> $request->title ?? null,
			'description'	=> $request->description ?? null,
			'max_downloads'	=> $request->max_downloads ?? 0
		];

		$metadata = Upload::setMetaData($bundleId, $metadata);

		// Creating the bundle folder
		Storage::disk('uploads')->makeDirectory($bundleId);

		return response()->json($metadata);
	}

	public function uploadFile(Request $request, String $bundleId) {

		// Getting metadata
		$metadata = Upload::getMetadata($bundleId);

		// Validating file
		abort_if(! $request->hasFile('file'), 401);
		abort_if(! $request->file('file')->isValid(), 401);

		$this->validate($request, [
			'file'	=> 'required|file|max:'.(Upload::fileMaxSize() / 1000)
		]);

		// Generating the file name
		$original   = $request->file->getClientOriginalName();
		$filename 	= substr(sha1($original.time()), 0, rand(20, 30));

		// Moving file to final destination
		try {
			$fullpath = $request->file('file')->storeAs(
				$bundleId, $filename, 'uploads'
			);

			// Generating file metadata
			$file = [
				'uuid'  				=> Str::uuid(),
				'original'  			=> $original,
				'filesize'  			=> Storage::disk('uploads')->size($fullpath),
				'fullpath'  			=> $fullpath,
				'filename'				=> $filename,
				'created_at'			=> time(),
				'status'				=> true
			];

			$metadata = Upload::addFileMetaData($bundleId, $file);

			return response()->json([
				'result'	=> true
			]);
		}
		catch (Exception $e) {
			return response()->json([
				'result' 	=> false,
				'error'		=> $e->getMessage(),
				'file'  	=> $e->getFile(),
				'line'  	=> $e->getLine()
			], 500);
		}
	}

	public function deleteFile(Request $request, String $bundleId) {

		$metadata = Upload::getMetadata($bundleId);

		abort_if(empty($request->file), 401);

		try {
			$metadata = Upload::deleteFile($bundleId, $request->file);
			return response()->json($metadata);
		}
		catch (Exception $e) {
			return response()->json([
				'result' 	=> false,
				'error'		=> $e->getMessage(),
				'file'  	=> $e->getFile(),
				'line'  	=> $e->getLine()
			], 500);
		}
	}


	public function completeBundle(Request $request, String $bundleId) {

		$metadata = Upload::getMetadata($bundleId);

		// Processing size
		if (! empty($metadata['files'])) {
			$size = 0;
			foreach ($metadata['files'] as $f) {
				$size += $f['filesize'];
			}
		}

		// Saving metadata
		try {
			$preview_token = substr(sha1(uniqid('dbdl', true)), 0, rand(10, 15));

			$metadata = Upload::setMetadata($bundleId, [
				'completed'		=> true,
				'expires_at'	=> time()+$metadata['expiry'],
				'fullsize'		=> $size,
				'preview_token'	=> $preview_token,
				'preview_link'	=> route('bundle.preview', ['bundle' => $bundleId, 'auth' => $preview_token]),
				'download_link'	=> route('bundle.zip.download', ['bundle' => $bundleId, 'auth' => $preview_token]),
				'deletion_link'	=> route('upload.bundle.delete', ['bundle' => $bundleId])
			]);

			return response()->json($metadata);
		}
		catch (\Exception $e) {
			return response()->json([
				'result'		=> false,
				'error'			=> $e->getMessage()
			], 500);
		}
	}

	/**
	 * In this method, we do not delete files
	 * physically to spare some time and ressources.
	 * We invalidate the expiry date and let the CRON
	 * task do the hard work
	 */
	public function deleteBundle(Request $request, $bundleId) {

		// Tries to get the metadata file
		$metadata = Upload::getMetadata($bundleId);

		// Forcing file to expire
		$metadata['expires_at'] = time() - (3600 * 24 * 30);

		// Rewriting the metadata file
		try {
			$metadata = Upload::setMetadata($bundleId, $metadata);
			return response()->json($metadata);
		}
		catch (Exception $e) {
			return response()->json([
				'success'		=> false
			]);
		}
	}

}

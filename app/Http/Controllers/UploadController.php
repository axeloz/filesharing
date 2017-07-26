<?php

namespace app\Http\Controllers;

use Upload;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UploadController extends Controller
{
	// The upload form
	public function create(Request $request) {
		// Generating unique ID for multiple uploads bundle
		$bundle_id 	= substr(sha1(uniqid(null, true)), 0, rand(10, 20));

		return view('upload', [
			'bundle_id' => $bundle_id
		]);
	}


	// Receiving one uploaded file
	public function store(Request $request) {

		// Bundle ID must the sent among the request
		if (empty($request->header('X-Upload-Bundle'))) {
			throw new Exception('Invalid request');
		}

		if (! $request->hasFile('file')) {
			throw new Exception('No file was attached to the request');
		}

		if (! $request->file('file')->isValid()) {
			throw new Exception('Uploaded file is not valid');
		}

		// Validating file upload
		$this->validate($request, [
			'file'	=> 'required|file|max:'.(Upload::fileMaxSize() / 1000)
		]);

		// Generating the file name
		$original   = $request->file->getClientOriginalName();
		$filename 	= substr(sha1($original.time()), 0, rand(10, 20));

		// Getting the storage path
		$path = Upload::generateFilePath($filename);

		// Moving file to final destination
		try {
			$fullpath = $request->file('file')->storeAs(
				$path, $filename, 'uploads'
			);

			// Generating file metadata
			$file = [
				'original'              => $original,
				'filesize'              => Storage::disk('uploads')->size($fullpath),
				'fullpath'              => $fullpath,
				'filename'				=> $filename,
				'created_at'			=> time()
			];

			$request->session()->push($request->header('X-Upload-Bundle').'.files', $file);
			return response()->json([
				'result' 	=> true
			]);
		}
		catch (Exception $e) {
			return response()->json([
				'result' 	=> false,
				'error'		=> $e->getMessage()
			], 500);
		}
	}

	public function complete(Request $request) {

		// Bundle ID must be sent among headers
		abort_if(empty($request->header('X-Upload-Bundle')), 401);

		// Getting files from session
		if (! $bundle = $request->session()->get($request->header('X-Upload-Bundle'))) {
			$bundle = [];
		}

		// Aborting if no file was uploaded
		abort_if(empty($bundle['files']) || count($bundle['files']) == 0, 500);

		// And clearing content from the session
		$request->session()->forget($request->header('X-Upload-Bundle'));

		// Getting an existing metadata file if applicable
		if (! $metadata = Upload::getMetadata($request->header('X-Upload-Bundle'))) {
			$metadata = [
				'created_at'	=> time(),
				'expires_at'	=> time()+60*60*24*15, # TODO : make this editable in the FRONT
				'bundle_id'		=> $request->header('X-Upload-Bundle'),
				'view-auth'		=> substr(sha1(uniqid('', true)), 0, rand(6, 10)),
				'delete-auth'	=> substr(sha1(uniqid('', true)), 0, rand(6, 10)),
				'fullsize'		=> 0,
				'files'			=> $bundle['files']
			];
		}
		// The metadata file already exists
		else {
			// Adding bundle files to metadata
			$metadata['files'] = array_merge($metadata['files'], $bundle['files']);
		}

		// Processing size
		if (! empty($metadata['files'])) {
			$size = 0;
			foreach ($metadata['files'] as $f) {
				$size += $f['filesize'];
			}
			$metadata['fullsize'] = $size;
		}

		// Saving metadata
		try {
			Storage::disk('uploads')->put('bundles/'.$request->header('X-Upload-Bundle').'.json', json_encode($metadata));
			return response()->json([
				'result'			=> true,
				'bundle_url'		=> route('bundle.preview', [
					'bundle' 		=> $request->header('X-Upload-Bundle'),
					'auth' 			=> $metadata['view-auth']
				]),
				'delete_url'		=> route('bundle.delete', [
					'bundle' 		=> $request->header('X-Upload-Bundle'),
					'auth'			=> $metadata['delete-auth']
				]),
				'download_url'		=> route('bundle.download', [
					'bundle' 		=> $request->header('X-Upload-Bundle'),
					'auth'			=> $metadata['view-auth']
				])
			]);
		}
		catch (Exception $e) {
			return response()->json([
				'result'		=> false,
				'error'			=> $e->getMessage()
			], 500);
		}
	}

}


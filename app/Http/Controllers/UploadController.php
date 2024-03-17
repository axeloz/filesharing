<?php

namespace App\Http\Controllers;

use Exception;
use App\Helpers\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\BundleResource;
use App\Http\Resources\FileResource;

use App\Models\Bundle;
use App\Models\File;

class UploadController extends Controller
{
	public function createBundle(Request $request, Bundle $bundle) {
		return view('upload', [
			'bundle'  		=> new BundleResource($bundle),
			'baseUrl'		=> config('app.url')
		]);
	}

	// The upload form
	public function storeBundle(Request $request, Bundle $bundle) {

		try {
			$bundle->update([
				'expiry'		=> $request->expiry ?? null,
				'password'  	=> $request->password ?? null,
				'title'			=> $request->title ?? null,
				'description'	=> $request->description ?? null,
				'max_downloads'	=> $request->max_downloads ?? 0
			]);

			return response()->json(new BundleResource($bundle));
		}
		catch (Exception $e) {
			return response()->json([
				'result'	=> false,
				'message'	=> $e->getMessage()
			], 500);
		}
	}

	public function uploadFile(Request $request, Bundle $bundle) {

		// Validating form data
		$request->validate([
			'uuid'		=> 'required|uuid',
			'file'		=> 'required|file|max:'.(Upload::fileMaxSize() / 1000)
		]);

		// Generating the file name
		$original   = $request->file->getClientOriginalName();
		$filename 	= substr(sha1($original.time()), 0, mt_rand(20, 30));

		// Moving file to final destination
		try {
			$size = $request->file->getSize();
			if (config('sharing.upload_prevent_duplicates', true) === true && $size < Upload::humanReadableToBytes(config('sharing.hash_maxfilesize', '1G'))) {
				$hash = sha1_file($request->file->getPathname());

				$existing = $bundle->files->whereNotNull('hash')->where('hash', $hash)->count();
				if (! empty($existing) && $existing > 0) {
					throw new Exception(__('app.duplicate-file'));
				}
			}

			$fullpath = $request->file('file')->storeAs(
				$bundle->slug, $filename, 'uploads'
			);

			if (false === $fullpath) {
				throw new Exception('An error occurred while storing the file');
			}

			// Generating file metadata
			$file = new File([
				'uuid'  				=> $request->uuid,
				'bundle_slug'			=> $bundle->slug,
				'original'  			=> $original,
				'filesize'  			=> $size,
				'fullpath'  			=> $fullpath,
				'filename'				=> $filename,
				'created_at'			=> time(),
				'status'				=> true,
				'hash'					=> $hash ?? null
			]);
			$file->save();

			return response()->json(new FileResource($file));
		}
		catch (Exception $e) {
			return response()->json([
				'result' 	=> false,
				'message'	=> $e->getMessage()
			], 500);
		}
	}

	public function deleteFile(Request $request, Bundle $bundle) {

		$request->validate([
			'uuid'		=> 'required|uuid'
		]);

		try {
			// Getting file model
			$file = File::findOrFail($request->uuid);

			// Physically deleting the file
			if (! Storage::disk('uploads')->delete($file->fullpath)) {
				throw new Exception('Cannot delete file from disk');
			}

			// Destroying the model
			$file->delete();

			return response()->json(new BundleResource($bundle));
		}
		catch (Exception $e) {
			return response()->json([
				'result' 	=> false,
				'message'	=> $e->getMessage()
			], 500);
		}
	}


	public function completeBundle(Request $request, Bundle $bundle) {

		// Processing size
		$size = 0;
		foreach ($bundle->files as $f) {
			$size += $f['filesize'];
		}

		// Saving metadata
		try {
			$bundle->completed		= true;

			// Infinite expiry
			if ($bundle->expiry == 'forever') {
				$bundle->expires_at = null;
			}
			else {
				$bundle->expires_at		= time()+$bundle->expiry;
			}
			$bundle->fullsize		= $size;
			$bundle->preview_link	= route('bundle.preview', ['bundle' => $bundle, 'auth' => $bundle->preview_token]);
			$bundle->download_link	= route('bundle.zip.download', ['bundle' => $bundle, 'auth' => $bundle->preview_token]);
			$bundle->deletion_link	= route('upload.bundle.delete', ['bundle' => $bundle]);
			$bundle->save();

			return response()->json(new BundleResource($bundle));
		}
		catch (\Exception $e) {
			return response()->json([
				'result'		=> false,
				'message'		=> $e->getMessage()
			], 500);
		}
	}

	/**
	 * In this method, we do not delete files
	 * physically to spare some time and ressources.
	 * We invalidate the expiry date and let the CRON
	 * task do the hard work
	 */
	public function deleteBundle(Request $request, Bundle $bundle) {

		try {
			// Forcing bundle to expire
			$bundle->expires_at = time() - (3600 * 24 * 30);
			$bundle->save();

			// Then deleting file models
			foreach ($bundle->files as $f) {
				$f->forceDelete();
			}

			// Finally deleting bundle
			$bundle->forceDelete();

			return response()->json([
				'success'	=> true
			]);
		}
		catch (Exception $e) {
			return response()->json([
				'success'		=> false,
				'message'		=> $e->getMessage()
			], 500);
		}
	}

}

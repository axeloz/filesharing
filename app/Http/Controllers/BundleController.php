<?php

namespace app\Http\Controllers;

use Upload;
use ZipArchive;
use Exceptions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class BundleController extends Controller
{

	// The bundle content preview
	public function preview(Request $request, $bundle_id) {

		// Getting bundle metadata
		abort_if(! $metadata = Upload::getMetadata($bundle_id), 404);

		// Checking authorization code
		abort_if($metadata['view-auth'] != $request->get('auth'), 401);

		// Checking bundle expiration
		// TODO : make this editable
		abort_if($metadata['expires_at'] < time(), 404);

		// Handling dates as Carbon
		Carbon::setLocale(config('app.locale'));
		$metadata['created_at_carbon'] = Carbon::createFromTimestamp($metadata['created_at']);
		$metadata['expires_at_carbon'] = Carbon::createFromTimestamp($metadata['expires_at']);

		return view('preview', [
			'bundle_id'		=> $bundle_id,
			'metadata'		=> $metadata,
		]);

	}

	// The download method
	// - the bundle
	// - or just one file
	public function download(Request $request, $bundle_id, $file_id = null) {

		// Getting bundle metadata
		abort_if(! $metadata = Upload::getMetadata($bundle_id), 404);

		// Checking authorization code
		abort_if($metadata['view-auth'] != $request->get('auth'), 401);

		// Checking bundle expiration
		// TODO : make this editable
		abort_if($metadata['expires_at'] < time(), 404);

		// If there is no file into the bundle (should never happen but ...)
		abort_if(count($metadata['files']) == 0, 404);

		try {
			// Download of the full bundle
			// We must create a Zip archive
			if (empty($file_id)) {
				// Timestamped filename
				$tmp 		= tempnam(null, 'bundle-');
				$zipname 	= 'bundle-'.date('Y-m-d-H-i').'.zip';

				// Creating the archive
				$zip = new ZipArchive;
				$zip->open($tmp, ZipArchive::OVERWRITE);

				// Adding the files into the Zip with their real names
				foreach ($metadata['files'] as $file) {
					$zip->addFile(config('filesystems.disks.uploads.root').'/'.$file['fullpath'], $file['original']);
				}
				$zip->close();

				// Let's download now
				header('Content-Type: application/octet-stream');
				header('Content-Disposition: attachment; filename="'.$zipname.'"');
				header('Cache-Control: no-cache, must-revalidate');
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
				header('Content-Length: '.filesize($tmp));
				readfile($tmp);
				@unlink($tmp);
				exit;
			}

			// Download of one particular file only
			else {
				$file = null;

				// Looking for the file in the bundle
				foreach ($metadata['files'] as $f) {
					if ($f['filename'] == $file_id) {
						// We found the file
						$file = $f;
						break;
					}
				}

				// If we could find this file into the bundle
				if (! empty($file)) {

					// Let's download it
					header('Content-Type: application/octet-stream');
					header('Content-Disposition: attachment; filename="'.$file['original'].'"');
					header('Cache-Control: no-cache, must-revalidate');
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
					header('Content-Length: '.$file['filesize']);
					echo Storage::disk('uploads')->get($file['fullpath']);
					exit;
				}

				// Could not find the file into the bundle
				else {
					abort(404);
				}
			}
		}

		// Could not find the metadata file
		catch (Exception $e) {
			abort(500);
		}

	}

	/**
	 * In this method, we do not delete files
	 * physically to spare some time and ressources.
	 * We invalidate the expiry date and let the CRON
	 * task do the hard work
	 */
    public function delete(Request $request, $bundle_id) {

		// Tries to get the metadata file
		$metadata = Upload::getMetadata($bundle_id);

		// Checking authorization code
		abort_if($metadata['delete-auth'] != $request->get('auth'), 401);

		// Forcing file to expire
		$metadata['expires_at'] = time() - 1000;

		// Rewriting the metadata file
		Storage::disk('uploads')->put('bundles/'.$bundle_id.'.json', json_encode($metadata));

		return redirect()->route('upload.create')->with('status', __('bundle-deleted'));
    }
}

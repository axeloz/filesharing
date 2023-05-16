<?php

namespace App\Http\Controllers;

use ZipArchive;
use Exception;
use Carbon\Carbon;
use App\Helpers\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;


class BundleController extends Controller
{

	// The bundle content preview
	public function previewBundle(Request $request, $bundleId) {

		// Getting bundle metadata
		abort_if(! $metadata = Upload::getMetadata($bundleId), 404);

		// Handling dates as Carbon
		Carbon::setLocale(config('app.locale'));
		$metadata['created_at_carbon'] = Carbon::createFromTimestamp($metadata['created_at']);
		$metadata['expires_at_carbon'] = Carbon::createFromTimestamp($metadata['expires_at']);

		return view('download', [
			'bundleId'		=> $bundleId,
			'metadata'		=> $metadata,
			'auth'  		=> $metadata['preview_token']
		]);

	}

	// The download method
	// - the bundle
	// - or just one file
	public function downloadZip(Request $request, $bundleId) {

		// Getting bundle metadata
		abort_if(! $metadata = Upload::getMetadata($bundleId), 404);

		try {
			// Download of the full bundle
			// We must create a Zip archive
			Upload::setMetadata($bundleId, [
				'downloads'		=> $metadata['downloads'] + 1
			]);

			$filename	= config('filesystems.disks.uploads.root').'/'.$metadata['bundle_id'].'/bundle.zip';
			if (1 == 1 || ! file_exists($filename)) {
				// Timestamped filename
				$bundlezip 	= fopen($filename, 'w');
				//chmod($filename, 0600);

				// Creating the archive
				$zip = new ZipArchive;
				if (! @$zip->open($filename, ZipArchive::CREATE)) {
					throw new Exception('Cannot initialize Zip archive');
				}

				// Setting password when required
				if (! empty($metadata['password'])) {
					$zip->setPassword($metadata['password']);
				}

				// Adding the files into the Zip with their real names
				foreach ($metadata['files'] as $k => $file) {
					if (file_exists(config('filesystems.disks.uploads.root').'/'.$file['fullpath'])) {
						$zip->addFile(config('filesystems.disks.uploads.root').'/'.$file['fullpath'], $file['original']);

						if (! empty($metadata['password'])) {
							$zip->setEncryptionIndex($k, ZipArchive::EM_AES_256);
						}
					}
				}

				if (! @$zip->close()) {
					throw new Exception('Cannot close Zip archive');
				}

				fclose($bundlezip);
			}

			// Let's download now
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.Str::slug($metadata['title']).'-'.time().'.zip'.'"');
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
			//TODO : fix this header('Content-Length: '.filesize($bundlezip));
			readfile($filename);
			exit;
		}

		// Could not find the metadata file
		catch (Exception $e) {
			abort(500, $e->getMessage());
		}

	}

}

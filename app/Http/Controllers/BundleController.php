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
						$name = $file['original'];

						// If a file in the archive has the same name
						if (false !== $zip->locateName($name)) {
							$i = 0;

							// Exploding the basename and extension
							$basename	= (false === strrpos($name, '.')) ? $name : substr($name, 0, strrpos($name, '.'));
							$extension	= (false === strrpos($name, '.')) ? null : substr($name, strrpos($name, '.'));

							// Looping to find the right name
							do {
								$i++;
								$newname = $basename.'-'.$i.$extension;
							}
							while ($zip->locateName($newname));

							// Final name was found
							$name = $newname;
						}
						// Finally adding files
						$zip->addFile(config('filesystems.disks.uploads.root').'/'.$file['fullpath'], $name);

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

			// Getting file size
			$filesize = filesize($filename);

			// Should we limit the download rate?
			$limit_rate = config('sharing.download_limit_rate', false);
			if ($limit_rate !== false) {
				$limit_rate = Upload::humanReadableToBytes($limit_rate);
			}
			else {
				$limit_rate = $filesize;
			}

			// Let's download now
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.Str::slug($metadata['title']).'-'.time().'.zip'.'"');
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
			header('Content-Length: '.$filesize);

			flush();


			$fh = fopen($filename, 'rb');
			while (! feof($fh)) {
				echo fread($fh, round($limit_rate));
				flush();

				if ($limit_rate !== false) {
					sleep(1);
				}
			}
			fclose($filename);
			exit;

		}

		// Could not find the metadata file
		catch (Exception $e) {
			abort(500, $e->getMessage());
		}

	}

}

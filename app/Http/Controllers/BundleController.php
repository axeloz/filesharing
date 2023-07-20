<?php

namespace App\Http\Controllers;

use ZipArchive;
use Exception;
use App\Helpers\Upload;
use App\Models\Bundle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Resources\BundleResource;

class BundleController extends Controller
{

	// The bundle content preview
	public function previewBundle(Request $request, Bundle $bundle) {
		return view('download', [
			'bundle'		=> new BundleResource($bundle)
		]);

	}

	// The download method
	// - the bundle
	// - or just one file
	public function downloadZip(Request $request, Bundle $bundle) {

		try {
			// Download of the full bundle
			// We must create a Zip archive
			$bundle->downloads ++;
			$bundle->save();


			$filename	= Storage::disk('uploads')->path('').'/'.$bundle->slug.'/bundle.zip';
			if (! file_exists($filename)) {
				$bundlezip 	= fopen($filename, 'w');

				// Creating the archive
				$zip = new ZipArchive;
				if (! @$zip->open($filename, ZipArchive::CREATE)) {
					throw new Exception('Cannot initialize Zip archive');
				}

				// Setting password when required
				if (! empty($bundle->password)) {
					$zip->setPassword($bundle->password);
				}

				// Adding the files into the Zip with their real names
				foreach ($bundle->files as $k => $file) {
					if (file_exists(config('filesystems.disks.uploads.root').'/'.$file->fullpath)) {
						$name = $file->original;

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
						$zip->addFile(config('filesystems.disks.uploads.root').'/'.$file->fullpath, $name);

						if (! empty($bundle->password)) {
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

			// Let's download now
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="'.Str::slug($bundle->title).'-'.time().'.zip'.'"');
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
			header('Content-Length: '.$filesize);

			// Downloading
			if (config('sharing.download_limit_rate', false) !== false) {
				$limit_rate = Upload::humanReadableToBytes(config('sharing.download_limit_rate'));

				$fh = fopen($filename, 'rb');
				while (! feof($fh)) {
					echo fread($fh, round($limit_rate));
					flush();
					sleep(1);
				}
				fclose($filename);
			}
			else {
				readfile($filename);
			}
			exit;

		}

		// Could not find the metadata file
		catch (Exception $e) {
			abort(500, $e->getMessage());
		}

	}

}

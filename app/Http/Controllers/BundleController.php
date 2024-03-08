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
	public function previewBundle(Request $request, Bundle $bundle)
	{
		return view('download', [
			'bundle'		=> new BundleResource($bundle)
		]);
	}

	// The download method
	// - the bundle
	// - or just one file
	public function downloadZip(Request $request, Bundle $bundle)
	{

		// try {
		// Download of the full bundle
		// We must create a Zip archive
		$bundle->downloads++;
		$bundle->save();


		$filename = $bundle->slug . '/bundle.zip';
		if (!Storage::exists($filename)) {
			// creating the zip archive
			$zip = new ZipArchive;
			$tempFilePath = tempnam(sys_get_temp_dir(), 'zip');


			if (!$zip->open($tempFilePath, ZipArchive::CREATE)) {
				throw new Exception('Cannot initialize Zip archive');
			}

			// Setting password when required
			if (!empty($bundle->password)) {
				$zip->setPassword($bundle->password);
			}

			// Adding the files into the Zip with their real names
			foreach ($bundle->files as $k => $file) {
				// get the bundle/filename
				// dd((Storage::getConfig()['root'] ?? '') . '/' . $file->fullpath);
				if (Storage::exists($file->fullpath)) {
					$name = $file->original;
					$stream = Storage::get($file->fullpath);

					// If a file in the archive has the same name
					if (false !== $zip->locateName($name)) {
						$i = 0;

						// Exploding the basename and extension
						$basename	= (false === strrpos($name, '.')) ? $name : substr($name, 0, strrpos($name, '.'));
						$extension	= (false === strrpos($name, '.')) ? null : substr($name, strrpos($name, '.'));

						// Looping to find the right name
						do {
							$i++;
							$newname = $basename . '-' . $i . $extension;
						} while (false !== $zip->locateName($newname));

						// Final name was found
						$name = $newname;
						dd(2);
					}
					// Finally adding files
					$zip->addFromString($name, $stream);

					if (!empty($bundle->password)) {
						$zip->setEncryptionIndex($k, ZipArchive::EM_AES_256);
					}
				}
			}


			if (!@$zip->close()) {
				throw new Exception('Cannot close Zip archive');
			}
			Storage::put($filename, file_get_contents($tempFilePath));
		}

		if (Storage::getConfig()['driver'] == 'local') {
			// Getting file size
			$filesize = filesize(Storage::path($filename));

			// Let's download now
			return response()->streamDownload(function () use ($filename) {
				// Downloading
				if (config('sharing.download_limit_rate', false) !== false) {
					$limit_rate = Upload::humanReadableToBytes(config('sharing.download_limit_rate'));

					$fh = fopen(Storage::path($filename), 'rb');
					while (!feof($fh)) {
						echo fread($fh, round($limit_rate));
						flush();
						sleep(1);
					}
					fclose($filename);
				} else {
					readfile(Storage::path($filename));
				}
			}, Str::slug($bundle->title) . '-' . time() . '.zip', [
				'Content-Length' => $filesize,
				'Content-Type' => 'application/zip'
			]);
		} else if (Storage::getConfig()['driver'] == 's3') {
			// Cannot limit the download rate
			return Storage::download($filename, Str::slug($bundle->title) . '-' . time() . '.zip');
		} else {
			// Handle other drivers
		}
	}

	// Could not find the metadata file
	// catch (Exception $e) {
	// 	abort(500, $e->getMessage());
	// }
	// }
}

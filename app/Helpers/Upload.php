<?php

namespace app\Helpers;

use Illuminate\Support\Facades\Storage;

class Upload {

	public static function generateFilePath(string $token) {
		$path = 'files/';
		for ($i = 0; $i < 3; $i++) {
			$letter = substr($token, $i, 1);
			$path .= $letter.'/';
		}
		return rtrim($path, '/');
	}


	public static function getMetadata(string $bundle_id) {
		// Making sure the metadata file exists
		if (! Storage::disk('uploads')->exists('bundles/'.$bundle_id.'.json')) {
			return false;
		}

		// Getting metadata file contents
		$metadata = Storage::disk('uploads')->get('bundles/'.$bundle_id.'.json');

		// Json decoding the metadata
		if ($json = json_decode($metadata, true)) {
			return $json;
		}

		return false;
	}

	public static function humanFilesize(float $size, $precision = 2)
	{
		if ($size > 0) {
			$size = (int) $size;
			$base = log($size) / log(1024);
			$suffixes = array(' bytes', ' KB', ' MB', ' GB', ' TB');

			return round(pow(1024, $base - floor($base)), $precision) . $suffixes[floor($base)];
		}
		else {
			return $size;
		}
	}


	public static function fileMaxSize($human = false) {
		$values = [
			'post'		=> ini_get('post_max_size'),
			'upload'	=> ini_get('upload_max_filesize'),
			'memory'	=> ini_get('memory_limit'),
			'config'	=> config('sharing.max_filesize')
		];

		foreach ($values as $k => $v) {
			$unit = preg_replace('/[^bkmgtpezy]/i', '', $v);
			$size = preg_replace('/[^0-9\.]/', '', $v);
			if ($unit) {
				// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
				$values[$k] = round($v * pow(1024, stripos('bkmgtpezy', $unit[0])));
			}
		}

		$min = min($values);
		if ($human === true) {
			return self::humanFilesize($min);
		}
		return $min;
	}
}

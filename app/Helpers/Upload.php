<?php

namespace App\Helpers;

use Exception;
use Illuminate\Support\Facades\Storage;

class Upload {

	public static function getMetadata(String $bundleId):Array {
		// Making sure the metadata file exists
		if (! Storage::disk('uploads')->exists($bundleId.'/bundle.json')) {
			return [];
		}

		// Getting metadata file contents
		$metadata = Storage::disk('uploads')->get($bundleId.'/bundle.json');

		// Json decoding the metadata
		if ($json = json_decode($metadata, true)) {
			return $json;
		}

		return [];
	}

	public static function setMetadata(String $bundleId, Array $metadata = []):Array {

		$origin 	= self::getMetadata($bundleId);
		$updated	= array_merge($origin, $metadata);

		if (! Storage::disk('uploads')->put($bundleId.'/bundle.json', json_encode($updated))) {
			throw new Exception('Cannot store metadata');
		}

		return $updated;
	}

	public static function addFileMetaData(String $bundleId, Array $file):Array {
		$metadata = self::getMetadata($bundleId);

		if (empty($metadata)) {
			$metadata = [
				'files' => []
			];
		}

		array_unshift($metadata['files'], $file);
		self::setMetadata($bundleId, $metadata);

		return $metadata;
	}

	public static function deleteFile(String $bundleId, String $uuid):Array {
		$metadata = self::getMetadata($bundleId);

		if (! empty($metadata['files'])) {
			foreach ($metadata['files'] as $key => $file) {
				if ($file['uuid'] == $uuid) {
					if (! Storage::disk('uploads')->delete($file['fullpath'])) {
						throw new Exception('Cannot delete file from disk');
					}
					unset($metadata['files'][$key]);
				}
			}

			self::setMetadata($bundleId, $metadata);
		}

		return $metadata;
	}

	public static function humanFilesize(Float $size, Int $precision = 2):String {
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

	public static function fileMaxSize(Bool $human = false):String {
		$values = [
			'post'		=> ini_get('post_max_size'),
			'upload'	=> ini_get('upload_max_filesize'),
			'memory'	=> ini_get('memory_limit'),
			'config'	=> config('sharing.max_filesize')
		];

		foreach ($values as $k => $v) {
			$values[$k] = self::humanReadableToBytes($v);
		}

		$min = min($values);
		if ($human === true) {
			return self::humanFilesize($min);
		}
		return $min;
	}

	public static function humanReadableToBytes(String $value):Int {
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $value);
		$size = preg_replace('/[^0-9\.]/', '', $value);
		if (! empty($unit)) {
			$value = round($size * pow(1024, stripos('bkmgtpezy', $unit[0])), 1);
		}
		return $value;
	}

	public static function isDuplicateFile(String $bundleId, String $hash):Bool {
		$metadata = self::getMetadata($bundleId);
		foreach ($metadata['files'] as $f) {
			if ($f['hash'] !== null && $f['hash'] === $hash) {
				return true;
			}
		}
		return false;
	}

	public static function canUpload(String $current_ip):Bool {

		// Getting the IP limit configuration
		$ips = config('sharing.upload_ip_limit');

		if (empty($ips)) {
			return true;
		}

		$ips = explode(',', $ips);

		// If set and not empty, checking client's IP
		if (! empty($ips) && count($ips) > 0) {
			$valid  	= false;

			foreach ($ips as $ip) {
				$ip = trim($ip);

				// Client's IP appears in the whitelist
				if (self::isValidIp($current_ip, $ip)) {
					$valid = true;
					break;
				}
			}

			// Client's IP is not allowed
			if ($valid === false) {
				return false;
			}
		}

		return true;
	}

	public static function isValidIp(String $ip, String $range):Bool {

		// Range is in CIDR format
		if (strpos($range, '/') !== false) {
			list($range, $netmask) = explode('/', $range, 2);

			// Netmask is a 255.255.0.0 format
			if (strpos($netmask, '.') !== false) {
				$netmask = str_replace('*', '0', $netmask);
				$netmask_dec = ip2long($netmask);
				return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
			}
			// Netmask is a CIDR size block
			else {
				// fix the range argument
				$x = explode('.', $range);

				while(count($x) < 4) {
					$x[] = 0;
				}

				list($a, $b, $c, $d) = $x;
				$range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
				$range_dec = ip2long($range);
				$ip_dec = ip2long($ip);

				$wildcard_dec = pow(2, (32-$netmask)) - 1;
				$netmask_dec = ~ $wildcard_dec;

				return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
			}
		}
		// Range might be 255.255.*.* or 1.2.3.0-1.2.3.255
		elseif (strpos($range, '*') !== false || strpos($range, '-') !== false) {

			// a.b.*.* format
			if (strpos($range, '*') !== false) {
				// Just convert to A-B format by setting * to 0 for A and 255 for B
				$lower = str_replace('*', '0', $range);
				$upper = str_replace('*', '255', $range);
				$range = "$lower-$upper";
			}

			// A-B format
			if (strpos($range, '-') !== false) {
				list($lower, $upper) = explode('-', $range, 2);
				$lower_dec = (float)sprintf("%u", ip2long($lower));
				$upper_dec = (float)sprintf("%u", ip2long($upper));
				$ip_dec = (float)sprintf("%u", ip2long($ip));
				return ( ($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec) );
			}

			return false;
		}
		// Full IP format 192.168.10.10
		else {
			return ($ip == $range);
		}
		return false;
	}

	public static function getExpirySeconds(String $expiry):Int {
		$unit_multipliers = [
			'h'	=> 3600,
			'd'	=> 86400,
			'w' => 604800,
			'm'	=> 2592000
		];

		$unit = strtolower(substr(trim($expiry), -1));
		$value = (int)substr($expiry, 0, -1);

		if (empty($unit_multipliers[$unit]) || $value <= 0) {
			// 24h by default
			return $unit_multipliers['d'];
		}

		return ($value * $unit_multipliers[$unit]);
	}
}

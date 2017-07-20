<?php

return [

	'max_filesize'			=> env('UPLOAD_MAX_FILESIZE', '5M'),
	'max_files'				=> env('UPLOAD_MAX_FILES', 100),

	/**
	 ** IP v4 access limitations
	 ** Acceptable formats :
	 **  1. Full IP address (192.168.10.2)
	 **  2. Wildcard format (192.168.10.*)
	 **  3. CIDR Format (192.168.10/24) OR  1.2.3.4/255.255.255.0
	 **  4. Start-end IP (192.168.10.0-192.168.10.10)
	 */
	'upload_ip_limit'		=> [
		'127.0.0.1'
	]


];

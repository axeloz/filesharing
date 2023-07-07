<?php

return [

    'max_filesize'			=> env('UPLOAD_MAX_FILESIZE', '5M'),
    'max_files'				=> env('UPLOAD_MAX_FILES', 100),
    'expiry_values'			=> [
        '1H'    	=> 'one-hour',
        '2H'    	=> 'two-hours',
        '6H'    	=> 'six-hours',
        '12H'   	=> 'twelve-hours',
        '24H'   	=> 'one-day',
        '48H'   	=> 'two-days',
        '1W'    	=> 'one-week',
        '2W'    	=> 'two-weeks',
        '1M'    	=> 'one-month',
        '3M'    	=> 'three-months',
        '6M'    	=> 'six-months',
    ],
    'default_expiry'		=> 86400, // 1 Day,

    /**
     ** IP v4 access limitations
     ** Acceptable formats :
     **  1. Full IP address (192.168.10.2)
     **  2. Wildcard format (192.168.10.*)
     **  3. CIDR Format (192.168.10/24) OR  1.2.3.4/255.255.255.0
     **  4. Start-end IP (192.168.10.0-192.168.10.10)
     */
    'upload_ip_limit'		=> env('UPLOAD_LIMIT_IPS', null),

	'download_limit_rate'	=> env('LIMIT_DOWNLOAD_RATE', false),

	'upload_prevent_duplicates'	=> env('UPLOAD_PREVENT_DUPLICATES', true),

	/**
	 ** Max filesize hash processing
	 ** TODO: find the best value to avoid too long time processing
	 */
	 'hash_maxfilesize'		=> env('HASH_MAX_FILESIZE', '1G')
];

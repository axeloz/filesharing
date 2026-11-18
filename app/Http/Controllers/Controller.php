<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    public $release = '';

    public function __construct()
	{
		if (file_exists(base_path('RELEASE.txt'))) {
			$release = trim(file_get_contents(base_path('RELEASE.txt')));
			if (preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $release)) {
				define('RELEASE', $release);
				$this->release = $release;
			}
		}
	}
}

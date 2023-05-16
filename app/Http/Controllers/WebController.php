<?php

namespace App\Http\Controllers;
use App\Helpers\Upload;

use Illuminate\Http\Request;

class WebController extends Controller
{
	function homepage(Request $request)
	{
		return view('homepage');
	}

	function newBundle(Request $request) {
		// Aborting if request is not AJAX
		abort_if(! $request->ajax(), 401);

		$request->validate([
			'bundle_id'		=> 'required',
			'owner_token'	=> 'required'
		]);

		$metadata = [
			'created_at'	=> time(),
			'completed' 	=> false,
			'expiry'		=> config('sharing.default-expiry', 86400),
			'expires_at'	=> null,
			'password'  	=> null,
			'bundle_id'		=> $request->bundle_id,
			'owner_token'	=> $request->owner_token,
			'preview_token'	=> null,
			'fullsize'		=> 0,
			'files'			=> [],
			'title'			=> null,
			'description'	=> null,
			'max_downloads'	=> 0,
			'downloads'		=> 0
		];

		if (Upload::setMetadata($metadata['bundle_id'], $metadata)) {
			return response()->json($metadata);
		}
		else {
			abort(500);
		}
	}
}

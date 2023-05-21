<?php

namespace App\Http\Controllers;
use App\Helpers\Upload;
use App\Helpers\User;
use Exception;
use Illuminate\Http\Request;

class WebController extends Controller
{
	public function homepage()
	{
		return view('homepage');
	}

	public function login() {
		return view('login');
	}

	public function doLogin(Request $request) {
		abort_if(! $request->ajax(), 403);

		$request->validate([
			'login'		=> 'required|alphanum|min:4|max:40',
			'password'	=> 'required|min:5|max:100'
		]);

		try {
			if (true === User::loginUser($request->login, $request->password)) {
				return response()->json([
					'result'	=> true,
				]);
			}
		}
		catch (Exception $e) {
			return response()->json([
				'result'	=> false,
				'error'		=> 'Authentication failed, please try again.'
			], 403);
		}

		// This should never happen
		return response()->json([
			'result'	=> false,
			'error'		=> 'Unexpected error'
		]);
	}

	function newBundle(Request $request) {
		// Aborting if request is not AJAX
		abort_if(! $request->ajax(), 403);

		$request->validate([
			'bundle_id'		=> 'required',
			'owner_token'	=> 'required'
		]);

		$owner = null;
		if (User::isLogged()) {
			$user = User::getLoggedUserDetails();
			$owner = $user['username'];

			// If bundle dimension is not initialized
			if (empty($user['bundles']) || ! is_array($user['bundles'])) {
				$user['bundles'] = [];
			}

			array_push($user['bundles'], $request->bundle_id);
			User::setUserDetails($user['username'], $user);
		}

		$metadata = [
			'owner'			=> $owner,
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

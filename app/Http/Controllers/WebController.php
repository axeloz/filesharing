<?php

namespace App\Http\Controllers;
use App\Helpers\Upload;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

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
			'login'		=> 'required',
			'password'	=> 'required'
		]);

		try {
			if (Storage::disk('users')->missing($request->login.'.json')) {
				throw new Exception('Authentication failed');
			}

			$json = Storage::disk('users')->get($request->login.'.json');

			if (! $user = json_decode($json, true)) {
				throw new Exception('Cannot decode JSON file');
			}

			if (! Hash::check($request->password, $user['password'])) {
				throw new Exception('Authentication failed');
			}

			$request->session()->put('login', $request->login);
			$request->session()->put('authenticated', true);

			return response()->json([
				'result'	=> true,
			]);
		}
		catch (Exception $e) {
			return response()->json([
				'result'	=> false,
				'error'		=> $e->getMessage()
			]);
		}
	}

	function newBundle(Request $request) {
		// Aborting if request is not AJAX
		abort_if(! $request->ajax(), 403);

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

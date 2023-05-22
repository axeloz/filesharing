<?php

namespace App\Http\Controllers;
use App\Helpers\Auth;
use Exception;
use Illuminate\Http\Request;
use App\Http\Resources\BundleResource;
use App\Models\Bundle;

class WebController extends Controller
{
	public function homepage()
	{
		// Getting user bundles
		if (Auth::isLogged()) {
			$bundles = Auth::getLoggedUserDetails()->bundles;
			if (! empty($bundles) && $bundles->count() > 0) {
				$bundles = BundleResource::collection($bundles) ;
			}
		}
		return view('homepage', [
			'bundles'	=> $bundles ?? []
		]);
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
			if (true === Auth::loginUser($request->login, $request->password)) {
				return response()->json([
					'result'	=> true,
				]);
			}
		}
		catch (Exception $e) {
			return response()->json([
				'result'	=> false,
				'message'	=> 'Authentication failed, please try again.'
			], 403);
		}

		// This should never happen
		return response()->json([
			'result'	=> false,
			'message'	=> 'Unexpected error'
		], 500);
	}

	function newBundle(Request $request) {
		// Aborting if request is not AJAX
		abort_if(! $request->ajax(), 403);

		if (Auth::isLogged()) {
			$user = Auth::getLoggedUserDetails();
		}

		try {
			$bundle = new Bundle([
				'user_username'	=> $user->username ?? null,
				'created_at'	=> time(),
				'completed' 	=> false,
				'expiry'		=> config('sharing.default-expiry', 86400),
				'expires_at'	=> null,
				'password'  	=> null,
				'slug'			=> substr(sha1(uniqid('slug_', true)), 0, rand(35, 40)),
				'owner_token'	=> substr(sha1(uniqid('preview_', true)), 0, 15),
				'preview_token'	=> substr(sha1(uniqid('preview_', true)), 0, 15),
				'fullsize'		=> 0,
				'title'			=> null,
				'description'	=> null,
				'max_downloads'	=> 0,
				'downloads'		=> 0
			]);
			$bundle->save();

			return response()->json([
				'result'	=> true,
				'redirect'	=> route('upload.create.show', ['bundle' => $bundle->slug]),
				'bundle'	=> new BundleResource($bundle)
			]);
		}
		catch (Exception $e) {
			return response()->json([
				'result'	=> false,
				'message'	=> $e->getMessage()
			], 500);
		}
	}

	public function logout(Request $request) {
		Auth::logout();
		return redirect()->route('homepage');
	}
}

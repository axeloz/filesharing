<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\Upload;
use App\Helpers\User;
use Illuminate\Support\Facades\Storage;

class UploadAccess
{
	/**
 	* Handle an incoming request.
 	*
 	* @param  \Illuminate\Http\Request  $request
 	* @param  \Closure  $next
 	* @return mixed
 	*/
	public function handle(Request $request, Closure $next): Response
	{
		// Checking IP based access
		if (Upload::canUpload($request->ip()) === true) {
			return $next($request);
		}

		// Checking credentials auth
		if (User::isLogged()) {
			return $next($request);
		}

		// Fallback, authentication required
		if ($request->ajax()) {
			abort(401);
		}
		else {
			return response()->view('login');
		}
	}
}

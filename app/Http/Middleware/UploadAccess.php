<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\Upload;

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
		if ($request->session()->missing('authenticated') && Upload::canUpload($request->ip()) !== true) {
			//return redirect('login');
			if ($request->ajax()) {
				abort(401);
			}
			else {
				return response()->view('login');
			}
		}

		return $next($request);
	}
}

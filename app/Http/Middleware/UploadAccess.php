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
		if (Upload::canUpload($request->ip()) !== true) {
			abort(401);
		}

		return $next($request);
	}
}

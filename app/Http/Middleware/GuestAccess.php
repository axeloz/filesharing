<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\Upload;

class GuestAccess
{
	/**
	 * Handle an incoming request.
	 *
	 * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
	 */
	public function handle(Request $request, Closure $next): Response
	{
		// Aborting if Bundle ID is not present
		abort_if(empty($request->route()->parameter('bundle')), 403);

		abort_if(empty($request->auth), 403);

		// Getting metadata
		$metadata = Upload::getMetadata($request->route()->parameter('bundle'));

		// Aborting if metadata are empty
		abort_if(empty($metadata), 404);

		// Aborting if auth_token is different from URL param
		abort_if($metadata['preview_token'] !== $request->auth, 403);

		// Checking bundle expiration
		abort_if($metadata['expires_at'] < time(), 404);

		// If there is no file into the bundle (should never happen but ...)
		abort_if(count($metadata['files']) == 0, 404);

		abort_if(($metadata['max_downloads'] ?? 0) > 0 && $metadata['downloads'] >= $metadata['max_downloads'], 404);

		return $next($request);
	}
}

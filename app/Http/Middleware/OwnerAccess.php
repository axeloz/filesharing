<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\Upload;

class OwnerAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
		// Aborting if request is not AJAX
		abort_if(! $request->ajax(), 401);

		// Aborting if Bundle ID is not present
		abort_if(empty($request->route()->parameter('bundle')), 401);

		// Aborting if auth is not present
		$auth = null;
		if (! empty($request->header('X-Upload-Auth'))) {
			$auth = $request->header('X-Upload-Auth');
		}
		else if (! empty($request->auth)) {
			$auth = $request->auth;
		}
		abort_if(empty($auth), 401);

		// Getting metadata
		$metadata = Upload::getMetadata($request->route()->parameter('bundle'));

		// Aborting if metadata are empty
		abort_if(empty($metadata), 404);

		// Aborting if auth_token is different from URL param
		abort_if($metadata['owner_token'] !== $auth, 401);

        return $next($request);
    }
}

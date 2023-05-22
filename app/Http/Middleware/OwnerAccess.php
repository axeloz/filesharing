<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\Upload;
use App\Models\Bundle;

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
		abort_if(! $request->ajax(), 403);

		// Aborting if Bundle ID is not present
		abort_if(empty($request->route()->parameter('bundle')), 403);
		$bundle = $request->route()->parameters()['bundle'];
		abort_if(! is_a($bundle, Bundle::class), 404);

		// Aborting if auth is not present
		$auth = null;
		if (! empty($request->header('X-Upload-Auth'))) {
			$auth = $request->header('X-Upload-Auth');
		}
		else if (! empty($request->auth)) {
			$auth = $request->auth;
		}
		// Aborting if no auth token provided
		abort_if(empty($auth), 403);

		// Aborting if owner token is wrong
		abort_if($bundle->owner_token !== $auth, 403);

        return $next($request);
    }
}

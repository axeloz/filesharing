<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Helpers\Upload;
use App\Models\Bundle;
use Carbon\Carbon;

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
		abort_if(empty($request->route()->parameter('bundle')), 404);
		$bundle = $request->route()->parameters()['bundle'];
		abort_if(! is_a($bundle, Bundle::class), 404);

		// Aborting if Auth token is not provided
		abort_if(empty($request->auth), 403);

		// Aborting if auth_token is different from URL param
		abort_if($bundle->preview_token !== $request->auth, 403);

		// Aborting if bundle expired
		if (! empty($bundle->expires_at)) {
			abort_if($bundle->expires_at->isBefore(Carbon::now()), 404);
		}

		// Aborting if max download is reached
		abort_if( ($bundle->max_downloads ?? 0) > 0 && $bundle->downloads >= $bundle->max_downloads, 404);

		// Else resuming
		return $next($request);
	}
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\App;

class Localisation
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
		$locales = $request->header('accept-language');
		if (! empty($locales)) {
			if (preg_match_all('~([a-z]{2})[-|_]?~', $locales, $matches) && ! empty($matches[1])) {
				$locales = array_unique($matches[1]);

				foreach ($locales as $l) {
					if (in_array($l, config('app.supported_locales'))) {
						App::setLocale($l);
						break;
					}
				}
			}
		}
        return $next($request);
    }
}

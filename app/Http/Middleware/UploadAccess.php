<?php

namespace app\Http\Middleware;

use Closure;
use Upload;

class UploadAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Upload::canUpload($request->ip()) !== true) {
            return redirect()->route('homepage');
        }
        return $next($request);
    }
}

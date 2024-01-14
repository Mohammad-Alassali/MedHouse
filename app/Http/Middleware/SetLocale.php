<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userLocal = 'en';
        if (\Auth::user()) {
            $userLocal = \Auth::user()['lang'];
        }
        if (in_array($userLocal, config('app.locales'))) {
            app()->setLocale($userLocal);
        }
        return $next($request);
    }
}

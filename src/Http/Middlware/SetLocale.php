<?php

namespace EhsanNosair\Chativel\Http\Middlware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language', config('chativel.default_language', 'en'));
        
        if (in_array($locale, config('chativel.languages', []))) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}

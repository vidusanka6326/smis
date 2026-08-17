<?php

namespace App\Http\Middleware;

use App\Enums\AppLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Apply the session or account locale for this request.
     *
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = AppLocale::tryFrom((string) $request->session()->get('locale'))
            ?? AppLocale::tryFrom((string) $request->user()?->locale)
            ?? AppLocale::tryFrom((string) config('app.locale'))
            ?? AppLocale::English;

        app()->setLocale($locale->value);
        Carbon::setLocale($locale->value);

        return $next($request);
    }
}

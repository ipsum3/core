<?php

namespace Ipsum\Core\app\Http\Middleware;

use Closure;
use deepskylog\LaravelGettext\Facades\LaravelGettext;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
    public function handle($request, Closure $next)
    {
        // Traitement page d'accueil
        if ($request->route('locale') === null) {
            return redirect('/'.config('ipsum.translate.default_locale'));
        }

        URL::defaults(['locale' => $request->route('locale')]);

        app()->setLocale($request->route('locale'));
        LaravelGettext::setDomain('messages');
        LaravelGettext::setLocale(config('ipsum.translate.locales.'.$request->route('locale').'.gettext'));

        foreach (config('ipsum.translate.locales.'.$request->route('locale').'.setLocale') as $category => $locale) {
            setlocale(constant($category), $locale);
        }

        // remove the locale parameter so we dont have to include it in all controller methods.
        $request->route()->forgetParameter('locale');

        return $next($request);
    }
}

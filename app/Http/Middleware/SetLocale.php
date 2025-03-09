<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = DB::table('lang')
            ->where('short', App::getLocale())
            ->value('locale');

        App::setLocale($request->route('locale'));
        setlocale(LC_COLLATE, $locale . '.utf8');
        setlocale(LC_CTYPE, $locale . '.utf8');

        app()->instance('id_lang', config('resucilex.lang')[App::getLocale()]);

        app()->instance('absoluteUriWithoutQuery',
            $request->getScheme() . '://' .
                $request->getHttpHost() . strtok($request->getRequestUri(), '?')
        );

        app()->instance('absoluteBasePath',
            $request->getScheme() . '://' .
                $request->getHttpHost() . $request->getBasePath()
        );

        if (array_key_exists('locale', $request->route()->parameters())) {
            $currentRouteWithAllLocales = [];
            $locales = array_column(config('resucilex.lang'), 'short');

            foreach ($locales as $locale) {
                $routeParams = $request->route()->parameters();
                $routeParams['locale'] = $locale;

                $currentRouteWithAllLocales[$locale] = URL::route(
                    Route::currentRouteName(),
                    $routeParams,
                    true  // absolute URL
                );
            }

            app()->instance('currentRouteWithAllLocales', $currentRouteWithAllLocales);
        }

        return $next($request);
    }
}

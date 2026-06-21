<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Si el usuario cambia el idioma manualmente por la URL (?lang=en)
        if ($request->has('lang')) {
            $lang = $request->get('lang');
            Session::put('lang', $lang);
            
            // Si está logueado, actualizamos su preferencia en la base de datos para siempre
            if (auth()->check()) {
                auth()->user()->update(['preferred_lang' => $lang]);
            }
        }

        // 2. Determinar qué idioma activar (Prioridad: Sesión -> BD del Usuario -> Forzado a Español Core)
        $locale = Session::get('lang', function() {
            return auth()->check() ? auth()->user()->preferred_lang : 'es'; // 👈 Candado definitivo
        });

        // 3. Le decimos a Laravel que traduzca la aplicación a ese idioma para esta petición
        App::setLocale($locale);

        return $next($request);
    }
}
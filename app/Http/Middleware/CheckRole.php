<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        // Si el usuario no ha iniciado sesión, o su rol no coincide con el permitido, lo botamos al login
        if (!auth()->check() || auth()->user()->role !== $role) {
            return redirect()->route('login')->with('error', 'No tienes permisos para acceder a esta sección.');
        }

        return $next($request);
    }
}
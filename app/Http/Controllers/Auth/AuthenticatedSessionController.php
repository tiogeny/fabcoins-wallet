<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Procesa el cambio obligatorio de clave inicial para cuentas invitadas.
     */
    public function completeOnboarding(\Illuminate\Http\Request $request)
    {
        // Creamos un validador manual para controlar el rechazo sin perder el estado
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'temp_user_id' => 'required|integer',
            'new_password' => 'required|string|min:8', 
        ], [
            'new_password.min' => __('messages.error_password_min'),
            'new_password.required' => __('messages.error_password_required'),
        ]);

        // 🛑 SI FALLA: Congelamos la pantalla de bienvenida y le inyectamos el mensaje en rojo de forma nativa
        if ($validator->fails()) {
            return view('auth.login', [
                'require_onboarding' => true,
                'temp_user_id' => $request->input('temp_user_id')
            ])->withErrors($validator);
        }

        // 1. Verificar usando Query Builder directo si el usuario existe y requiere el cambio
        $usuarioExiste = \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $request->input('temp_user_id'))
            ->where('force_password_change', 1)
            ->exists();

        if (!$usuarioExiste) {
            return redirect()->route('login')->withErrors(['email' => 'El proceso de activación no es válido o ya fue completado.']);
        }

        // 2. Forzar la actualización directa en la base de datos (Evita problemas de $fillable)
        \Illuminate\Support\Facades\DB::table('users')
            ->where('id', $request->input('temp_user_id'))
            ->update([
                'password' => \Illuminate\Support\Facades\Hash::make($request->input('new_password')),
                'force_password_change' => 0 // 🔓 Cuenta liberada oficialmente
            ]);

        // 3. Iniciar y regenerar su sesión limpia PRIMERO (Evita que Laravel tumbe el token de acceso)
        $request->session()->regenerate();

        // 4. Loguear al usuario usando su ID de forma segura inmediatamente después
        \Illuminate\Support\Facades\Auth::loginUsingId($request->input('temp_user_id'));

        // 5. Redirigir al distribuidor de tráfico central
        return redirect()->route('dashboard');
    }
}

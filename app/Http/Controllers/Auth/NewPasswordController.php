<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Auth; // 🚀 IMPORTAMOS EL MOTOR DE SESIONES
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Services\MailService; // 📩 Importamos tu suite de correos

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', ['request' => $request]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                // 🔒 ENVIAR CORREO DE CONFIRMACIÓN AUTOMÁTICO
                MailService::notificarCambioPassword($user->email, $user->name);

                // 🔒 INYECTAMOS AUTO-LOGIN: Firma digitalmente la sesión del usuario de inmediato
                Auth::login($user);
            }
        );

        // 🎯 REDIRECCIÓN INTELIGENTE: Si todo sale bien, lo mandamos al orquestador de /dashboard
        // para que detecte su rol (Lab, Creator o Admin) y lo lleve a su panel directo sin escalas.
        return $status == Password::PASSWORD_RESET
                    ? redirect()->route('dashboard')->with('msg', 'password_reset_ok')
                    : back()->withInput($request->only('email'))
                        ->withErrors(['email' => __($status)]);
    }
}

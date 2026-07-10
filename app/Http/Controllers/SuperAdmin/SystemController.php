<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\MailService; // 🚀 IMPORTACIÓN DE NUESTRO SERVICIO DE MAILING
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\URL;

class SystemController extends Controller
{
    public function createLab(Request $request)
    {
        // Antigravity Approved: Se elimina el campo 'password' de la validación obligatoria
        $request->validate(['name' => 'required|string', 'email' => 'required|email']);

        $name = trim($request->input('name'));
        $email = trim($request->input('email'));
        // Se genera un string robusto y aleatorio de 32 caracteres que nunca se enviará por mail
        $passwordOculta = Str::random(32);
        $lab_lang = $request->input('lab_lang', 'es');

        $existe = DB::table('users')->where('email', $email)->exists();
        if ($existe) {
            return redirect()->route('superadmin.dashboard')->with('error', "El correo institucional ya se encuentra registrado.");
        }

        $baseSlug = Str::slug($name);
        $slug = $baseSlug;

        if (DB::table('users')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . rand(100, 999);
        }

        $avatar = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=2ecc71&color=fff";

        $newLabId = DB::table('users')->insertGetId([
            'name' => $name, 'email' => $email, 'password' => Hash::make($passwordOculta),
            'role' => 'lab', 'avatar_url' => $avatar, 'slug' => $slug,
            'force_password_change' => 1, 'preferred_lang' => $lab_lang, 'created_at' => now()
        ]);

        // Genera el enlace firmado seguro apuntando a nuestra nueva ruta GET de verificación
        $urlSeguraOnboarding = URL::temporarySignedRoute(
            'onboarding.verify', 
            now()->addDays(7), // 🎯 Rango extendido y seguro de 7 días (Aprobado por Antigravity)
            ['user' => $newLabId] 
        );

        // 📨 TRIGGER: Enviamos el enlace de firma única al correo
        MailService::bienvenidaLab($email, $name, $urlSeguraOnboarding);

        return redirect()->route('superadmin.dashboard')->with('msg', 'lab_ok');
    }

    public function updatePolicy(Request $request)
    {
        // Escudo Monetario (VULN-NEW-05): Validar rango para evitar inflación o errores tipográficos
        $request->validate([
            'nuevo_pct' => 'required|numeric|min:1|max:100',
        ]);

        DB::table('global_settings')
            ->where('setting_key', 'tokenization_pct')
            ->update(['setting_value' => floatval($request->input('nuevo_pct'))]);

        return redirect()->route('superadmin.dashboard')->with('msg', 'pct_updated');
    }
}
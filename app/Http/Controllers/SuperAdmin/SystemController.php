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
        $request->validate(['name' => 'required|string', 'email' => 'required|email', 'password' => 'required']);

        $name = trim($request->input('name'));
        $email = trim($request->input('email'));
        $password = $request->input('password');
        $lab_lang = $request->input('lab_lang', 'es');

        $existe = DB::table('users')->where('email', $email)->exists();
        if ($existe) {
            return redirect()->route('superadmin.dashboard')->with('error', "El correo institucional ya se encuentra registrado.");
        }

        // 🧠 LÓGICA DE SLUG INTELIGENTE CONDICIONAL
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;

        // 🔍 Si el slug limpio ya está tomado por otro Lab, recién ahí concatenamos el número
        if (DB::table('users')->where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . rand(100, 999);
        }

        $avatar = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=2ecc71&color=fff";

        // Cambiado a insertGetId para capturar y almacenar el ID real del nuevo Lab
        $newLabId = DB::table('users')->insertGetId([
            'name' => $name, 'email' => $email, 'password' => Hash::make($password),
            'role' => 'lab', 'avatar_url' => $avatar, 'slug' => $slug,
            'force_password_change' => 1, 'preferred_lang' => $lab_lang, 'created_at' => now()
        ]);

        // Genera un enlace firmado único para este usuario que expira en 1 día
        $urlOnboarding = URL::temporarySignedRoute(
            'onboarding.complete', 
            now()->addDays(1), 
            ['user' => $newLabId] // Corregido: Usamos el ID real capturado
        );

        // 📨 TRIGGER: Despacha la plantilla de bienvenida oficial bilingüe
        MailService::bienvenidaLab($email, $name, $urlOnboarding);

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
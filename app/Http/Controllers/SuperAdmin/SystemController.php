<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

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

        // Slugificación limpia nativa de Laravel
        $slug = Str::slug($name) . '-' . rand(100, 999);
        $avatar = "https://ui-avatars.com/api/?name=" . urlencode($name) . "&background=2ecc71&color=fff";

        DB::table('users')->insert([
            'name' => $name, 'email' => $email, 'password' => Hash::make($password),
            'role' => 'lab', 'avatar_url' => $avatar, 'slug' => $slug,
            'force_password_change' => 1, 'preferred_lang' => $lab_lang, 'created_at' => now()
        ]);

        return redirect()->route('superadmin.dashboard')->with('msg', 'lab_ok');
    }

    public function updatePolicy(Request $request)
    {
        DB::table('global_settings')
            ->where('setting_key', 'tokenization_pct')
            ->update(['setting_value' => floatval($request->input('nuevo_pct'))]);

        return redirect()->route('superadmin.dashboard')->with('msg', 'pct_updated');
    }
}
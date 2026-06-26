<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $creator = auth()->user();

        // 1. Reglas de Validación con Nomenclatura Unificada
        $request->validate([
            'bio'              => 'required|string|max:1000',
            'social_linkedin'  => 'nullable|url|max:255',
            'social_github'    => 'nullable|url|max:255',
            'social_portfolio' => 'nullable|url|max:255',
            'social_instagram' => 'nullable|url|max:255',
            'social_fabacademy'=> 'nullable|url|max:255',
            'city'             => 'nullable|string|max:100',
            'country'          => 'nullable|string|max:100',
            'skills'           => 'nullable|array'
        ]);

        // 2. Query Builder Directo: Guarda toda la información de golpe sin trabas de $fillable
        DB::table('users')->where('id', $creator->id)->update([
            'bio'               => $request->input('bio'),
            'social_linkedin'   => $request->input('social_linkedin'),
            'social_github'     => $request->input('social_github'),
            'social_portfolio'  => $request->input('social_portfolio'),
            'social_instagram'  => $request->input('social_instagram'),
            'social_fabacademy' => $request->input('social_fabacademy'),
            'city'              => $request->input('city'),
            'country'           => $request->input('country'),
            'updated_at'        => now()
        ]);

        // 3. Sincronización limpia de Especializaciones Técnicas (Habilidades)
        DB::table('user_skills')->where('user_id', $creator->id)->delete();
        $skillsElegidas = $request->input('skills', []);

        if (!empty($skillsElegidas)) {
            foreach ($skillsElegidas as $skillId) {
                DB::table('user_skills')->insert([
                    'user_id'  => $creator->id,
                    'skill_id' => intval($skillId)
                ]);
            }
        }

        return redirect()->route('creator.dashboard')->with('msg', 'profile_updated');
    }

    public function security(Request $request)
    {
        $creator = auth()->user();
        
        if (!Hash::check($request->input('current_password'), $creator->password)) {
            return redirect()->back()->withErrors(['current_password' => __('messages.validation_password_mismatch')]);
        }

        $request->validate([
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        DB::table('users')->where('id', $creator->id)->update([
            'password'   => Hash::make($request->input('new_password')),
            'updated_at' => now()
        ]);

        return redirect()->route('creator.dashboard')->with('msg', 'password_updated');
    }
}
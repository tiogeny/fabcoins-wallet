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

        // 1. Añade los nuevos campos a la validación
        $request->validate([
            'bio'              => 'required|string|max:1000',
            'social_linkedin'  => 'nullable|url|max:255',
            'social_github'    => 'nullable|url|max:255',
            'social_portfolio' => 'nullable|url|max:255',
            'instagram_url'    => 'nullable|url|max:255',   // 👈 NUEVO
            'fab_academy_url'  => 'nullable|url|max:255',   // 👈 NUEVO
            'city'             => 'nullable|string|max:100', // 👈 NUEVO
            'country'          => 'nullable|string|max:100', // 👈 NUEVO
            'skills'           => 'nullable|array'
        ]);

        // 2. Añade los nuevos campos a la actualización del modelo
        $creator->update([
            'bio'              => $request->input('bio'),
            'social_linkedin'  => $request->input('social_linkedin'),
            'social_github'    => $request->input('social_github'),
            'social_portfolio' => $request->input('social_portfolio'),
            'instagram_url'    => $request->input('instagram_url'),     // 👈 NUEVO
            'fab_academy_url'  => $request->input('fab_academy_url'),   // 👈 NUEVO
            'city'             => $request->input('city'),              // 👈 NUEVO
            'country'          => $request->input('country'),           // 👈 NUEVO
        ]);

        // 3. Sincronización atómica de Especializaciones Técnicas
        DB::table('user_skills')->where('user_id', $creator->id)->delete();
        $skillsElegidas = $request->input('skills', []);

        if (!empty($skillsElegidas)) {
            foreach ($skillsElegidas as $skillId) {
                DB::table('user_skills')->insert([
                    'user_id'  => $creator->id,
                    'skill_id' => $skillId
                ]);
            }
        }

        return redirect()->route('creator.dashboard')->with('msg', 'profile_updated');
    }

    public function security(Request $request)
    {
        $creator = auth()->user();
        if (!Hash::check($request->input('current_password'), $creator->password)) {
            return redirect()->route('creator.dashboard')->with('error', __('messages.err_current_pass_wrong'));
        }

        $creator->update(['password' => Hash::make($request->input('new_password'))]);
        return redirect()->route('creator.dashboard')->with('msg', 'pass_ok');
    }
}
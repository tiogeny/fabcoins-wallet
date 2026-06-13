<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $creator = auth()->user();

        // 1. Persistencia de datos biográficos y profesionales
        $creator->update([
            'bio'               => strip_tags($request->input('bio'), '<b><strong><i><em><u><ul><li><ol><br><p>'),
            'address'           => trim($request->input('address')),
            'social_fabacademy' => trim($request->input('social_fabacademy')),
            'social_linkedin'   => trim($request->input('social_linkedin')),
            'social_github'     => trim($request->input('social_github')),
            'social_portfolio'  => trim($request->input('social_portfolio')),
            'social_instagram'  => trim($request->input('social_instagram')),
        ]);

        // 2. Sincronización atómica de Especializaciones Técnicas
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
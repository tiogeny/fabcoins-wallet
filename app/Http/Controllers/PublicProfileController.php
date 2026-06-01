<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicProfileController extends Controller
{
    /**
     * Renderiza el portafolio unificado y bilingüe de cualquier miembro de la red
     */
    public function show($slugOrId)
    {
        // 1. Resolver la identidad del usuario por slug o ID
        $user = User::where('slug', $slugOrId)->orWhere('id', $slugOrId)->firstOrFail();

        $miRol = auth()->check() ? auth()->user()->role : 'guest';
        $miId = auth()->check() ? auth()->id() : null;

        // 2. LÓGICA DE RECLUTAMIENTO: Si el visitante es un Lab y ve a un Maker
        $misMisionesAbiertas = [];
        if ($miRol === 'lab' && $miId !== $user->id && $user->role === 'maker') {
            $misMisionesAbiertas = DB::table('missions')
                ->where('lab_id', $miId)
                ->where('status', 'open')
                ->whereRaw('spots_total > spots_filled')
                ->whereNull('target_maker_id')
                ->select('id', 'title')
                ->get();
        }

        // 3. SÚPER CONSULTA UNIFICADA: Reseñas + Misiones + Mercado + Habilidades
        $historialUnificado = DB::select("
            SELECT 
                r.id as review_id, r.rating, r.comment, r.created_at, r.context_type, 
                u.name as reviewer_name, u.slug as reviewer_slug, u.id as reviewer_id,
                CASE 
                    WHEN r.context_type = 'mission' THEN m.title 
                    WHEN r.context_type = 'market' THEN la.custom_name 
                END as context_title,
                CASE 
                    WHEN r.context_type = 'mission' THEN m.description
                    WHEN r.context_type = 'market' THEN NULL
                END as context_desc,
                CASE 
                    WHEN r.context_type = 'mission' THEN m.reward_fc 
                    WHEN r.context_type = 'market' THEN o.total_fc 
                END as context_fc,
                (
                    SELECT GROUP_CONCAT(CONCAT(sc.name, ':', sc.type) SEPARATOR '|')
                    FROM skill_endorsements se
                    JOIN skills_catalog sc ON se.skill_id = sc.id
                    WHERE se.review_id = r.id
                ) as specific_skills
            FROM reviews r 
            JOIN users u ON r.reviewer_id = u.id 
            LEFT JOIN missions m ON r.context_id = m.id AND r.context_type = 'mission'
            LEFT JOIN orders o ON r.context_id = o.id AND r.context_type = 'market'
            LEFT JOIN lab_assets la ON o.asset_id = la.id
            WHERE r.reviewee_id = ? 
            ORDER BY r.created_at DESC
        ", [$user->id]);

        // 4. Carga de Paneles Laterales según Rol
        $misHabilidades = [];
        $activosLab = [];

        if ($user->role === 'maker') {
            $misHabilidades = DB::select("
                SELECT s.id, s.name, s.type, COUNT(e.id) as endorsements_count
                FROM user_skills us
                JOIN skills_catalog s ON us.skill_id = s.id
                LEFT JOIN skill_endorsements e ON e.skill_id = s.id AND e.maker_id = us.user_id
                WHERE us.user_id = ?
                GROUP BY s.id, s.name, s.type
                ORDER BY endorsements_count DESC
            ", [$user->id]);
        } else {
            $activosLab = DB::table('lab_assets')
                ->where('lab_id', $user->id)
                ->where('status', 'active')
                ->select('custom_name', 'asset_type', 'set_price_fc')
                ->get();
        }

        return view('profile.show', compact(
            'user', 'miRol', 'miId', 'misMisionesAbiertas', 
            'historialUnificado', 'misHabilidades', 'activosLab'
        ));
    }

    /**
     * Procesa y despacha la invitación formal de reclutamiento
     */
    public function invite(Request $request, $slugOrId)
    {
        $user = User::where('slug', $slugOrId)->orWhere('id', $slugOrId)->firstOrFail();
        $request->validate(['mision_id' => 'required|integer']);
        
        $miId = auth()->id();
        $misionId = $request->input('mision_id');

        $mision = DB::table('missions')
            ->where('id', $misionId)
            ->where('lab_id', $miId)
            ->where('status', 'open')
            ->first();

        if ($mision) {
            // Registrar notificación en la campanita
            DB::table('notifications')->insert([
                'user_id' => $user->id,
                'message' => "🎯 " . auth()->user()->name . " te ha invitado a la misión: " . $mision->title,
                'type' => 'info',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // En un paso posterior amarraremos la función 'notificar_invitacion_mision' para los correos
        }

        return redirect()->route('public.profile', $slugOrId)->with('msg', 'invite_ok');
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PublicProfileController extends Controller
{
    public function show($slugOrId)
    {
        // Buscamos al usuario por su ID o por su SLUG corporativo
        $user = User::where('slug', $slugOrId)->orWhere('id', $slugOrId)->firstOrFail();
        
        // 1. Historial unificado de reseñas (Aplica tanto a Creadores como a Labs)
        $historialUnificado = DB::table('reviews')
            ->join('users', 'reviews.reviewer_id', '=', 'users.id')
            ->leftJoin('missions', function($join) {
                $join->on('reviews.context_id', '=', 'missions.id')->where('reviews.context_type', '=', 'mission');
            })
            ->leftJoin('orders', function($join) {
                $join->on('reviews.context_id', '=', 'orders.id')->where('reviews.context_type', '=', 'market');
            })
            ->leftJoin('lab_assets', 'orders.asset_id', '=', 'lab_assets.id')
            ->where('reviews.reviewee_id', $user->id)
            ->select(
                'reviews.*', 'users.name as reviewer_name', 'users.slug as reviewer_slug',
                DB::raw("CASE WHEN reviews.context_type = 'mission' THEN missions.title ELSE lab_assets.custom_name END as context_title"),
                DB::raw("CASE WHEN reviews.context_type = 'mission' THEN missions.reward_fc ELSE orders.total_fc END as context_fc")
            )
            ->orderBy('reviews.created_at', 'desc')
            ->get();

        // Inicializamos colecciones vacías para prevenir fallas por variables no definidas
        $misHabilidades = collect();
        $misMisionesAbiertas = collect();
        $misActivos = collect();
        $misMisionesNodo = collect();

        // 2. Carga Dinámica Bifurcada según la naturaleza del Rol
        if ($user->role === 'creator') {
            try {
                $misHabilidades = DB::table('user_skills')
                    ->join('skills', 'user_skills.skill_id', '=', 'skills.id')
                    ->where('user_skills.user_id', $user->id)
                    ->select('skills.id', 'skills.name_es', 'skills.name_en', 'skills.type')
                    ->get();

                $endorsementsCounts = [];
                foreach ($historialUnificado as $r) {
                    if (!empty($r->endorsed_skills)) {
                        $chunks = explode(',', $r->endorsed_skills);
                        foreach ($chunks as $chunk) {
                            $parts = explode('|', $chunk);
                            $skillNameFromReview = trim($parts[0] ?? '');
                            if ($skillNameFromReview !== '') {
                                $endorsementsCounts[$skillNameFromReview] = ($endorsementsCounts[$skillNameFromReview] ?? 0) + 1;
                            }
                        }
                    }
                }

                foreach ($misHabilidades as $sk) {
                    $countEs = $endorsementsCounts[trim($sk->name_es)] ?? 0;
                    $countEn = $endorsementsCounts[trim($sk->name_en)] ?? 0;
                    $sk->endorsements_count = max($countEs, $countEn);
                }

            } catch (\Exception $e) {
                $misHabilidades = collect();
            }

            // Si quien visita es un Lab, traemos sus misiones abiertas para poder invitarlo
            if (auth()->check() && auth()->user()->role === 'lab') {
                $misMisionesAbiertas = DB::table('missions')
                    ->where('lab_id', auth()->id())
                    ->where('status', 'open')
                    ->whereRaw('spots_total > spots_filled')
                    ->get();
            }

        } elseif ($user->role === 'lab') {
            // 🏪 INFRAESTRUCTURA NODO: Activos reales activos que posean capacidad disponible mayor a cero
            $misActivos = DB::table('lab_assets')
                ->join('global_catalog', 'lab_assets.catalog_id', '=', 'global_catalog.id')
                ->where('lab_assets.lab_id', $user->id)
                ->where('lab_assets.status', 'active')
                ->whereRaw('(lab_assets.useful_life_hours - lab_assets.consumed_hours) > 0')
                ->select('lab_assets.*', 'global_catalog.generic_name as display_name')
                ->get();

            // 🎯 RADAR DE MISIONES NODO: Desafíos vigentes publicados exclusivamente por este laboratorio
            $misMisionesNodo = DB::table('missions')
                ->where('lab_id', $user->id)
                ->where('status', 'open')
                ->whereRaw('spots_total > spots_filled')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('public.profile', compact(
            'user', 'misHabilidades', 'historialUnificado', 
            'misMisionesAbiertas', 'misActivos', 'misMisionesNodo'
        ));
    }

    public function invite(Request $request, $slugOrId)
    {
        $request->validate([
            'creator_id' => 'required|exists:users,id',
            'mission_id' => 'required|exists:missions,id',
        ]);

        $mission = DB::table('missions')->where('id', $request->mission_id)->where('lab_id', auth()->id())->first();
        if (!$mission) return back()->with('error', 'Error de permisos de misión.');

        DB::table('mission_applications')->updateOrInsert(
            ['mission_id' => $mission->id, 'creator_id' => $request->creator_id],
            ['status' => 'invited', 'created_at' => now(), 'updated_at' => now()]
        );

        return back()->with('msg', 'invite_sent_success');
    }
}
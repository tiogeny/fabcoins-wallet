<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class PublicProfileController extends Controller
{
    public function show($slugOrId)
    {
        // Buscamos al usuario por su ID o por su SLUG
        $user = User::where('slug', $slugOrId)->orWhere('id', $slugOrId)->firstOrFail();
        
        // Obtenemos las habilidades (Solo si es Creador)
        $misHabilidades = collect();
        if ($user->role === 'creator') {
            try {
                $misHabilidades = DB::table('user_skills')
                    ->join('skills_catalog', 'user_skills.skill_id', '=', 'skills_catalog.id')
                    ->where('user_skills.user_id', $user->id)
                    ->select('skills_catalog.*')
                    ->get();
            } catch (\Exception $e) {
                // Evitamos error si la tabla no existe
                $misHabilidades = collect();
            }
        }

        // Obtenemos las reseñas e historial unificado
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

        // Si quien visita es un Lab, traemos sus misiones abiertas para poder invitarlo
        $misMisionesAbiertas = collect();
        if (auth()->check() && auth()->user()->role === 'lab' && $user->role === 'creator') {
            $misMisionesAbiertas = DB::table('missions')
                ->where('lab_id', auth()->id())
                ->where('status', 'open')
                ->whereRaw('spots_total > spots_filled')
                ->get();
        }

        // Enviamos los datos a la vista
        return view('public.profile', compact('user', 'misHabilidades', 'historialUnificado', 'misMisionesAbiertas'));
    }

    public function invite(Request $request)
    {
        // Lógica de invitación a misiones (lo haremos después si es necesario)
        return redirect()->back();
    }
}
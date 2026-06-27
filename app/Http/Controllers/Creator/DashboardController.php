<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Carga el estado de cuenta y sincroniza los KPIs del Creator
     */
    public function index()
    {
        $creator = auth()->user();

        // Balance de Billetera Contable Real
        $querySaldo = DB::select("SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as saldo FROM transactions WHERE user_id = ?", [$creator->id]);
        $saldoTotal = $querySaldo[0]->saldo ?? 0;

        // Contador de Misiones Evaluadas y Completadas con Éxito
        $misionesCompletadasKpi = DB::table('mission_applications')
            ->where('creator_id', $creator->id)
            ->where('is_reviewed', 1)
            ->count();

        // Misiones disponibles en la red pública
        $misionesAbiertas = DB::table('missions as m')
            ->join('users as u', 'm.lab_id', '=', 'u.id')
            ->where('m.status', 'open')
            ->where(function($query) use ($creator) {
                $query->whereNull('m.target_creator_id')->orWhere('m.target_creator_id', $creator->id);
            })
            ->whereNotIn('m.id', function($q) use ($creator) {
                $q->select('mission_id')->from('mission_applications')->where('creator_id', $creator->id);
            })
            ->select('m.*', 'u.name as lab_name', 'u.avatar_url', 'u.slug as lab_slug')
            ->orderBy('m.created_at', 'desc')
            ->get();

        // Estado de Postulaciones Laborales del Creator (Detecta reseñas dinámicamente)
        $misPostulaciones = DB::table('mission_applications as ma')
            ->join('missions as m', 'ma.mission_id', '=', 'm.id')
            ->join('users as u', 'm.lab_id', '=', 'u.id')
            ->where('ma.creator_id', $creator->id)
            ->select(
                'ma.*', 
                'm.title', 
                'm.reward_fc', 
                'm.status as mission_status', 
                'm.deadline', 
                'm.lab_id', 
                'm.target_creator_id', 
                'u.name as lab_name', 
                'u.slug as lab_slug',
                // 🎯 SUBCONSULTA CONTABLE: Cuenta si ya existe una review de esta misión hecha por el creador
                DB::raw("(SELECT COUNT(*) FROM reviews WHERE reviewer_id = ma.creator_id AND context_type = 'mission' AND context_id = ma.mission_id) as ya_calificado")
            )
            ->orderBy('ma.created_at', 'desc')
            ->get();

        // Libro de Reservas de Maquinaria en el Mercado
        $misReservas = DB::table('orders as o')
            ->join('lab_assets as a', 'o.asset_id', '=', 'a.id')
            ->join('users as u', 'a.lab_id', '=', 'u.id')
            ->where('o.creator_id', $creator->id)
            ->select('o.*', 'a.custom_name', 'u.id as lab_owner_id', 'u.name as lab_name', 'u.email as lab_email', 'u.slug as lab_slug')
            ->orderBy('o.created_at', 'desc')
            ->get();

        // Catálogo de Recursos Disponibles en el Mercado Global
        $recursosMercado = DB::table('lab_assets as la')
            ->join('global_catalog as gc', 'la.catalog_id', '=', 'gc.id')
            ->join('users as u', 'la.lab_id', '=', 'u.id')
            ->where('la.status', 'active')
            ->whereRaw('(la.useful_life_hours - la.consumed_hours) > 0')
            ->select('la.*', 'gc.generic_name as display_name', 'gc.generic_name', 'u.name as lab_name', 'u.slug as lab_slug', 'u.address as lab_address')
            ->get();

        // Historial de Estado de Cuenta Contable
        $misTransacciones = DB::table('transactions')
            ->where('user_id', $creator->id)
            ->orderBy('created_at', 'desc')
            ->limit(50)
            ->get();

        // 🚀 NUEVA CIRUGÍA: Ingresos Históricos Totales (Para el Círculo de la Billetera)
        $totalFCGenerados = DB::table('transactions')
            ->where('user_id', $creator->id)
            ->whereIn('type', ['income', 'mint'])
            ->sum('amount');

        // Estado del Contrato de Crédito ISA Pendiente/Activo
        $creditoActual = DB::table('financing_agreements as c')
            ->join('users as u', 'c.lab_id', '=', 'u.id')
            ->where('c.creator_id', $creator->id)
            ->whereIn('c.status', ['pending', 'active'])
            ->select('c.*', 'u.name as lab_name')
            ->first();

        // Historial de Amortizaciones a través de Sudor de Misiones
        $historialAbonos = DB::table('transactions as t')
            ->join('missions as m', 't.description', '=', DB::raw("CONCAT('Retorno de Crédito Fab (Misión #', m.id, ')')"))
            ->where('m.assigned_creator_id', $creator->id)
            ->select('t.amount', 't.created_at', 'm.title')
            ->orderBy('t.created_at', 'desc')
            ->get();

        // Nodos del Mapa Explorador
        $labsMapa = User::where('role', 'lab')->whereNotNull('latitude')->whereNotNull('longitude')->select('id', 'name', 'slug', 'address', 'latitude', 'longitude', 'avatar_url')->get();
        $labsMapaJson = json_encode($labsMapa);

        // Notificaciones
        $notificaciones = DB::table('notifications')->where('user_id', $creator->id)->latest()->limit(10)->get();
        $unread_count = $notificaciones->where('is_read', false)->count(); 

        // 🚀 CORRECCIÓN MAESTRA: Carga de Habilidades con Soporte Bilingüe Real de la tabla 'skills'
        $userLang = $creator->preferred_lang ?? 'es';
        $columnaNombreSkill = ($userLang === 'en') ? 'name_en' : 'name_es';

        try {
            $catalogoSkills = DB::table('skills')
                ->orderBy('type')
                ->orderBy($columnaNombreSkill)
                ->get();
        } catch (\Exception $e) {
            $catalogoSkills = collect(); // Evita colapsos si la tabla está vacía
        }

        try {
            $misSkillsIds = DB::table('user_skills')->where('user_id', $creator->id)->pluck('skill_id')->toArray();
        } catch (\Exception $e) {
            $misSkillsIds = []; 
        }

        return view('creator.dashboard', compact(
            'creator', 'saldoTotal', 'misionesCompletadasKpi', 'misionesAbiertas', 'misPostulaciones',
            'misReservas', 'recursosMercado', 'misTransacciones', 'creditoActual', 'historialAbonos',
            'labsMapaJson', 'notificaciones', 'unread_count', 'catalogoSkills', 'misSkillsIds', 'totalFCGenerados'
        ));
    }

    public function readNotifications()
    {
        DB::table('notifications')->where('user_id', auth()->id())->update(['is_read' => true]);
        return redirect()->route('creator.dashboard');
    }
}
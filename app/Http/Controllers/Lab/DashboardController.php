<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * 📊 BALANCE FINTECH MACROECONÓMICO EN TIEMPO REAL (REAL-TIME LEDGER)
     */
    public function index()
    {
        $lab = auth()->user();

        // 1. Colecciones Base de Operaciones (Ecosistema Protegido)
        $misActivos = $lab->activos()->orderBy('asset_type')->orderBy('custom_name')->get();
        $misMisiones = $lab->misiones()->latest()->get();
        $misTransacciones = $lab->transacciones()->latest()->limit(50)->get();

        // 2. HUB A: Nivelación Demográfica y Activos unificados bajo 3 Macro-Ejes Strict
        $activosFisicos = $misActivos;
        
        // 🔥 CORRECCIÓN: Cuenta la infraestructura total disponible (tanto Enlistada como Operativa)
        $totalActivosCount = $misActivos->whereIn('status', ['enlisted', 'active'])->count();
        
        // Categoría 1: Máquinas (Hardware Puro)
        $totalMaquinasCount = $misActivos->whereIn('status', ['enlisted', 'active'])->where('asset_type', 'machine')->count();
        
        // 🔥 CORRECCIÓN: Servicios y Talleres se consolidan bajo el tipo 'service'
        $totalServiciosCount = $misActivos->whereIn('status', ['enlisted', 'active'])->where('asset_type', 'service')->count();
        
        // 🔥 CORRECCIÓN: Infraestructura espacial bajo el tipo 'lab'
        $totalLabsConectados = $misActivos->whereIn('status', ['enlisted', 'active'])->where('asset_type', 'lab')->count();

        // 3. HUB B: Libro Contable Automatizado (Fórmulas de Tu Base de Datos Real)
        $totalMinted = $misActivos->sum('generated_fc') ?? 0;
        $dadosDeBajaValor = $misActivos->where('status', 'retired')->sum('generated_fc') ?? 0;

        // Cálculo de Billetera Líquida: Ingresos y Emisiones (+) menos Egresos (-)
        $saldoTotal = DB::table('transactions')
            ->where('user_id', $lab->id)
            ->selectRaw("SUM(CASE WHEN type IN ('income', 'mint') THEN amount ELSE -amount END) as saldo")
            ->value('saldo') ?? 0;

        $enReserva = $saldoTotal; 
        $isFrozen = ($saldoTotal < 0);

        // --- MÓDULO FINANCIERO DE ESCROW VIVO (MISIONES) ---
        $totalEnviadoEscrow = DB::table('transactions')
            ->where('user_id', $lab->id)
            ->where('type', 'escrow')
            ->sum(DB::raw('ABS(amount)')) ?? 0;

        $totalDevueltoEscrow = DB::table('transactions')
            ->where('user_id', $lab->id)
            ->where('type', 'income')
            ->where('description', 'like', 'Devolución de Escrow%')
            ->sum('amount') ?? 0;

        $trabajoTotalRevisado = DB::table('mission_applications')
            ->join('missions', 'mission_applications.mission_id', '=', 'missions.id')
            ->where('missions.lab_id', $lab->id)
            ->where('mission_applications.is_reviewed', 1)
            ->sum('missions.reward_fc') ?? 0;

        $escrowRealMisiones = max(0, $totalEnviadoEscrow - $totalDevueltoEscrow - $trabajoTotalRevisado);

        $totalAmortizado = DB::table('transactions')
            ->where('user_id', $lab->id)
            ->where('type', 'income')
            ->where('description', 'like', 'Retorno de Crédito Fab (Misión #%')
            ->sum('amount') ?? 0;

        $historicoPagadoMisiones = max(0, $trabajoTotalRevisado - $totalAmortizado);
        
        $ofertadosTotal = $escrowRealMisiones + $historicoPagadoMisiones; 
        $ofertadosCongelados = $ofertadosTotal; 
        $totalHistoricoQuemado = DB::table('orders')
            ->join('lab_assets', 'orders.asset_id', '=', 'lab_assets.id')
            ->where('lab_assets.lab_id', $lab->id)
            ->where('orders.status', 'completed')
            ->sum('orders.total_fc') ?? 0;

        // 4. HUB C: Métricas Agrupadas de la Bolsa de Trabajo
        $totalMisionesCount = $misMisiones->count();
        $statsMisiones = [
            'completadas'  => $misMisiones->where('status', 'completed')->count(),
            'en_ejecucion' => $misMisiones->where('status', 'assigned')->count(),
            'abiertas'     => $misMisiones->where('status', 'open')->count(),
            'por_aceptar'  => DB::table('mission_applications')
                                ->join('missions', 'mission_applications.mission_id', '=', 'missions.id')
                                ->where('missions.lab_id', $lab->id)
                                ->where('mission_applications.status', 'pending')
                                ->count(),
        ];

        // 5. Estructuras Secundarias Operativas (Tablas Internas)
        $misReservas = DB::table('orders')
            ->join('users', 'orders.maker_id', '=', 'users.id')
            ->join('lab_assets', 'orders.asset_id', '=', 'lab_assets.id')
            ->where('lab_assets.lab_id', $lab->id)
            ->select('orders.*', 'users.name as maker_name', 'users.slug as maker_slug', 'lab_assets.custom_name')
            ->orderBy('orders.created_at', 'desc')
            ->get();

        $misFinanciamientos = DB::table('financing_agreements')
            ->join('users', 'financing_agreements.maker_id', '=', 'users.id')
            ->where('financing_agreements.lab_id', $lab->id)
            ->whereIn('financing_agreements.status', ['pending', 'active'])
            ->select('financing_agreements.*', 'users.name as maker_name', 'users.slug as maker_slug', 'users.email as maker_email')
            ->orderBy('financing_agreements.created_at', 'desc')
            ->get();

        $makersExplorador = User::where('role', 'maker')
            ->select('users.*')
            ->selectRaw('(SELECT COUNT(*) FROM reviews WHERE reviews.reviewee_id = users.id) as total_resenas')
            ->selectRaw('(SELECT COUNT(*) FROM mission_applications WHERE mission_applications.maker_id = users.id AND mission_applications.status = "accepted") as misiones_completadas')
            ->orderBy('reputation_score', 'desc')
            ->get();
        
        $postulantesData = DB::table('mission_applications')
            ->join('users', 'mission_applications.maker_id', '=', 'users.id')
            ->join('missions', 'mission_applications.mission_id', '=', 'missions.id')
            ->where('missions.lab_id', $lab->id)
            ->select('mission_applications.*', 'users.name as maker_name', 'users.slug as maker_slug', 'users.reputation_score')
            ->get();

        $postulantesPorMision = [];
        foreach ($postulantesData as $p) { 
            $postulantesPorMision[$p->mission_id][] = $p; 
        }

        // 6. Sistema de Alertas
        $notificaciones = DB::table('notifications')->where('user_id', $lab->id)->latest()->limit(10)->get();
        $unread_count = $notificaciones->where('is_read', false)->count();

        // Variables Macroeconómicas Consolidadas
        $totalHistoricoEmitido = $totalMinted; 
        $totalFinanciados = $misFinanciamientos->where('status', 'active')->count(); 
        $totalPorCobrar = $misFinanciamientos->sum('amount_remaining') ?? 0; 
        $makerSkills = [];

        return view('lab.dashboard', compact(
            'lab', 'misActivos', 'activosFisicos', 'misMisiones', 'misTransacciones', 'misReservas', 
            'misFinanciamientos', 'makersExplorador', 'makerSkills', 'postulantesPorMision', 
            'notificaciones', 'unread_count', 'totalActivosCount', 'totalMaquinasCount', 
            'totalServiciosCount', 'totalLabsConectados', 'totalMinted', 'ofertadosCongelados', 
            'enReserva', 'statsMisiones', 'totalMisionesCount', 'dadosDeBajaValor', 'escrowRealMisiones', 
            'historicoPagadoMisiones', 'ofertadosTotal', 'saldoTotal', 'isFrozen', 'totalHistoricoEmitido', 
            'totalHistoricoQuemado', 'totalFinanciados', 'totalPorCobrar'
        ));
    }

    public function readNotifications()
    {
        DB::table('notifications')->where('user_id', auth()->id())->update(['is_read' => true]);
        return redirect()->route('lab.dashboard');
    }
}
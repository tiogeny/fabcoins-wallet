<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
    public function index()
    {
        $lab = auth()->user();

        $misActivos = $lab->activos()->with('categoriaGlobal')->orderBy('asset_type')->orderBy('custom_name')->get();
        $misMisiones = $lab->misiones()->latest()->get();
        $misTransacciones = $lab->transacciones()->latest()->limit(50)->get();

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

        $todasLasSkills = DB::table('user_skills')
            ->join('skills_catalog', 'user_skills.skill_id', '=', 'skills_catalog.id')
            ->leftJoin('skill_endorsements', function($join) {
                $join->on('skill_endorsements.skill_id', '=', 'skills_catalog.id')
                     ->on('skill_endorsements.maker_id', '=', 'user_skills.user_id');
            })
            ->select('user_skills.user_id', 'skills_catalog.name', 'skills_catalog.type', DB::raw('COUNT(skill_endorsements.id) as endorsements'))
            ->groupBy('user_skills.user_id', 'skills_catalog.id', 'skills_catalog.name', 'skills_catalog.type')
            ->orderBy('endorsements', 'desc')
            ->get();

        $makerSkills = [];
        foreach ($todasLasSkills as $sk) {
            $makerSkills[$sk->user_id][] = $sk;
        }

        $postulantesData = DB::table('mission_applications')
            ->join('users', 'mission_applications.maker_id', '=', 'users.id')
            ->join('missions', 'mission_applications.mission_id', '=', 'missions.id')
            ->where('missions.lab_id', $lab->id)
            ->select('mission_applications.*', 'users.name as maker_name', 'users.slug as maker_slug', 'users.reputation_score', 'users.deuda_lab_id', 'users.deuda_fc')
            ->get();

        $postulantesPorMision = [];
        foreach ($postulantesData as $p) {
            $postulantesPorMision[$p->mission_id][] = $p;
        }

        $notificaciones = DB::table('notifications')->where('user_id', $lab->id)->latest()->limit(10)->get();
        $unreadCount = $notificaciones->where('is_read', false)->count();

        // MATEMÁTICA FINANCIERA ORIGINAL
        $saldoTotal = $lab->saldo_total;
        $isFrozen = ($saldoTotal < 0);
        $totalHistoricoEmitido = $lab->activos()->sum('generated_fc');
        $totalEnviadoEscrow = $lab->transacciones()->where('type', 'escrow')->sum(DB::raw('ABS(amount)'));
        $totalDevueltoEscrow = $lab->transacciones()->where('type', 'income')->where('description', 'LIKE', 'Devolución de Escrow%')->sum('amount');
        
        $trabajoTotalRevisado = DB::table('mission_applications')
            ->join('missions', 'mission_applications.mission_id', '=', 'missions.id')
            ->where('missions.lab_id', $lab->id)
            ->where('mission_applications.status', 'accepted')
            ->sum('missions.reward_fc');

        $totalAmortizado = $lab->transacciones()->where('type', 'income')->where('description', 'LIKE', 'Retorno de Crédito Fab (Misión #%')->sum('amount');

        $escrowRealMisiones = max(0, $totalEnviadoEscrow - $totalDevueltoEscrow - $trabajoTotalRevisado);
        $historicoPagadoMisiones = max(0, $trabajoTotalRevisado - $totalAmortizado);
        $totalPorCobrar = DB::table('financing_agreements')->where('lab_id', $lab->id)->where('status', 'active')->sum('amount_remaining');
        $totalHistoricoQuemado = DB::table('orders')->join('lab_assets', 'orders.asset_id', '=', 'lab_assets.id')->where('lab_assets.lab_id', $lab->id)->where('orders.status', 'completed')->sum('orders.total_fc');
        $totalFinanciados = DB::table('financing_agreements')->where('lab_id', $lab->id)->where('status', 'active')->count();

        return view('lab.dashboard', compact(
            'lab', 'misActivos', 'misMisiones', 'misTransacciones', 'misReservas', 'misFinanciamientos',
            'makersExplorador', 'makerSkills', 'postulantesPorMision', 'notificaciones', 'unreadCount',
            'saldoTotal', 'isFrozen', 'totalHistoricoEmitido', 'escrowRealMisiones', 'historicoPagadoMisiones',
            'totalPorCobrar', 'totalHistoricoQuemado', 'totalFinanciados'
        ));
    }

    public function updateProfile(Request $request)
    {
        auth()->user()->update([
            'bio' => strip_tags($request->input('bio'), '<b><strong><i><em><u><ul><li><ol><br><p>'),
            'address' => trim($request->input('address')),
            'latitude' => $request->input('latitude') ? floatval($request->input('latitude')) : null,
            'longitude' => $request->input('longitude') ? floatval($request->input('longitude')) : null
        ]);
        return redirect()->route('lab.dashboard')->with('msg', 'profile_updated');
    }

    public function changePassword(Request $request)
    {
        $lab = auth()->user();
        if (!Hash::check($request->input('current_password'), $lab->password)) {
            return redirect()->route('lab.dashboard')->with('error', "La contraseña actual es incorrecta.");
        }
        $lab->update(['password' => Hash::make($request->input('new_password'))]);
        return redirect()->route('lab.dashboard')->with('msg', 'pass_ok');
    }

    public function readNotifications()
    {
        DB::table('notifications')->where('user_id', auth()->id())->update(['is_read' => true]);
        return redirect()->route('lab.dashboard');
    }

    public function exportCsv()
    {
        $lab = auth()->user();
        if (ob_get_length()) ob_end_clean();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="Estado_Cuenta_Lab_' . date('Ymd_His') . '.csv"');
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        $esEn = (app()->getLocale() === 'en');
        fputcsv($output, $esEn ? ['Date and Time', 'Concept / Description', 'Operation Type', 'Amount (FC)'] : ['Fecha y Hora', 'Concepto / Descripción', 'Tipo de Operación', 'Monto (FC)']);

        $txs = DB::table('transactions')->where('user_id', $lab->id)->orderBy('created_at', 'desc')->get();
        foreach ($txs as $row) {
            $tipo = $row->type;
            if (!$esEn) {
                if ($tipo === 'income') $tipo = 'Ingreso (Recibido)';
                elseif ($tipo === 'expense') $tipo = 'Egreso (Gasto)';
                elseif ($tipo === 'mint') $tipo = 'Emisión [MINT]';
                elseif ($tipo === 'escrow') $tipo = 'Reserva [ESCROW]';
            } else {
                if ($tipo === 'income') $tipo = 'Income';
                elseif ($tipo === 'expense') $tipo = 'Expense';
                elseif ($tipo === 'mint') $tipo = 'Tokenization [MINT]';
                elseif ($tipo === 'escrow') $tipo = 'Guarantee [ESCROW]';
            }
            $signo = in_array($row->type, ['income', 'mint']) ? '+' : '-';
            fputcsv($output, [date('d/m/Y H:i:s', strtotime($row->created_at)), $row->description, $tipo, $signo . number_format($row->amount, 2, '.', '')]);
        }
        fclose($output);
        exit;
    }
}
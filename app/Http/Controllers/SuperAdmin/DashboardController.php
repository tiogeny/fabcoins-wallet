<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Procesa la macroeconomía en vivo y compila el panel de control central
     */
    public function index()
    {
        $superadmin = auth()->user();

        // 1. MACROECONOMÍA Y VARIABLES MONETARIAS REALES
        $total_fc = floatval(DB::table('transactions')->where('type', 'mint')->sum('amount'));
        
        $total_bovedas = floatval(DB::table('transactions as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->where('u.role', 'lab')
            ->sum(DB::raw("CASE WHEN t.type IN ('income', 'mint') THEN t.amount ELSE -t.amount END")));

        $total_circulando = floatval(DB::table('transactions as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->where('u.role', 'maker')
            ->sum(DB::raw("CASE WHEN t.type IN ('income', 'mint') THEN t.amount ELSE -t.amount END")));

        $escrow_c = floatval(DB::table('orders')->whereIn('status', ['pending', 'rescheduled'])->sum('total_fc'));
        $escrow_m = floatval(DB::table('missions')
            ->whereIn('status', ['open', 'assigned'])
            ->sum(DB::raw("reward_fc * (spots_total - (SELECT COUNT(*) FROM mission_applications WHERE mission_id = missions.id AND is_reviewed = 1))")));
        $total_escrow = $escrow_m + $escrow_c;

        $total_quemado = floatval(DB::table('transactions')->where('type', 'burn')->sum('amount'));

        $pib_maquinas = floatval(DB::table('orders')->where('status', 'completed')->where('created_at', '>=', now()->subDays(30))->sum('total_fc'));
        $pib_misiones = floatval(DB::table('mission_applications as ma')
            ->join('missions as m', 'ma.mission_id', '=', 'm.id')
            ->where('ma.is_reviewed', 1)
            ->where('ma.applied_at', '>=', now()->subDays(30))
            ->sum('m.reward_fc'));
            
        $volumen_30d = $pib_maquinas + $pib_misiones;

        $total_deuda_global = floatval(DB::table('financing_agreements')->where('status', 'active')->sum('amount_remaining'));
        $tasa_absorcion = ($total_fc > 0) ? ($total_quemado / $total_fc) * 100 : 0;

        $respaldo_vivo = floatval(DB::table('lab_assets')->where('expires_at', '>', now())->whereRaw('useful_life_hours > consumed_hours')->where('status', 'active')->sum('generated_fc'));
        $dados_de_baja = max(0, $total_fc - $respaldo_vivo);

        // 2. MONITOREO DE CAPACIDAD PRODUCTIVA DEMOGRÁFICA (DPI)
        $total_makers_count = User::where('role', 'maker')->count();
        $total_labs_count = User::where('role', 'lab')->count();
        
        $mix = [
            'machine' => DB::table('lab_assets')->where('asset_type', 'machine')->count(),
            'service' => DB::table('lab_assets')->whereIn('asset_type', ['space', 'service', 'workshop'])->count()
        ];

        $capacidad_horas = floatval(DB::table('lab_assets')->where('asset_type', 'machine')->where('expires_at', '>', now())->where('status', 'active')->sum(DB::raw('useful_life_hours - consumed_hours')));
        $val_equipos = floatval(DB::table('orders')->where('status', 'completed')->sum('total_fc'));
        $val_talento = floatval(DB::table('mission_applications as ma')->join('missions as m', 'ma.mission_id', '=', 'm.id')->where('ma.is_reviewed', 1)->sum('m.reward_fc'));

        // 3. SECCIONES DE AUDITORÍA Y TABLAS DE REFERENCIA
        $global_pct = DB::table('global_settings')->where('setting_key', 'tokenization_pct')->value('setting_value');
        $catalogo = DB::table('global_catalog')->orderBy('asset_type')->orderBy('generic_name')->get();
        $top_labs = User::where('role', 'lab')->orderBy('reputation_score', 'desc')->limit(5)->get();
        $top_makers = User::where('role', 'maker')->orderBy('reputation_score', 'desc')->limit(5)->get();
        
        $ultimas_tx = DB::table('transactions as t')
            ->join('users as u', 't.user_id', '=', 'u.id')
            ->select('t.*', 'u.name as user_name')
            ->orderBy('t.created_at', 'desc')
            ->limit(10)
            ->get();

        $radar_misiones = DB::table('missions as m')
            ->join('users as u', 'm.lab_id', '=', 'u.id')
            ->select('m.*', 'u.id as lab_id', 'u.name as lab_name')
            ->orderBy('m.created_at', 'desc')
            ->get();

        $makers_financiados = DB::table('financing_agreements as c')
            ->join('users as f', 'c.maker_id', '=', 'f.id')
            ->join('users as l', 'c.lab_id', '=', 'l.id')
            ->where('c.status', 'active')
            ->select('c.*', 'f.name as maker_name', 'l.name as lab_name')
            ->orderBy('c.amount_remaining', 'desc')
            ->get();

        return view('superadmin.dashboard', compact(
            'superadmin', 'total_fc', 'total_bovedas', 'total_circulando', 'total_escrow', 'total_quemado',
            'volumen_30d', 'total_deuda_global', 'tasa_absorcion', 'dados_de_baja', 'total_makers_count',
            'total_labs_count', 'mix', 'capacidad_horas', 'val_equipos', 'val_talento', 'global_pct',
            'catalogo', 'top_labs', 'top_makers', 'ultimas_tx', 'radar_misiones', 'makers_financiados'
        ));
    }

    /**
     * Despacha los listados analíticos mediante llamadas AJAX nativas
     */
    public function getAjaxDesglose(Request $request)
    {
        $tipo = $request->query('ajax_desglose');
        $html = '<style>
            .swal-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; margin-top:10px; }
            .swal-table th { border-bottom: 2px solid #34495e; padding: 8px; color: #bdc3c7; position: sticky; top: 0; background: #1a252f; }
            .swal-table td { border-bottom: 1px solid #2c3e50; padding: 8px; color: #fff; }
        </style>';
        $html .= '<div style="max-height: 350px; overflow-y: auto;"><table class="swal-table">';

        if ($tipo === 'mint') {
            $html .= '<tr><th>Fecha</th><th>Lab</th><th>Detalle</th><th>Emitido</th></tr>';
            $rows = DB::table('transactions as t')->join('users as u', 't.user_id', '=', 'u.id')->where('t.type', 'mint')->orderBy('t.created_at', 'desc')->limit(100)->select('u.name', 't.description', 't.amount', 't.created_at')->get();
            foreach($rows as $r) { $html .= '<tr><td>'.date('d/m/y', strtotime($r->created_at)).'</td><td>'.htmlspecialchars($r->name).'</td><td>'.htmlspecialchars($r->description).'</td><td style="color:#3498db; font-weight:bold;">'.number_format($r->amount,2).'</td></tr>'; }
        } elseif ($tipo === 'bovedas') {
            $html .= '<tr><th>Laboratorio</th><th>Balance Líquido (FC)</th></tr>';
            $rows = DB::table('users as u')->join('transactions as t', 'u.id', '=', 't.user_id')->where('u.role', 'lab')->groupBy('u.id', 'u.name')->select('u.name', DB::raw("SUM(CASE WHEN t.type IN ('income', 'mint') THEN t.amount ELSE -t.amount END) as balance"))->get();
            foreach($rows as $r) { if($r->balance > 0) $html .= '<tr><td>'.htmlspecialchars($r->name).'</td><td style="color:#2ecc71; font-weight:bold;">'.number_format($r->balance,2).'</td></tr>'; }
        } elseif ($tipo === 'circulante') {
            $html .= '<tr><th>Maker</th><th>Balance Líquido (FC)</th></tr>';
            $rows = DB::table('users as u')->join('transactions as t', 'u.id', '=', 't.user_id')->where('u.role', 'maker')->groupBy('u.id', 'u.name')->select('u.name', DB::raw("SUM(CASE WHEN t.type IN ('income', 'mint') THEN t.amount ELSE -t.amount END) as balance"))->get();
            foreach($rows as $r) { if($r->balance > 0) $html .= '<tr><td>'.htmlspecialchars($r->name).'</td><td style="color:#2ecc71; font-weight:bold;">'.number_format($r->balance,2).'</td></tr>'; }
        } elseif ($tipo === 'escrow') {
            $html .= '<tr><th colspan="3" style="background:#2c3e50;">🔒 EN MISIONES (LABS)</th></tr>';
            $stmt_m = DB::select("SELECT u.name, m.id, (m.reward_fc * (m.spots_total - (SELECT COUNT(*) FROM mission_applications WHERE mission_id = m.id AND is_reviewed = 1))) as retenido FROM missions m JOIN users u ON m.lab_id = u.id WHERE m.status IN ('open', 'assigned')");
            foreach($stmt_m as $r) { if($r->retenido > 0) $html .= '<tr><td>'.htmlspecialchars($r->name).'</td><td>Misión #'.$r->id.'</td><td style="color:#f39c12; font-weight:bold;">'.number_format($r->retenido,2).'</td></tr>'; }
            $html .= '<tr><th colspan="3" style="background:#2c3e50; margin-top:10px;">🔒 EN RESERVAS (MAKERS)</th></tr>';
            $stmt_o = DB::table('orders as o')->join('users as u', 'o.maker_id', '=', 'u.id')->whereIn('o.status', ['pending', 'rescheduled'])->select('u.name', 'o.id', 'o.total_fc')->get();
            foreach($stmt_o as $r) { $html .= '<tr><td>'.htmlspecialchars($r->name).'</td><td>Reserva #'.$r->id.'</td><td style="color:#f39c12; font-weight:bold;">'.number_format($r->total_fc,2).'</td></tr>'; }
        } elseif ($tipo === 'burn') {
            $html .= '<tr><th>Fecha</th><th>Maker</th><th>Detalle de Quema</th><th>Quemado</th></tr>';
            $rows = DB::table('transactions as t')->join('users as u', 't.user_id', '=', 'u.id')->where('t.type', 'burn')->orderBy('t.created_at', 'desc')->limit(100)->select('u.name', 't.description', 't.amount', 't.created_at')->get();
            foreach($rows as $r) { $html .= '<tr><td>'.date('d/m/y', strtotime($r->created_at)).'</td><td>'.htmlspecialchars($r->name).'</td><td>'.htmlspecialchars($r->description).'</td><td style="color:#e74c3c; font-weight:bold;">'.number_format($r->amount,2).'</td></tr>'; }
        } elseif ($tipo === 'pib') { // 🔥 RESTAURADO CASO PIB COMPLETO
            $html .= '<tr><th colspan="3" style="background:#2c3e50;">⚡ MÁQUINAS ALQUILADAS (30 DÍAS)</th></tr>';
            $orders = DB::table('orders as o')->join('users as u', 'o.maker_id', '=', 'u.id')->where('o.status', 'completed')->where('o.created_at', '>=', now()->subDays(30))->select('u.name', 'o.id', 'o.total_fc')->get();
            foreach($orders as $r) { $html .= '<tr><td>'.htmlspecialchars($r->name).'</td><td>Orden #'.$r->id.'</td><td style="color:#3498db; font-weight:bold;">'.number_format($r->total_fc,2).'</td></tr>'; }
            $html .= '<tr><th colspan="3" style="background:#2c3e50; margin-top:10px;">⚡ TRABAJOS COMPLETADOS (30 DÍAS)</th></tr>';
            $missions = DB::table('mission_applications as ma')->join('missions as m', 'ma.mission_id', '=', 'm.id')->join('users as u', 'ma.maker_id', '=', 'u.id')->where('ma.is_reviewed', 1)->where('ma.applied_at', '>=', now()->subDays(30))->select('u.name', 'm.id', 'm.reward_fc')->get();
            foreach($missions as $r) { $html .= '<tr><td>'.htmlspecialchars($r->name).'</td><td>Misión #'.$r->id.'</td><td style="color:#3498db; font-weight:bold;">'.number_format($r->reward_fc,2).'</td></tr>'; }
        } elseif ($tipo === 'creditos') {
            $html .= '<tr><th>Laboratorio</th><th>Maker (Deudor)</th><th>Deuda Restante</th></tr>';
            $rows = DB::table('financing_agreements as f')->join('users as ul', 'f.lab_id', '=', 'ul.id')->join('users as um', 'f.maker_id', '=', 'um.id')->where('f.status', 'active')->where('f.amount_remaining', '>', 0)->orderBy('f.amount_remaining', 'desc')->select('ul.name as lab', 'um.name as maker', 'f.amount_remaining')->get();
            foreach($rows as $r) { $html .= '<tr><td>'.htmlspecialchars($r->lab).'</td><td>'.htmlspecialchars($r->maker).'</td><td style="color:#f1c40f; font-weight:bold;">'.number_format($r->amount_remaining,2).'</td></tr>'; }
        } elseif ($tipo === 'velocidad') { // 🔥 RESTAURADO METRICA VELOCIDAD
            $t_mint = floatval(DB::table('transactions')->where('type', 'mint')->sum('amount'));
            $t_burn = floatval(DB::table('transactions')->where('type', 'burn')->sum('amount'));
            $t_abs = ($t_mint > 0) ? ($t_burn / $t_mint) * 100 : 0;
            $html .= '<tr><th>Métrica</th><th>Valor Actual</th></tr>';
            $html .= '<tr><td>Masa Monetaria Emitida</td><td style="color:#f1c40f; font-weight:bold;">'.number_format($t_mint, 2).' FC</td></tr>';
            $html .= '<tr><td>Total Consumido (Quemado)</td><td style="color:#e74c3c; font-weight:bold;">'.number_format($t_burn, 2).' FC</td></tr>';
            $html .= '<tr><td>Tasa de Absorción Global</td><td style="color:#3498db; font-weight:bold;">'.number_format($t_abs, 2).'%</td></tr>';
        } elseif ($tipo === 'baja') { // 🔥 RESTAURADO METRICA BAJA
            $html .= '<tr><th>Laboratorio</th><th>Máquina / Activo</th><th>Total Emitido FC</th></tr>';
            $rows = DB::table('lab_assets as a')->join('users as u', 'a.lab_id', '=', 'u.id')->where('a.expires_at', '<=', now())->orWhereRaw('a.useful_life_hours <= a.consumed_hours')->orWhere('a.status', '!=', 'active')->select('u.name', 'a.custom_name', 'a.generated_fc')->get();
            foreach($rows as $r) { $html .= '<tr><td>'.htmlspecialchars($r->name).'</td><td>'.htmlspecialchars($r->custom_name).'</td><td style="color:#7f8c8d; font-weight:bold;">'.number_format($r->generated_fc,2).'</td></tr>'; }
        }

        $html .= '</table></div>';
        return response($html);
    }
}
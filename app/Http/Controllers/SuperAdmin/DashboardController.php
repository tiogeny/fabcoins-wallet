<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\MailService;
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

       // 1. 🪙 MASA MONETARIA TOTAL EMITIDA
        $total_fc = floatval(DB::table('transactions')->where('type', 'mint')->sum('amount'));

        // 2. ⏳ ESCROW GLOBAL NETO (REPARADO): Total enviado a escrow menos los pagos ya realizados a creadores
        $totalEnviadoEscrow = floatval(DB::table('transactions')->where('type', 'escrow')->sum('amount'));
        
        $trabajoTotalRevisado = floatval(DB::table('mission_applications')
            ->where('is_reviewed', 1)
            ->join('missions', 'mission_applications.mission_id', '=', 'missions.id')
            ->sum('missions.reward_fc'));

        $escrowMisiones = max(0, $totalEnviadoEscrow - $trabajoTotalRevisado); // 🎯 Esto dará los 1,100 FC reales
        
        // Calcular el remanente de reservas mecánicas
        $escrowReservasRaw = floatval(DB::table('orders')->whereIn('status', ['pending', 'rescheduled'])->sum('total_fc'));
        $creditosPendientesGlobal = floatval(DB::table('financing_agreements')->where('status', 'pending')->sum('amount_initial'));
        
        $total_escrow = $escrowMisiones + max(0, $escrowReservasRaw - $creditosPendientesGlobal); // 🎯 Esto dará 1,185 FC

        // 3. 🛡️ BÓVEDAS LABS LIQUIDAS: Sumamos los balances reales de todas las cuentas tipo 'lab' en la BD
        $total_bovedas = floatval(DB::table('transactions')
            ->join('users', 'transactions.user_id', '=', 'users.id')
            ->where('users.role', 'lab')
            ->selectRaw("SUM(CASE 
                WHEN transactions.type IN ('income', 'mint') THEN transactions.amount 
                WHEN transactions.type = 'consumed' THEN 0 
                ELSE -transactions.amount 
            END) as saldo")
            ->value('saldo') ?? 0);

        // 4. REGISTRO HISTÓRICO DE DEFLACIÓN (Métrica estadística complementaria)
        $total_quemado = floatval(DB::table('transactions')->where('type', 'consumed')->sum('amount'));

        // 5. 🚀 EN CIRCULACIÓN (RESIDUAL CLANESTINO): Restamos también el pool quemado 
        // para que coincida exactamente con los saldos líquidos reales de las billeteras de los creadores
        $total_circulando = max(0, $total_fc - $total_bovedas - $total_escrow - $total_quemado);

        

        // PIB de la Red (Velocidad de transacciones comerciales de los últimos 30 días)
        $pib_maquinas = floatval(DB::table('orders')->where('status', 'completed')->where('created_at', '>=', now()->subDays(30))->sum('total_fc'));
        $pib_misiones = floatval(DB::table('mission_applications as ma')
            ->join('missions as m', 'ma.mission_id', '=', 'm.id')
            ->where('ma.is_reviewed', 1)
            ->where('ma.created_at', '>=', now()->subDays(30))
            ->sum('m.reward_fc'));
        $volumen_30d = $pib_maquinas + $pib_misiones;

        $tasa_absorcion = ($total_fc > 0) ? ($total_quemado / $total_fc) * 100 : 0;
        $total_deuda_global = floatval(DB::table('financing_agreements')->where('status', 'active')->sum('amount_remaining'));
        $dados_de_baja = 0; 

        // 2. MONITOREO DE CAPACIDAD PRODUCTIVA DEMOGRÁFICA (DPI)
        $total_creators_count = User::where('role', 'creator')->count();
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
        $top_creators = User::where('role', 'creator')->orderBy('reputation_score', 'desc')->limit(5)->get();
        
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

        $creators_financiados = DB::table('financing_agreements as c')
            ->join('users as f', 'c.creator_id', '=', 'f.id')
            ->join('users as l', 'c.lab_id', '=', 'l.id')
            ->where('c.status', 'active')
            ->select('c.*', 'f.name as creator_name', 'l.name as lab_name')
            ->orderBy('c.amount_remaining', 'desc')
            ->get();

        // 🔍 Buscador universal de usuarios para la consola de auditoría
        $todos_los_usuarios = User::whereIn('role', ['lab', 'creator'])->orderBy('role')->orderBy('name')->get(['id', 'name', 'email', 'role']);

        return view('superadmin.dashboard', compact(
            'superadmin', 'total_fc', 'total_bovedas', 'total_circulando', 'total_escrow', 'total_quemado',
            'volumen_30d', 'total_deuda_global', 'tasa_absorcion', 'dados_de_baja', 'total_creators_count',
            'total_labs_count', 'mix', 'capacidad_horas', 'val_equipos', 'val_talento', 'global_pct',
            'catalogo', 'top_labs', 'top_creators', 'ultimas_tx', 'radar_misiones', 'creators_financiados', 'todos_los_usuarios'
        ));
    }

    /**
     * Despacha los listados analíticos mediante llamadas AJAX nativas
     */
    public function getAjaxDesglose(Request $request)
    {
        $tipo = $request->query('ajax_desglose');
        $html = '<style>
            .swal-table { width: 100%; border-collapse: collapse; font-size: 13px; text-align: left; margin-top:10px; font-family:"Inter", sans-serif; }
            .swal-table th { border-bottom: 2px solid rgba(255,255,255,0.08); padding: 10px 8px; color: #a0aec0; position: sticky; top: 0; background: #1c2230; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; }
            .swal-table td { border-bottom: 1px solid rgba(255,255,255,0.04); padding: 10px 8px; color: #cbd5e0; }
        </style>';
        $html .= '<div style="max-height: 350px; overflow-y: auto;"><table class="swal-table">';

        if ($tipo === 'mint') {
            $html .= '<tr><th>Fecha</th><th>Lab</th><th>Detalle</th><th>Emitido</th></tr>';
            $rows = DB::table('transactions as t')->join('users as u', 't.user_id', '=', 'u.id')->where('t.type', 'mint')->orderBy('t.created_at', 'desc')->limit(100)->select('u.name', 't.description', 't.amount', 't.created_at')->get();
            foreach($rows as $r) { $html .= '<tr><td>'.date('d/m/y', strtotime($r->created_at)).'</td><td>'.htmlspecialchars($r->name).'</td><td>'.htmlspecialchars($r->description).'</td><td style="color:#3498db; font-weight:bold;">'.number_format($r->amount,2).'</td></tr>'; }
        } elseif ($tipo === 'bovedas') {
            $html .= '<tr><th>Laboratorio</th><th>Balance Líquido (FC)</th></tr>';
            
            // ⚖️ BALANCE CENTRAL AUDITADO: Emisión Base - Escrow de Cupos Vivos - Masa ya entregada a los Creadores
            $rows = DB::table('users as u')
                ->where('u.role', 'lab')
                ->select('u.name', DB::raw("
                    (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = u.id AND type = 'mint') 
                    - 
                    (SELECT COALESCE(SUM(m.reward_fc * (m.spots_total - (SELECT COUNT(*) FROM mission_applications WHERE mission_id = m.id AND is_reviewed = 1))), 0) 
                     FROM missions m WHERE m.lab_id = u.id AND m.status IN ('open', 'assigned'))
                    -
                    (SELECT COALESCE(SUM(m2.reward_fc), 0) 
                     FROM mission_applications ma2 
                     JOIN missions m2 ON ma2.mission_id = m2.id 
                     WHERE m2.lab_id = u.id AND ma2.is_reviewed = 1)
                    -
                    (SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE user_id = u.id AND type = 'expense') -- 🔥 RECONCILIACIÓN: Restar créditos otorgados y egresos ordinarios
                    as balance
                "))->get();
                
            foreach($rows as $r) { 
                $html .= '<tr><td>'.htmlspecialchars($r->name).'</td><td style="color:#3498db; font-weight:bold;">'.number_format($r->balance, 2).'</td></tr>'; 
            }

        } elseif ($tipo === 'circulante') {
            $html .= '<tr><th>🚀 Creador</th><th>Balance Líquido Neto (FC)</th></tr>';
            // 🧠 CALIBRACIÓN EN VIVO: Extrae el saldo neto real ejecutando la balanza del ledger de transacciones
            $rows = DB::table('users')
                ->where('role', 'creator')
                ->get()
                ->map(function($u) {
                    $u->balance = DB::table('transactions')->where('user_id', $u->id)->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as total")->value('total') ?? 0;
                    return $u;
                });
            foreach($rows as $r) { if($r->balance > 0) $html .= '<tr><td>👤 '.htmlspecialchars($r->name).'</td><td style="color:#2ecc71; font-weight:bold;">'.number_format($r->balance,2).' FC</td></tr>'; }
        } elseif ($tipo === 'escrow') {
            // ⚖️ ENCABEZADO DE AUDITORÍA: Cambiamos "Laboratorio" por "Entidad" para soportar Creadores y Labs
            $html .= '<tr><th>Entidad (Origen)</th><th>Concepto / Descripción</th><th style="text-align: right;">Monto (FC)</th></tr>';
            
            // 1. SECCIÓN DE MISIONES (El dinero retenido pertenece a los Laboratorios)
            $html .= '<tr><td colspan="3" style="background: rgba(255,255,255,0.02); font-weight: bold; color: #f1c40f; font-size: 11px; padding: 8px 12px; text-transform: uppercase;">🔒 En Misiones (Fondos de Labs)</td></tr>';
            
            $misiones = DB::table('missions as m')
                ->join('users as u', 'm.lab_id', '=', 'u.id')
                ->whereIn('m.status', ['open', 'assigned'])
                ->select('u.name as lab_name', 'm.title', 'm.reward_fc', 'm.spots_total', 'm.id')
                ->get();

            foreach ($misiones as $m) {
                $reviewedSpots = DB::table('mission_applications')
                    ->where('mission_id', $m->id)
                    ->where('is_reviewed', 1)
                    ->count();
                
                $escrowMision = $m->reward_fc * max(0, $m->spots_total - $reviewedSpots);
                
                if ($escrowMision > 0) {
                    $html .= "<tr>
                        <td>{$m->lab_name}</td>
                        <td>{$m->title}</td>
                        <td style='color: #f1c40f; font-weight: bold; text-align: right;'>" . number_format($escrowMision, 2) . "</td>
                    </tr>";
                }
            }

            // 2. 🎯 SECCIÓN DE RESERVAS REPARADA (El dinero retenido pertenece a los Creadores)
            $html .= '<tr><td colspan="3" style="background: rgba(255,255,255,0.02); font-weight: bold; color: #f1c40f; font-size: 11px; padding: 8px 12px; text-transform: uppercase;">📅 En Reservas (Fondos de Creadores)</td></tr>';

            // 🚀 CIRUGÍA SQL: Extraemos al deudor, el id de la orden y el id del lab del activo
            $reservas = DB::table('orders as o')
                ->join('users as c', 'o.creator_id', '=', 'c.id')
                ->join('lab_assets as a', 'o.asset_id', '=', 'a.id')
                ->whereIn('o.status', ['pending', 'rescheduled'])
                ->select('o.id as order_id', 'c.name as creator_name', 'a.custom_name as asset_name', 'o.total_fc', 'o.hours_requested', 'o.creator_id', 'a.lab_id')
                ->get();

            if ($reservas->isEmpty()) {
                $html .= '<tr><td colspan="3" style="color: #7f8c8d; font-style: italic; text-align: center; padding: 12px;">No hay alquileres pendientes en custodia</td></tr>';
            } else {
                foreach ($reservas as $r) {
                    // 🎯 PRECISIÓN RELACIONAL TOTAL: Verificamos si existe un crédito asociado directamente a este ID de orden único
                    $creditoAsociado = floatval(DB::table('financing_agreements')
                        ->where('order_id', $r->order_id)
                        ->where('status', 'pending')
                        ->value('amount_initial') ?? 0);

                    $montoNetoEscrow = max(0, $r->total_fc - $creditoAsociado);
                    
                    // Nota informativa bilingüe implícita para el SuperAdmin
                    $detalleConcepto = htmlspecialchars($r->asset_name) . " ({$r->hours_requested} hrs)";
                    if ($creditoAsociado > 0) {
                        $detalleConcepto .= "<br><small style='color: #7f8c8d;'>⚠️ Solicitud ISA pendiente: -" . number_format($creditoAsociado, 0) . " FC</small>";
                    }

                    $html .= "<tr>
                        <td>{$r->creator_name}</td>
                        <td>Reserva: {$detalleConcepto}</td>
                        <td style='color: #f1c40f; font-weight: bold; text-align: right;'>" . number_format($montoNetoEscrow, 2) . "</td>
                    </tr>";
                }
            }
        } elseif ($tipo === 'burn') {
            // 🚀 CORRECCIÓN: Apunta de forma nativa a la tabla de consumos reales 'consumed'
            $html .= '<tr><th>Fecha</th><th>Usuario</th><th>Detalle de Consumo</th><th>Monto</th></tr>';
            $rows = DB::table('transactions as t')->join('users as u', 't.user_id', '=', 'u.id')->where('t.type', 'consumed')->orderBy('t.created_at', 'desc')->limit(100)->select('u.name', 't.description', 't.amount', 't.created_at')->get();
            foreach($rows as $r) { $html .= '<tr><td>'.date('d/m/y', strtotime($r->created_at)).'</td><td>'.htmlspecialchars($r->name).'</td><td>'.htmlspecialchars($r->description).'</td><td style="color:#e67e22; font-weight:bold;">'.number_format($r->amount,2).'</td></tr>'; }
        } elseif ($tipo === 'pib') {
            $html .= '<tr><th colspan="3" style="background:#2c3e50;">⚡ MÁQUINAS ALQUILADAS (30 DÍAS)</th></tr>';
            $orders = DB::table('orders as o')->join('users as u', 'o.creator_id', '=', 'u.id')->where('o.status', 'completed')->where('o.created_at', '>=', now()->subDays(30))->select('u.name', 'o.id', 'o.total_fc')->get();
            foreach($orders as $r) { $html .= '<tr><td>'.htmlspecialchars($r->name).'</td><td>Orden #'.$r->id.'</td><td style="color:#3498db; font-weight:bold;">'.number_format($r->total_fc,2).'</td></tr>'; }
            $html .= '<tr><th colspan="3" style="background:#2c3e50; margin-top:10px;">⚡ TRABAJOS COMPLETADOS (30 DÍAS)</th></tr>';
            $missions = DB::table('mission_applications as ma')->join('missions as m', 'ma.mission_id', '=', 'm.id')->join('users as u', 'ma.creator_id', '=', 'u.id')->where('ma.is_reviewed', 1)->where('ma.created_at', '>=', now()->subDays(30))->select('u.name', 'm.id', 'm.reward_fc')->get();
            foreach($missions as $r) { $html .= '<tr><td>'.htmlspecialchars($r->name).'</td><td>Misión #'.$r->id.'</td><td style="color:#3498db; font-weight:bold;">'.number_format($r->reward_fc,2).'</td></tr>'; }
        } elseif ($tipo === 'creditos') {
            $html .= '<tr><th>Laboratorio</th><th>Creador (Deudor)</th><th>Deuda Restante</th></tr>';
            $rows = DB::table('financing_agreements as f')->join('users as ul', 'f.lab_id', '=', 'ul.id')->join('users as um', 'f.creator_id', '=', 'um.id')->where('f.status', 'active')->where('f.amount_remaining', '>', 0)->orderBy('f.amount_remaining', 'desc')->select('ul.name as lab', 'um.name as creator', 'f.amount_remaining')->get();
            foreach($rows as $r) { $html .= '<tr><td>'.$r->lab.'</td><td>'.$r->creator.'</td><td style="color:#f1c40f; font-weight:bold;">'.number_format($r->amount_remaining,2).'</td></tr>'; }
        } elseif ($tipo === 'velocidad') {
            // 🚀 CORRECCIÓN: Sincronizado para usar el pool de consumos reales 'consumed'
            $t_mint = floatval(DB::table('transactions')->where('type', 'mint')->sum('amount'));
            $t_burn = floatval(DB::table('transactions')->where('type', 'consumed')->sum('amount'));
            $t_abs = ($t_mint > 0) ? ($t_burn / $t_mint) * 100 : 0;
            $html .= '<tr><th>Métrica</th><th>Valor Actual</th></tr>';
            $html .= '<tr><td>Masa Monetaria Emitida</td><td style="color:#f1c40f; font-weight:bold;">'.number_format($t_mint, 2).' FC</td></tr>';
            $html .= '<tr><td>Total Consumido (Deflación)</td><td style="color:#e67e22; font-weight:bold;">'.number_format($t_burn, 2).' FC</td></tr>';
            $html .= '<tr><td>Tasa de Absorción Global</td><td style="color:#3498db; font-weight:bold;">'.number_format($t_abs, 2).'%</td></tr>';
        } elseif ($tipo === 'audit_user') {
            $userId = $request->query('user_id');
            $userTarget = DB::table('users')->where('id', $userId)->first();
            
            if ($userTarget) {
                $html = '<div style="text-align:left; font-family:\'Inter\',sans-serif; color:#fff; padding:5px;">';
                $icon = $userTarget->role === 'lab' ? '🏭' : '👤';
                $html .= '<h3 style="margin:0; color:#f1c40f; font-family:\'Rajdhani\'; font-size:22px; font-weight:700;">'.$icon.' '.htmlspecialchars($userTarget->name).'</h3>';
                $html .= '<p style="margin:2px 0 20px 0; color:#a0aec0; font-size:12px;">📧 '.htmlspecialchars($userTarget->email).' | Rol: <strong style="text-transform:uppercase; color:#3498db;">'.$userTarget->role.'</strong></p>';
                
                if ($userTarget->role === 'lab') {
                    // =========================================================
                    // 🏭 SECCIÓN A: COMPILACIÓN MACROECONÓMICA DEL FAB LAB
                    // =========================================================
                    $minted = floatval(DB::table('transactions')->where('user_id', $userTarget->id)->where('type', 'mint')->sum('amount'));

                    // 🎯 RECONCILIACIÓN DE AUDITORÍA: El dinero solo sale del Escrow si el cupo individual ya fue calificado y liquidado
                    $escrow = floatval(DB::table('missions')
                        ->where('lab_id', $userTarget->id)
                        ->whereIn('status', ['open', 'assigned'])
                        ->get()
                        ->sum(function($m) {
                            $reviewedSpots = DB::table('mission_applications')
                                ->where('mission_id', $m->id)
                                ->where('is_reviewed', 1)
                                ->count();
                            return $m->reward_fc * max(0, $m->spots_total - $reviewedSpots);
                        }));

                    $pagadoCreadores = floatval(DB::table('mission_applications')
                        ->where('is_reviewed', 1)
                        ->join('missions', 'mission_applications.mission_id', '=', 'missions.id')
                        ->where('missions.lab_id', $userTarget->id)
                        ->sum('missions.reward_fc'));
                    
                    $saldo = DB::table('transactions')->where('user_id', $userTarget->id)->selectRaw("SUM(CASE WHEN type = 'mint' THEN amount WHEN type IN ('expense', 'escrow') THEN -amount ELSE 0 END) as total")->value('total') ?? 0;
                    $consumed = floatval(DB::table('transactions')->where('user_id', $userTarget->id)->where('type', 'consumed')->sum('amount'));

                    $cntActivos = DB::table('lab_assets')->where('lab_id', $userTarget->id)->count();
                    $cntMisiones = DB::table('missions')->where('lab_id', $userTarget->id)->count();
                    $cntReservasRecibidas = DB::table('orders')->join('lab_assets', 'orders.asset_id', '=', 'lab_assets.id')->where('lab_assets.lab_id', $userTarget->id)->count();

                    // Cápsula de Actividad (Lab)
                    $html .= '<div style="margin:-12px 0 18px 0; font-size:11px; background:rgba(255,255,255,0.02); padding:6px 12px; border-radius:6px; border:1px solid rgba(255,255,255,0.04); display:flex; gap:14px; color:#cbd5e0; font-weight:500;">';
                    $html .= '<span style="color:#7f8c8d; text-transform:uppercase; font-size:9.5px; font-weight:700; display:flex; align-items:center;">📊 Actividad:</span>';
                    $html .= '<span>🏭 '.$cntActivos.' Activos</span><span>🎯 '.$cntMisiones.' Misiones</span><span>📅 '.$cntReservasRecibidas.' Reservas</span></div>';

                    // Corona Superior (Lab)
                    $html .= '<div style="width: 100%; text-align: center; margin-bottom: 15px;">';
                    $html .= '<div style="border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; background: rgba(255,255,255,0.01);">';
                    $html .= '<span style="font-size:10px; color:#7f8c8d; text-transform:uppercase; letter-spacing:0.5px;">Masa Total Emitida (Colateral Base)</span>';
                    $html .= '<div style="color:#ffffff; font-size:24px; font-weight:800; font-family:\'Rajdhani\'; margin-top:2px;">🪙 '.number_format($minted,0).' FC</div></div></div>';

                    // Caja de Desglose de Masa (Lab)
                    $html .= '<div style="background: #131722; border: 1px dashed rgba(255, 255, 255, 0.06); padding: 15px; border-radius: 10px; margin-bottom: 15px;">';
                    $html .= '<div style="font-size: 9px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">⚖️ DISTRIBUCIÓN DE MASA EN RED</div>';
                    $html .= '<div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:8px;">';
                    $html .= '<div style="background:#1c2230; padding:10px; border-radius:6px; border-bottom:2px solid #3498db;"><span style="font-size:9px; color:#a0aec0;">Bóveda Líquida</span><br><span style="color:#3498db; font-weight:bold; font-family:\'Rajdhani\';">'.number_format($saldo,0).' FC</span></div>';
                    $html .= '<div style="background:#1c2230; padding:10px; border-radius:6px; border-bottom:2px solid #f1c40f;"><span style="font-size:9px; color:#a0aec0;">En Escrow</span><br><span style="color:#f1c40f; font-weight:bold; font-family:\'Rajdhani\';">'.number_format($escrow,0).' FC</span></div>';
                    $html .= '<div style="background:#1c2230; padding:10px; border-radius:6px; border-bottom:2px solid #2ecc71;"><span style="font-size:9px; color:#a0aec0;">Masa en Creadores</span><br><span style="color:#2ecc71; font-weight:bold; font-family:\'Rajdhani\';">'.number_format($pagadoCreadores,0).' FC</span></div>';
                    $html .= '</div></div>';

                    // Bloque de Capacidad Física Consumida (Lab)
                    $html .= '<div style="background:#131722; padding:12px; border-radius:8px; border-left:4px solid #e67e22; margin-bottom:20px; display:flex; justify-content:space-between; align-items:center;">';
                    $html .= '<span>🔥 <strong>Capacidad Realizada / Consumida:</strong><br><small style="color:#7f8c8d; font-size:10px;">Masa deflatada por uso físico o amortizaciones</small></span>';
                    $html .= '<span style="color:#e67e22; font-size:20px; font-weight:800; font-family:\'Rajdhani\';">'.number_format($consumed,0).' FC</span></div>';

                    // 📜 LEDGER AVANZADO EN 4 COLUMNAS EXCLUSIVO PARA LABS
                    $html .= '<strong style="font-size:11px; color:#7f8c8d; text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:8px; border-top:1px solid rgba(255,255,255,0.04); padding-top:15px;">📜 Historial Contable del Ledger (Últimos Movimientos):</strong>';
                    $html .= '<table class="swal-table" style="margin-top:0; width:100%;">';
                    $html .= '<thead><tr>';
                    $html .= '<th style="width:90px; padding:6px 0; text-align:left;">Fecha</th>';
                    $html .= '<th style="padding:6px 4px; text-align:left;">Concepto / Descripción</th>';
                    $html .= '<th style="width:120px; padding:6px 0; text-align:right;">Flujo Bóveda</th>';
                    $html .= '<th style="width:140px; padding:6px 0; text-align:right;">Capacidad Consumida</th>';
                    $html .= '</tr></thead>';

                    $txs = DB::table('transactions')->where('user_id', $userTarget->id)->latest()->limit(10)->get();
                    if($txs->isEmpty()) {
                        $html .= '<tr><td colspan="4" style="text-align:center; color:#7f8c8d; font-style:italic; padding:20px;">No se registran transacciones asentadas en este nodo.</td></tr>';
                    } else {
                        foreach($txs as $t) {
                            $colFlujo = '<span style="color:#4a5568;">-</span>';
                            $colConsumido = '<span style="color:#4a5568;">-</span>';
                            
                            if ($t->type === 'mint') {
                                $colFlujo = '<span style="color:#3498db; font-weight:bold; font-family:\'Rajdhani\'; font-size:14.5px;">'.number_format($t->amount,0).' FC</span>';
                            } elseif ($t->type === 'consumed' || \Illuminate\Support\Str::contains(strtolower($t->description), 'consumido')) {
                                $colConsumido = '<span style="color:#e67e22; font-weight:bold; font-family:\'Rajdhani\'; font-size:14.5px;">🔥 '.number_format($t->amount,0).' FC</span>';
                            } elseif ($t->type === 'escrow') {
                                $colFlujo = '<span style="color:#f1c40f; font-weight:bold; font-family:\'Rajdhani\'; font-size:14.5px;">'.number_format($t->amount,0).' FC</span>';
                            } elseif ($t->type === 'expense') {
                                $colFlujo = '<span style="color:#e74c3c; font-weight:bold; font-family:\'Rajdhani\'; font-size:14.5px;">'.number_format($t->amount,0).' FC</span>';
                            } else {
                                $colFlujo = '<span style="color:#2ecc71; font-weight:bold; font-family:\'Rajdhani\'; font-size:14.5px;">'.number_format($t->amount,0).' FC</span>';
                            }
                            
                            $html .= '<tr>';
                            $html .= '<td style="padding:10px 0; border-bottom:1px solid rgba(255,255,255,0.03); color:#a0aec0; font-size:12px;">'.date('d/m H:i', strtotime($t->created_at)).'</td>';
                            $html .= '<td style="padding:10px 4px; border-bottom:1px solid rgba(255,255,255,0.03); color:#cbd5e0; font-size:12.5px;">'.htmlspecialchars($t->description).'</td>';
                            $html .= '<td style="padding:10px 0; border-bottom:1px solid rgba(255,255,255,0.03); text-align:right;">'.$colFlujo.'</td>';
                            $html .= '<td style="padding:10px 0; border-bottom:1px solid rgba(255,255,255,0.03); text-align:right;">'.$colConsumido.'</td>';
                            $html .= '</tr>';
                        }
                    }
                    $html .= '</table></div>';

                } else {
                    // =========================================================
                    // 👤 SECCIÓN B: COMPILACIÓN MACROECONÓMICA DEL CREATOR
                    // =========================================================
                    $saldo = DB::table('transactions')->where('user_id', $userTarget->id)->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as total")->value('total') ?? 0;
                    $ingresos = floatval(DB::table('transactions')->where('user_id', $userTarget->id)->where('type', 'income')->sum('amount'));
                    $egresos = floatval(DB::table('transactions')->where('user_id', $userTarget->id)->where('type', 'expense')->sum('amount'));

                    $cntMisionesHechas = DB::table('mission_applications')->where('creator_id', $userTarget->id)->where('is_reviewed', 1)->count();
                    $cntReservasHechas = DB::table('orders')->where('creator_id', $userTarget->id)->count();

                    // Cápsula de Actividad (Creator)
                    $html .= '<div style="margin:-12px 0 18px 0; font-size:11px; background:rgba(255,255,255,0.02); padding:6px 12px; border-radius:6px; border:1px solid rgba(255,255,255,0.04); display:flex; gap:14px; color:#cbd5e0; font-weight:500;">';
                    $html .= '<span style="color:#7f8c8d; text-transform:uppercase; font-size:9.5px; font-weight:700; display:flex; align-items:center;">📊 Actividad:</span>';
                    $html .= '<span>🎯 '.$cntMisionesHechas.' Misiones Concluidas</span><span>📅 '.$cntReservasHechas.' Reservas de Capacidad</span></div>';

                    // Corona Superior (Creator)
                    $html .= '<div style="width: 100%; text-align: center; margin-bottom: 15px;">';
                    $html .= '<div style="border: 1px solid #2ecc71; padding: 12px; border-radius: 8px; background: rgba(46,204,113,0.02);">';
                    $html .= '<span style="font-size:10px; color:#7f8c8d; text-transform:uppercase; letter-spacing:0.5px;">Saldo Líquido Disponible (Billetera)</span>';
                    $html .= '<div style="color:#2ecc71; font-size:26px; font-weight:800; font-family:\'Rajdhani\'; margin-top:2px;">🥮 '.number_format($saldo,0).' FC</div></div></div>';

                    // Caja de Flujos Contables (Creator)
                    $html .= '<div style="background: #131722; border: 1px dashed rgba(255, 255, 255, 0.06); padding: 15px; border-radius: 10px; margin-bottom: 20px;">';
                    $html .= '<div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:8px;">';
                    $html .= '<div style="background:#1c2230; padding:10px; border-radius:6px; border-bottom:2px solid #3498db;"><span style="font-size:9px; color:#a0aec0;">Total Ganado</span><br><span style="color:#3498db; font-weight:bold; font-family:\'Rajdhani\';">'.number_format($ingresos,0).' FC</span></div>';
                    $html .= '<div style="background:#1c2230; padding:10px; border-radius:6px; border-bottom:2px solid #e74c3c;"><span style="font-size:9px; color:#a0aec0;">Total Gastado</span><br><span style="color:#e74c3c; font-weight:bold; font-family:\'Rajdhani\';">'.number_format($egresos,0).' FC</span></div>';
                    $html .= '<div style="background:#1c2230; padding:10px; border-radius:6px; border-bottom:2px solid #f1c40f;"><span style="font-size:9px; color:#a0aec0;">Deuda ISA Viva</span><br><span style="color:#f1c40f; font-weight:bold; font-family:\'Rajdhani\';">'.number_format($userTarget->deuda_fc,0).' FC</span></div>';
                    $html .= '</div></div>';

                    // 📜 LEDGER TRADICIONAL DE 3 COLUMNAS SIN ALTERACIONES PARA CREADORES
                    $html .= '<strong style="font-size:11px; color:#7f8c8d; text-transform:uppercase; letter-spacing:0.5px; display:block; margin-bottom:5px;">📜 Historial Contable del Ledger (Últimos Movimientos):</strong>';
                    $html .= '<table class="swal-table" style="margin-top:0; width:100%;">';
                    $html .= '<thead><tr>';
                    $html .= '<th style="width:90px; padding:6px 0; text-align:left;">Fecha</th>';
                    $html .= '<th style="padding:6px 4px; text-align:left;">Concepto / Descripción</th>';
                    $html .= '<th style="width:140px; padding:6px 0; text-align:right;">Flujo Billetera</th>';
                    $html .= '</tr></thead>';

                    $txs = DB::table('transactions')->where('user_id', $userTarget->id)->latest()->limit(10)->get();
                    if($txs->isEmpty()) {
                        $html .= '<tr><td colspan="3" style="text-align:center; color:#7f8c8d; font-style:italic; padding:20px;">No se registran transacciones asentadas en este nodo.</td></tr>';
                    } else {
                        foreach($txs as $t) {
                            if ($t->type === 'consumed' || \Illuminate\Support\Str::contains(strtolower($t->description), 'consumido')) {
                                $color = '#e67e22'; // Naranja Capacidad Realizada / Quemada
                            } elseif ($t->type === 'expense') {
                                $color = '#e74c3c'; // Rojo Gasto / Egreso
                            } else {
                                $color = '#2ecc71'; // Verde Ingreso / Remesas Ordinarias
                            }
                            
                            $html .= '<tr>';
                            $html .= '<td style="padding:10px 0; border-bottom:1px solid rgba(255,255,255,0.03); color:#a0aec0; font-size:12px;">'.date('d/m H:i', strtotime($t->created_at)).'</td>';
                            $html .= '<td style="padding:10px 4px; border-bottom:1px solid rgba(255,255,255,0.03); color:#cbd5e0; font-size:12.5px;">'.htmlspecialchars($t->description).'</td>';
                            $html .= '<td style="padding:10px 0; border-bottom:1px solid rgba(255,255,255,0.03); text-align:right; font-weight:bold; color:'.$color.'; font-family:\'Rajdhani\'; font-size:14.5px;">'.number_format($t->amount,0).' FC</td>';
                            $html .= '</tr>';
                        }
                    }
                    $html .= '</table></div>';
                }
                
                $html .= '</div>';
                return response($html);
            }
            return response('<p class="text-center">Nodo no hallado en la red.</p>');
        }

        $html .= '</table></div>';
        return response($html);
    }
}
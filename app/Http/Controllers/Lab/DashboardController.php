<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Services\MailService;
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
        $totalActivosCount = $misActivos->whereIn('status', ['enlisted', 'active'])->count();
        $totalMaquinasCount = $misActivos->whereIn('status', ['enlisted', 'active'])->where('asset_type', 'machine')->count();
        $totalServiciosCount = $misActivos->whereIn('status', ['enlisted', 'active'])->where('asset_type', 'service')->count();
        $totalLabsConectados = $misActivos->whereIn('status', ['enlisted', 'active'])->where('asset_type', 'lab')->count();

        // 3. HUB B: Libro Contable Automatizado con Lectura Directa de Ledger
        $totalMinted = $misActivos->sum('generated_fc') ?? 0;
        $dadosDeBajaValor = $misActivos->where('status', 'retired')->sum('generated_fc') ?? 0;

        // A. 🪙 CAJA REAL EN BÓVEDA: Lee directamente el saldo líquido acumulado real en el Ledger
        $realLiquid = floatval(DB::table('transactions')
            ->where('user_id', $lab->id)
            ->whereIn('type', ['income', 'mint', 'expense', 'escrow'])
            ->selectRaw("SUM(CASE WHEN type IN ('income', 'mint') THEN amount ELSE -amount END) as total")
            ->value('total') ?? 0);

        // B. 🔒 CUSTODIA (ESCROW VIVO): Filtra las misiones y órdenes que siguen retenidas
        $congeladosReales = floatval(DB::table('missions')
            ->where('lab_id', $lab->id)
            ->whereIn('status', ['open', 'assigned'])
            ->get()
            ->sum(function($m) {
                $reviewedSpots = DB::table('mission_applications')
                    ->where('mission_id', $m->id)
                    ->where('is_reviewed', 1)
                    ->count();
                return $m->reward_fc * max(0, $m->spots_total - $reviewedSpots);
            }));

        // C. 🟢 EQUILIBRIO DE MONEDAS RESIDUAL: El circulante es el residuo exacto de la masa monetaria
        // Esto garantiza simetría total con el SuperAdmin y un cierre del 100% en el Donut Chart
        $enCirculacion = max(0, $totalMinted - $realLiquid - $congeladosReales);

        // F. Contador Estadístico Independiente Neutral: Servicios Liquidados / Quemados
        $realConsumed = DB::table('transactions')->where('user_id', $lab->id)->where('type', 'consumed')->sum('amount');

        // Mapeo estricto de variables de compatibilidad para el Blade
        $enReserva = $realLiquid;
        $saldoTotal = $realLiquid;
        $isFrozen = ($realLiquid < 0);
        $escrowRealMisiones = $congeladosReales;
        $ofertadosTotal = $congeladosReales;
        $ofertadosCongelados = $congeladosReales;
        $totalHistoricoQuemado = $realConsumed;
        $totalHistoricoEmitido = $totalMinted;
        $historicoPagadoMisiones = $enCirculacion; // 🎯 ALIAS ASEGURADO

        // 4. HUB C: Métricas Agrupadas de la Bolsa de Trabajo
        $totalMisionesCount = $misMisiones->count();
        $statsMisiones = [
            'completadas'  => $misMisiones->where('status', 'completed')->count(),
            'en_ejecucion' => $misMisiones->filter(fn($m) => 
                $m->status === 'assigned' || ($m->status === 'open' && $m->spots_filled > 0)
            )->count(),
            'abiertas'     => $misMisiones->filter(fn($m) => 
                $m->status === 'open' && $m->spots_filled == 0
            )->count(),
            'por_aceptar'  => DB::table('mission_applications')
                                ->join('missions', 'mission_applications.mission_id', '=', 'missions.id')
                                ->where('missions.lab_id', $lab->id)
                                ->where('mission_applications.status', 'pending')
                                ->count(),
        ];

        // 5. Estructuras Secundarias Operativas (Tablas Internas)
        $misReservas = DB::table('orders')
            ->join('users', 'orders.creator_id', '=', 'users.id')
            ->join('lab_assets', 'orders.asset_id', '=', 'lab_assets.id')
            ->where('lab_assets.lab_id', $lab->id)
            ->select('orders.*', 'users.name as creator_name', 'users.slug as creator_slug', 'lab_assets.custom_name')
            ->orderBy('orders.created_at', 'desc')
            ->get();

        $misFinanciamientos = DB::table('financing_agreements')
            ->join('users', 'financing_agreements.creator_id', '=', 'users.id')
            ->where('financing_agreements.lab_id', $lab->id)
            ->whereIn('financing_agreements.status', ['pending', 'active'])
            ->select('financing_agreements.*', 'users.name as creator_name', 'users.slug as creator_slug', 'users.email as creator_email')
            ->orderBy('financing_agreements.created_at', 'desc')
            ->get();

        $creatorsExplorador = User::where('role', 'creator')
            ->select('users.*')
            ->selectRaw('(SELECT COUNT(*) FROM reviews WHERE reviews.reviewee_id = users.id) as total_resenas')
            ->selectRaw('(SELECT COUNT(*) FROM mission_applications WHERE mission_applications.creator_id = users.id AND mission_applications.status = "accepted") as misiones_completadas')
            ->orderBy('reputation_score', 'desc')
            ->get();
        
        $postulantesData = DB::table('mission_applications')
            ->join('users', 'mission_applications.creator_id', '=', 'users.id')
            ->join('missions', 'mission_applications.mission_id', '=', 'missions.id')
            ->where('missions.lab_id', $lab->id)
            ->select([
                'mission_applications.*', 
                'users.name as creator_name', 
                'users.slug as creator_slug', 
                'users.reputation_score',
                'users.deuda_fc',             
                'users.deuda_lab_id'          
            ])
            ->get();

        $postulantesPorMision = [];
        foreach ($postulantesData as $p) { 
            $postulantesPorMision[$p->mission_id][] = $p; 
        }

        // 6. Sistema de Alertas
        $notificaciones = DB::table('notifications')->where('user_id', $lab->id)->latest()->limit(10)->get();
        $unread_count = $notificaciones->where('is_read', false)->count();

        $totalFinanciados = $misFinanciamientos->where('status', 'active')->count(); 
        $totalPorCobrar = $misFinanciamientos->sum('amount_remaining') ?? 0; 
        $creatorSkills = [];

        return view('lab.dashboard', compact(
            'lab', 'misActivos', 'activosFisicos', 'misMisiones', 'misTransacciones', 'misReservas', 
            'misFinanciamientos', 'creatorsExplorador', 'creatorSkills', 'postulantesPorMision', 
            'notificaciones', 'unread_count', 'totalActivosCount', 'totalMaquinasCount', 
            'totalServiciosCount', 'totalLabsConectados', 'totalMinted', 'ofertadosCongelados', 
            'enReserva', 'statsMisiones', 'totalMisionesCount', 'dadosDeBajaValor', 'escrowRealMisiones', 
            'historicoPagadoMisiones', 'ofertadosTotal', 'saldoTotal', 'isFrozen', 'totalHistoricoEmitido', 
            'totalHistoricoQuemado', 'totalFinanciados', 'totalPorCobrar',
            'realLiquid', 'enCirculacion', 'congeladosReales', 'realConsumed'
        ));
    }

    public function readNotifications()
    {
        DB::table('notifications')->where('user_id', auth()->id())->update(['is_read' => true]);
        return redirect()->route('lab.dashboard');
    }

    /**
     * 👤 GUARDAR PERFIL DEL LAB Y COORDENADAS DEL MAPA (HUB 1)
     */
    public function updateProfile(Request $request)
    {
        $labId = auth()->id();

        // Conservamos tu regla exacta de limpieza de etiquetas permitidas para la Bio
        $nuevaBio = strip_tags($request->input('bio'), '<b><strong><i><em><u><ul><li><ol><br><p>');
        
        try {
            DB::table('users')->where('id', $labId)->update([
                'bio'               => $nuevaBio,
                'address'           => trim($request->input('address')),
                'phone'             => $request->input('phone') ? preg_replace('/\s+/', '', $request->input('phone')) : null, 
                'social_fabacademy' => trim($request->input('social_fabacademy')),
                'social_linkedin'   => trim($request->input('social_linkedin')),
                'social_github'     => trim($request->input('social_github')),
                'social_portfolio'  => trim($request->input('social_portfolio')),
                'social_instagram'  => trim($request->input('social_instagram')),
                'latitude'          => $request->input('latitude') ? floatval($request->input('latitude')) : null,
                'longitude'         => $request->input('longitude') ? floatval($request->input('longitude')) : null,
                'updated_at'        => now()
            ]);

            // 📨 TRIGGER DE SEGURIDAD: Despacha el aviso de modificación al correo institucional
            MailService::notificarActualizacionPerfil(auth()->user()->email, auth()->user()->name);

            return redirect()->route('lab.dashboard')->with('msg', 'profile_updated');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * 📊 EXPORTAR ESTADO DE CUENTA A CSV COMERCIAL (CON NÚMEROS NATURALEZAS PARA EXCEL)
     */
    public function exportCSV()
    {
        $lab = auth()->user();
        $fileName = 'Estado_Cuenta_Lab_' . date('Ymd_His') . '.csv';
        
        $headers = [
            "Content-type"        => "text/csv; charset=utf-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $callback = function() use ($lab) {
            $file = fopen('php://output', 'w');
            
            // Inyectar el BOM UTF-8 exacto para soporte de eñes y tildes en Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Encabezados con doble columna financiera
            if (app()->getLocale() === 'en') {
                fputcsv($file, ['Date and Time', 'Concept / Description', 'Operation Type', 'Liquid Cash (FC)', 'Consumed (FC)']);
            } else {
                fputcsv($file, ['Fecha y Hora', 'Concepto / Descripción', 'Tipo de Operación', 'Caja (FC)', 'Consumido (FC)']);
            }

            // Consulta al libro contable ordenado por fecha descendente
            $transactions = DB::table('transactions')
                ->where('user_id', $lab->id)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($transactions as $row) {
                $tipo_texto = $row->type;
                
                // Homogenización de nombres de operación
                if (app()->getLocale() === 'es') {
                    if ($row->type === 'income') $tipo_texto = 'Circulación [INCOME]';
                    elseif ($row->type === 'expense') $tipo_texto = 'Egreso (Gasto)';
                    elseif ($row->type === 'mint') $tipo_texto = 'Emisión Base [MINT]';
                    elseif ($row->type === 'escrow') $tipo_texto = 'En Custodia [ESCROW]';
                    elseif ($row->type === 'consumed') $tipo_texto = 'Consumido [CONSUMED]';
                    elseif ($row->type === 'info') $tipo_texto = 'Bitácora [INFO]';
                } else {
                    if ($row->type === 'income') $tipo_texto = 'Circulating [INCOME]';
                    elseif ($row->type === 'expense') $tipo_texto = 'Expense';
                    elseif ($row->type === 'mint') $tipo_texto = 'Base Emit [MINT]';
                    elseif ($row->type === 'escrow') $tipo_texto = 'Guarantee [ESCROW]';
                    elseif ($row->type === 'consumed') $tipo_texto = 'Consumed [CONSUMED]';
                    elseif ($row->type === 'info') $tipo_texto = 'Log [INFO]';
                }

                $monto_caja = '';
                $monto_consumido = '';

                if ($row->type === 'info') {
                    $monto_caja = 'REGISTRO';
                } elseif ($row->type === 'consumed') {
                    // 🚀 CORRECCIÓN: Quitamos el '+' para que Excel lo detecte como Float/Número Puro
                    $monto_consumido = number_format($row->amount, 2, '.', '');
                } else {
                    // Entradas y salidas estándar de liquidez
                    if (in_array($row->type, ['income', 'mint'])) {
                        // 🚀 CORRECCIÓN: Los ingresos van sin el signo '+' al inicio
                        $monto_caja = number_format($row->amount, 2, '.', '');
                    } else {
                        // Los egresos mantienen el '-' porque Excel sí entiende números negativos nativos
                        $monto_caja = '-' . number_format($row->amount, 2, '.', '');
                    }
                }

                fputcsv($file, [
                    date('d/m/Y H:i:s', strtotime($row->created_at)),
                    $row->description,
                    $tipo_texto,
                    $monto_caja,
                    $monto_consumido
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
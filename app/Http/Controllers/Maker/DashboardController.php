<?php

namespace App\Http\Controllers\Maker;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * 📊 ACCIÓN 1: CARGAR EL DASHBOARD DEL MAKER
     */
    public function index()
    {
        $maker = auth()->user();

        // Obtener el saldo usando tu Atributo Mágico del Modelo User
        $saldoTotal = $maker->saldo_total;

        // Historial de transacciones del Maker (Límite 50 registros)
        $misTransacciones = $maker->transacciones()->latest()->limit(50)->get();

        // Obtener sus postulaciones activas usando las consultas dinámicas de tu maker_controller
        $misPostulaciones = DB::table('mission_applications')
            ->join('missions', 'mission_applications::mission_id', '=', 'missions.id')
            ->join('users', 'missions.lab_id', '=', 'users.id')
            ->where('mission_applications.maker_id', $maker->id)
            ->select('mission_applications.*', 'missions.title', 'missions.reward_fc', 'missions.status as mission_status', 'missions.deadline', 'users.name as lab_name', 'users.slug as lab_slug')
            ->orderBy('mission_applications.applied_at', 'desc')
            ->get();

        // Bolsa de Trabajo: Misiones abiertas a las que NO se ha postulado aún
        $misionesDisponibles = Mission::where('status', 'open')
            ->where(function($query) use ($maker) {
                $query->whereNull('target_maker_id')
                      ->orWhere('target_maker_id', $maker->id);
            })
            ->whereNotIn('id', function($query) use ($maker) {
                $query->select('mission_id')->from('mission_applications')->where('maker_id', $maker->id);
            })
            ->latest()
            ->get();

        // Mercado Global de Máquinas: Traer todos los activos operativos de la red
        $recursosMercado = DB::table('lab_assets')
            ->join('global_catalog', 'lab_assets.catalog_id', '=', 'global_catalog.id')
            ->join('users', 'lab_assets.lab_id', '=', 'users.id')
            ->where('lab_assets.status', 'active')
            ->whereRaw('(lab_assets.useful_life_hours - lab_assets.consumed_hours) > 0')
            ->select('lab_assets.*', 'global_catalog.generic_name as display_name', 'users.name as lab_name', 'users.slug as lab_slug', 'users.address as lab_address')
            ->get();

        return view('maker.dashboard', compact(
            'maker', 'saldoTotal', 'misTransacciones', 'misPostulaciones', 'misionesDisponibles', 'recursosMercado'
        ));
    }

    /**
     * 💸 ACCIÓN 2: TRANSFERENCIA P2P (Enviar FabCoins directos a otro Maker)
     */
    public function transferP2P(Request $request)
    {
        $miUsuario = auth()->user();

        // Validaciones estrictas de seguridad de Laravel
        $request->validate([
            'dest_email' => 'required|email',
            'monto_p2p' => 'required|numeric|min:0.10',
        ]);

        $emailDestino = trim($request->input('dest_email'));
        $monto = floatval($request->input('monto_p2p'));

        // 1. Buscar al Maker receptor en la base de datos
        $receptor = User::where('email', $emailDestino)->where('role', 'maker')->first();

        if (!$receptor) {
            return redirect()->back()->with('error', 'Usuario no encontrado o no es un Maker especialista.');
        }

        if ($receptor->id === $miUsuario->id) {
            return redirect()->back()->with('error', 'Operación inválida: No puedes enviarte FabCoins a ti mismo.');
        }

        if ($monto > $miUsuario->saldo_total) {
            return redirect()->back()->with('error', 'Saldo insuficiente para realizar la transferencia P2P.');
        }

        try {
            // Libro contable P2P blindado en una transacción atómica
            DB::transaction(function () use ($miUsuario, $receptor, $monto) {
                
                // A. Registrar el EGRESO en la cuenta del emisor
                $miUsuario->transacciones()->create([
                    'description' => "Envío P2P a " . $receptor->name,
                    'amount' => $monto,
                    'type' => 'expense'
                ]);

                // B. Registrar el INGRESO en la cuenta del receptor
                $receptor->transacciones()->create([
                    'description' => "Recibido P2P de " . $miUsuario->name,
                    'amount' => $monto,
                    'type' => 'income'
                ]);

                // C. Inyectar notificaciones internas en la tabla (Campanitas)
                DB::table('notifications')->insert([
                    ['user_id' => $receptor->id, 'message' => "💰 Has recibido $monto FC de " . $miUsuario->name, 'type' => 'success', 'created_at' => now()],
                    ['user_id' => $miUsuario->id, 'message' => "💸 Enviaste $monto FC a " . $receptor->name, 'type' => 'info', 'created_at' => now()]
                ]);
            });

            return redirect()->route('maker.dashboard')->with('msg', 'p2p_ok');

        } catch (\Exception $e) {
            return redirect()->route('maker.dashboard')->with('error', 'Error en la transferencia: ' . $e->getMessage());
        }
    }
}
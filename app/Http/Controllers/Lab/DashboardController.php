<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * 📊 ACCIÓN 1: CARGAR EL DASHBOARD DEL LAB (Reemplaza tu consulta de saldo y KPIs)
     */
    public function index()
    {
        $lab = auth()->user();

        // Usamos las relaciones Eloquent automáticas que creamos en la Fase 2
        $misActivos = $lab->activos()->with('categoriaGlobal')->get();
        $misMisiones = $lab->misiones()->latest()->get();

        // Saldo neto (Calculado automáticamente por el atributo mágico que programamos en tu Modelo User)
        $saldoTotal = $lab->saldo_total;
        $isFrozen = ($saldoTotal < 0);

        // KPIs económicos rápidos usando agregadores de Laravel
        $totalFinanciados = DB::table('financing_agreements')->where('lab_id', $lab->id)->where('status', 'active')->count();
        $totalHistoricoEmitido = $lab->activos()->sum('generated_fc');
        
        // Pasamos los datos limpios a la vista de Blade (la cual diseñaremos en la Fase 4)
        return view('lab.dashboard', compact(
            'lab', 'misActivos', 'misMisiones', 'saldoTotal', 'isFrozen', 'totalFinanciados', 'totalHistoricoEmitido'
        ));
    }

    /**
     * 🚀 ACCIÓN 2: TOKENIZACIÓN DE ACTIVOS (Emisión de FabCoins por preventa de capacidad)
     */
    public function tokenize(Request $request)
    {
        $lab = auth()->user();
        
        // Validación express nativa de Laravel (reemplaza tus validaciones manuales)
        $request->validate([
            'custom_name' => 'required|array',
            'set_price_fc' => 'required|array',
        ]);

        try {
            // Tu lógica original de base de datos blindada con seguridad atómica de Laravel
            DB::transaction(function () use ($request, $lab) {
                $names = $request->input('custom_name');
                $prices = $request->input('set_price_fc');
                $catalogIds = $request->input('catalog_id');
                $types = $request->input('asset_type');
                $quantities = $request->input('quantity_offered');
                
                $globalPct = 35; // Política monetaria referencial fijada por tu SuperAdmin
                $horasVidaUtilContrato = 4160;
                $fechaExpiracion = now()->addYears(2);

                for ($i = 0; $i < count($names); $i++) {
                    $precio = floatval($prices[$i]);
                    
                    if (!empty(trim($names[$i])) && $precio > 0) {
                        $tipo = $types[$i];
                        
                        // Ecuación macroeconómica original de FabCoins según el tipo de activo
                        $cantidadGuardada = ($tipo === 'machine') ? ($horasVidaUtilContrato * ($globalPct / 100)) : floatval($quantities[$i]);
                        $montoGenerar = $cantidadGuardada * $precio;

                        if ($montoGenerar <= 0) continue;

                        // 1. Guardar el activo en el inventario del Lab usando Eloquent
                        $lab->activos()->create([
                            'catalog_id' => $catalogIds[$i],
                            'asset_type' => $tipo,
                            'custom_name' => trim($names[$i]),
                            'useful_life_hours' => $cantidadGuardada,
                            'tokenization_pct' => ($tipo === 'machine') ? $globalPct : 100,
                            'set_price_fc' => $precio,
                            'generated_fc' => $montoGenerar,
                            'expires_at' => $fechaExpiracion,
                        ]);

                        // 2. Registrar el movimiento de emisión en tu libro contable
                        $lab->transacciones()->create([
                            'description' => "Emisión (Mint): " . trim($names[$i]),
                            'amount' => $montoGenerar,
                            'type' => 'mint'
                        ]);
                    }
                }
            ]);

            // Redirección elegante con un "Mensaje Flash" que SweetAlert leerá en la vista
            return redirect()->route('lab.dashboard')->with('msg', 'mint_ok');

        } catch (\Exception $e) {
            return redirect()->route('lab.dashboard')->with('error', 'Error en la tokenización: ' . $e->getMessage());
        }
    }
}
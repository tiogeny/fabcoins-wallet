<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * 📊 DASHBOARD DEL LAB
     */
    public function index()
    {
        $lab = auth()->user();

        $misActivos = $lab->activos()->with('categoriaGlobal')->get();
        $misMisiones = $lab->misiones()->latest()->get();

        $saldoTotal = $lab->saldo_total;
        $isFrozen = ($saldoTotal < 0);

        $totalFinanciados = DB::table('financing_agreements')->where('lab_id', $lab->id)->where('status', 'active')->count();
        $totalHistoricoEmitido = $lab->activos()->sum('generated_fc');
        
        return view('lab.dashboard', compact(
            'lab', 'misActivos', 'misMisiones', 'saldoTotal', 'isFrozen', 'totalFinanciados', 'totalHistoricoEmitido'
        ));
    }

    /**
     * 🚀 TOKENIZACIÓN (MINT)
     */
    public function tokenize(Request $request)
    {
        $lab = auth()->user();
        
        $request->validate([
            'custom_name' => 'required|array',
            'set_price_fc' => 'required|array',
        ]);

        try {
            DB::transaction(function () use ($request, $lab) {
                $names = $request->input('custom_name');
                $prices = $request->input('set_price_fc');
                $catalogIds = $request->input('catalog_id');
                $types = $request->input('asset_type');
                $quantities = $request->input('quantity_offered');
                
                $globalPct = 35;
                $horasVidaUtilContrato = 4160;
                $fechaExpiracion = now()->addYears(2);

                for ($i = 0; $i < count($names); $i++) {
                    $precio = floatval($prices[$i]);
                    
                    if (!empty(trim($names[$i])) && $precio > 0) {
                        $tipo = $types[$i];
                        $cantidadGuardada = ($tipo === 'machine') ? ($horasVidaUtilContrato * ($globalPct / 100)) : floatval($quantities[$i]);
                        $montoGenerar = $cantidadGuardada * $precio;

                        if ($montoGenerar <= 0) {
                            continue;
                        }

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

                        $lab->transacciones()->create([
                            'description' => "Emisión (Mint): " . trim($names[$i]),
                            'amount' => $montoGenerar,
                            'type' => 'mint'
                        ]);
                    }
                }
            }); // 👈 ¡CORREGIDO AQUÍ! Cambiado ]; por };

            return redirect()->route('lab.dashboard')->with('msg', 'mint_ok');

        } catch (\Exception $e) {
            return redirect()->route('lab.dashboard')->with('error', 'Error en la tokenización: ' . $e->getMessage());
        }
    }
}
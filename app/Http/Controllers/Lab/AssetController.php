<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    /**
     * 🏢 ENLISTAR INFRAESTRUCTURA EN EL INVENTARIO (ESTADO BASE ENLISTED)
     */
    public function store(Request $request)
    {
        $lab = auth()->user();
        
        try {
            DB::transaction(function () use ($request, $lab) {
                $types = $request->input('asset_type');
                $catalogIds = $request->input('catalog_id');
                $names = $request->input('custom_name');
                $quantities = $request->input('quantity_declared');
                
                if (!$names || count($names) == 0) {
                    return;
                }

                for ($i = 0; $i < count($names); $i++) {
                    $cantidad = floatval($quantities[$i]);
                    
                    if (!empty(trim($names[$i])) && $cantidad > 0 && !empty($catalogIds[$i])) {
                        
                        $subcategoria = null;
                        if ($types[$i] === 'service') {
                            $catalogItem = DB::table('global_catalog')->where('id', $catalogIds[$i])->first();
                            if ($catalogItem && str_contains(strtolower($catalogItem->generic_name), 'taller')) {
                                $subcategoria = 'workshop';
                            } else {
                                $subcategoria = 'mentorship';
                            }
                        }

                        DB::table('lab_assets')->insert([
                            'lab_id'            => $lab->id,
                            'catalog_id'        => $catalogIds[$i],
                            'asset_type'        => $types[$i],
                            'subcategory'       => $subcategoria,
                            'custom_name'       => trim($names[$i]),
                            'useful_life_hours' => $cantidad,
                            'consumed_hours'    => 0.00,
                            'tokenization_pct'  => 0,
                            'generated_fc'      => 0.00,
                            'status'            => 'enlisted',
                            'set_price_fc'      => 0.00,
                            'expires_at'        => now()->addYears(2),
                            'created_at'        => now(),
                            'updated_at'        => now()
                        ]);
                    }
                }
            }); // 🔥 CORRECCIÓN: Cerrado correctamente con }) en lugar de ]

            return redirect()->route('lab.dashboard')->with('msg', 'asset_enlisted_ok');
        } catch (\Exception $e) {
            return redirect()->route('lab.dashboard')->with('error', $e->getMessage());
        }
    }

    /**
     * 🗑️ ELIMINACIÓN SEGURA DE INFRAESTRUCTURA NO TOKENIZADA
     */
    public function destroy($id)
    {
        try {
            $activo = DB::table('lab_assets')->where('id', $id)->where('lab_id', auth()->id())->first();
            
            if (!$activo || $activo->status !== 'enlisted') {
                return redirect()->back()->with('error', 'No se puede eliminar un activo comprometido en el Ledger.');
            }
            
            DB::table('lab_assets')->where('id', $id)->delete();
            
            // 🔥 CORRECCIÓN: Cambiado de 'profile_updated' a 'asset_deleted_ok'
            return redirect()->route('lab.dashboard')->with('msg', 'asset_deleted_ok'); 
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * 🪙 TRANSFORMACIÓN CONTABLE DE ACTIVOS EN FABCOINS (MINT GENERAL VINCULANTE - BLINDADO)
     */
    public function tokenise(Request $request)
    {
        // 1. Validar la estructura base de los arreglos entrantes
        $request->validate([
            'transformar_activo'   => 'required|array',
            'set_price_fc'         => 'required|array',
        ]);

        $lab = auth()->user();
        $activosSeleccionados = $request->input('transformar_activo');
        $preciosAjustados = $request->input('set_price_fc');

        // 2. Extraer de forma segura la política de emisión oficial de la red
        $globalPctSetting = DB::table('global_settings')->where('setting_key', 'tokenization_pct')->value('setting_value');
        $pctOficial = $globalPctSetting ? floatval($globalPctSetting) / 100 : 0.20; // Fallback seguro al 20%

        try {
            DB::transaction(function () use ($activosSeleccionados, $preciosAjustados, $pctOficial, $lab) {
                foreach ($activosSeleccionados as $id) {
                    $asset = DB::table('lab_assets')->where('id', $id)->where('lab_id', $lab->id)->first();
                    
                    if ($asset && $asset->status === 'enlisted') {
                        // Forzamos a que el precio sea positivo y le ponemos un techo prudente de control
                        $precio = abs(floatval($preciosAjustados[$id] ?? 10.00));
                        if ($precio > 1000.00) { $precio = 1000.00; } // Candado preventivo contra inflaciones inducidas
                        
                        // 🎯 REGLA DE INFRAESTRUCTURA: El porcentaje viene de la Gobernanza Central, NO del cliente
                        $montoGenerar = ($asset->useful_life_hours * $pctOficial) * $precio;

                        // Actualización de estado en el inventario real
                        DB::table('lab_assets')->where('id', $id)->update([
                            'status'           => 'active',
                            'tokenization_pct' => intval($pctOficial * 100),
                            'set_price_fc'     => $precio,
                            'generated_fc'     => $montoGenerar,
                            'updated_at'       => now()
                        ]);

                        // Asentamiento inmutable en el Ledger histórico
                        DB::table('transactions')->insert([
                            'user_id'     => $lab->id,
                            'description' => "Transformación (Mint): " . $asset->custom_name . " (" . intval($pctOficial * 100) . "%)",
                            'amount'      => $montoGenerar,
                            'type'        => 'mint',
                            'created_at'  => now(),
                            'updated_at'  => now()
                        ]);
                    }
                }
            });

            // 🚀 TRIGGER ASENTADO: Alerta al laboratorio de su emisión base con éxito
            MailService::notificarTokenizacion($lab->email, $lab->name, 'Lote de infraestructura tokenizado', $request->input('transformar_activo'));

            return redirect()->route('lab.dashboard')->with('msg', 'mint_ok');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Fallo en la verificación del Ledger: ' . $e->getMessage());
        }
    }

    /**
     * 📈 ACTUALIZAR PRECIO COMERCIAL DE ALQUILER EN EL MERCADO
     */
    public function updatePrice(Request $request)
    {
        try {
            DB::table('lab_assets')
                ->where('id', $request->input('asset_id'))
                ->where('lab_id', auth()->id())
                ->where('status', 'active')
                ->update([
                    'set_price_fc' => floatval($request->input('nuevo_precio')),
                    'updated_at'   => now()
                ]);

            return redirect()->route('lab.dashboard')->with('msg', 'price_ok');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
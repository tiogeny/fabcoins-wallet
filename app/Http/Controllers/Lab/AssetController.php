<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
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
}
<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\LabAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetController extends Controller
{
    public function tokenize(Request $request)
    {
        $lab = auth()->user();
        try {
            DB::transaction(function () use ($request, $lab) {
                $names = $request->input('custom_name');
                $prices = $request->input('set_price_fc');
                $catalogIds = $request->input('catalog_id');
                $types = $request->input('asset_type');
                $quantities = $request->input('quantity_offered');
                
                for ($i = 0; $i < count($names); $i++) {
                    $precio = floatval($prices[$i]);
                    if (!empty(trim($names[$i])) && $precio > 0 && !empty($catalogIds[$i])) {
                        $tipo = $types[$i];
                        $cantidadGuardada = ($tipo === 'machine') ? (4160 * 0.35) : floatval($quantities[$i]);
                        $montoGenerar = $cantidadGuardada * $precio;

                        if ($cantidadGuardada <= 0 || $montoGenerar <= 0) continue;

                        $lab->activos()->create([
                            'catalog_id' => $catalogIds[$i], 'asset_type' => $tipo, 'custom_name' => trim($names[$i]),
                            'useful_life_hours' => $cantidadGuardada, 'tokenization_pct' => ($tipo === 'machine') ? 35 : 100,
                            'set_price_fc' => $precio, 'generated_fc' => $montoGenerar, 'expires_at' => now()->addYears(2),
                        ]);

                        $lab->transacciones()->create(['description' => "Emisión (Mint): " . trim($names[$i]), 'amount' => $montoGenerar, 'type' => 'mint']);
                    }
                }
            ]);
            return redirect()->route('lab.dashboard')->with('msg', 'mint_ok');
        } catch (\Exception $e) {
            return redirect()->route('lab.dashboard')->with('error', $e->getMessage());
        }
    }

    public function retireAsset(Request $request)
    {
        $lab = auth()->user();
        $asset = LabAsset::where('id', $request->input('asset_id'))->where('lab_id', $lab->id)->firstOrFail();

        if ($asset->status === 'active') {
            DB::transaction(function () use ($lab, $asset) {
                $asset->update(['status' => 'retired']);
                $lab->transacciones()->create(['description' => "PENALIZACIÓN: Retiro de activo " . $asset->custom_name, 'amount' => $asset->generated_fc, 'type' => 'expense']);
            });
            return redirect()->route('lab.dashboard')->with('msg', 'retired_ok');
        }
        return redirect()->route('lab.dashboard');
    }

    public function updatePrice(Request $request)
    {
        LabAsset::where('id', $request->input('asset_id'))->where('lab_id', auth()->id())->firstOrFail()->update(['set_price_fc' => floatval($request->input('nuevo_precio'))]);
        return redirect()->route('lab.dashboard')->with('msg', 'price_ok');
    }
}
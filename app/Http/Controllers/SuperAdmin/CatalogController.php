<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CatalogController extends Controller
{
    public function storeMultiple(Request $request)
    {
        $request->validate(['generic_name_es' => 'required|array', 'suggested_price_fc' => 'required|array']);

        $types = $request->input('asset_type');
        $names_es = $request->input('generic_name_es');
        $names_en = $request->input('generic_name_en');
        $units = $request->input('measurement_unit');
        $prices = $request->input('suggested_price_fc');

        DB::transaction(function () use ($types, $names_es, $names_en, $units, $prices) {
            for ($i = 0; $i < count($names_es); $i++) {
                if (!empty(trim($names_es[$i]))) {
                    DB::table('global_catalog')->insert([
                        'generic_name' => trim($names_es[$i]),
                        'generic_name_en' => trim($names_en[$i]),
                        'asset_type' => $types[$i],
                        'suggested_price_fc' => floatval($prices[$i]),
                        'measurement_unit' => trim($units[$i])
                    ]);
                }
            }
        });

        return redirect()->route('superadmin.dashboard')->with('msg', 'cat_ok');
    }

    public function updatePrice(Request $request)
    {
        DB::table('global_catalog')
            ->where('id', $request->input('cat_id'))
            ->update(['suggested_price_fc' => floatval($request->input('nuevo_precio'))]);

        return redirect()->route('superadmin.dashboard')->with('msg', 'precio_ok');
    }

    public function destroy(Request $request)
    {
        DB::table('global_catalog')->where('id', $request->input('cat_id'))->delete();
        return redirect()->route('superadmin.dashboard')->with('msg', 'borrado_ok');
    }
}
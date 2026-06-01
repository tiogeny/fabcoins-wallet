<?php

namespace App\Http\Controllers\Maker;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function apply(Request $request)
    {
        $maker = auth()->user();
        $misionId = $request->input('mission_id');

        $existe = DB::table('mission_applications')->where('mission_id', $misionId)->where('maker_id', $maker->id)->exists();
        if ($existe) {
            return redirect()->route('maker.dashboard')->with('error', "Ya te has postulado a esta misión.");
        }

        DB::transaction(function() use ($maker, $misionId, $request) {
            DB::table('mission_applications')->insert([
                'mission_id' => $misionId, 'maker_id' => $maker->id,
                'message' => trim($request->input('message')), 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()
            ]);

            $mision = DB::table('missions')->where('id', $misionId)->first();
            DB::table('notifications')->insert([
                'user_id' => $mision->lab_id,
                'message' => "El Maker " . $maker->name . " se ha postulado a la misión: " . $mision->title,
                'type' => 'info', 'created_at' => now()
            ]);
        ]);

        return redirect()->route('maker.dashboard')->with('msg', 'applied_ok');
    }

    public function signCredit(Request $request)
    {
        $maker = auth()->user();
        $contractId = $request->input('contract_id');

        $contract = DB::table('financing_agreements')->where('id', $contractId)->where('maker_id', $maker->id)->where('status', 'pending')->first();

        if ($contract) {
            DB::transaction(function() use ($maker, $contract) {
                DB::table('financing_agreements')->where('id', $contract->id)->update(['status' => 'active']);
                $maker->update(['deuda_fc' => $contract->amount_initial, 'deuda_inicial_fc' => $contract->amount_initial, 'deuda_lab_id' => $contract->lab_id]);
            });
            return redirect()->route('maker.dashboard')->with('msg', 'credit_accepted');
        }
        return redirect()->route('maker.dashboard');
    }

    public function transferP2P(Request $request)
    {
        $maker = auth()->user();
        $emailDestino = trim($request->input('dest_email'));
        $monto = floatval($request->input('monto_p2p'));

        $receptor = DB::table('users')->where('email', $emailDestino)->where('role', 'maker')->first();

        if (!$receptor) return redirect()->route('maker.dashboard')->with('error', "Usuario destinatario no encontrado en la red Maker.");
        if ($receptor->id === $maker->id) return redirect()->route('maker.dashboard')->with('error', "Operación inválida: No puedes enviarte fondos a ti mismo.");

        // Validar Disponibilidad líquida en cuenta
        $querySaldo = DB::select("SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as saldo FROM transactions WHERE user_id = ?", [$maker->id]);
        if (($querySaldo[0]->saldo ?? 0) < $monto) return redirect()->route('maker.dashboard')->with('error', "Fondos insuficientes en billetera.");

        DB::transaction(function() use ($maker, $receptor, $monto) {
            DB::table('transactions')->insert(['user_id' => $maker->id, 'description' => "Envío P2P a " . $receptor->name, 'amount' => $monto, 'type' => 'expense', 'created_at' => now()]);
            DB::table('transactions')->insert(['user_id' => $receptor->id, 'description' => "Recibido P2P de " . $maker->name, 'amount' => $monto, 'type' => 'income', 'created_at' => now()]);
            DB::table('notifications')->insert(['user_id' => $receptor->id, 'message' => "💰 Has recibido $monto FC de " . $maker->name, 'type' => 'success', 'created_at' => now()]);
        });

        return redirect()->route('maker.dashboard')->with('msg', 'p2p_ok');
    }

    /**
     * API de validación en vivo para el buscador dinámico de remesas
     */
    public function checkEmailP2P(Request $request)
    {
        $name = DB::table('users')->where('email', trim($request->query('email')))->where('role', 'maker')->value('name');
        return response()->json(['name' => $name ?: 'NOT_FOUND']);
    }
}
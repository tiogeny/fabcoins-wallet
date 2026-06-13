<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function apply(Request $request)
    {
        $creator = auth()->user();
        $misionId = $request->input('mission_id');

        $existe = DB::table('mission_applications')->where('mission_id', $misionId)->where('creator_id', $creator->id)->exists();
        if ($existe) {
            return redirect()->route('creator.dashboard')->with('error', "Ya te has postulado a esta misión.");
        }

        DB::transaction(function() use ($creator, $misionId, $request) {
            DB::table('mission_applications')->insert([
                'mission_id' => $misionId, 'creator_id' => $creator->id,
                'message' => trim($request->input('message')), 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()
            ]);

            $mision = DB::table('missions')->where('id', $misionId)->first();
            DB::table('notifications')->insert([
                'user_id' => $mision->lab_id,
                'message' => "El Creator " . $creator->name . " se ha postulado a la misión: " . $mision->title,
                'type' => 'info', 'created_at' => now()
            ]);
        ]);

        return redirect()->route('creator.dashboard')->with('msg', 'applied_ok');
    }

    public function signCredit(Request $request)
    {
        $creator = auth()->user();
        $contractId = $request->input('contract_id');

        $contract = DB::table('financing_agreements')->where('id', $contractId)->where('creator_id', $creator->id)->where('status', 'pending')->first();

        if ($contract) {
            DB::transaction(function() use ($creator, $contract) {
                DB::table('financing_agreements')->where('id', $contract->id)->update(['status' => 'active']);
                $creator->update(['deuda_fc' => $contract->amount_initial, 'deuda_inicial_fc' => $contract->amount_initial, 'deuda_lab_id' => $contract->lab_id]);
            });
            return redirect()->route('creator.dashboard')->with('msg', 'credit_accepted');
        }
        return redirect()->route('creator.dashboard');
    }

    public function transferP2P(Request $request)
    {
        $creator = auth()->user();
        $emailDestino = trim($request->input('dest_email'));
        $monto = floatval($request->input('monto_p2p'));

        $receptor = DB::table('users')->where('email', $emailDestino)->where('role', 'creator')->first();

        if (!$receptor) return redirect()->route('creator.dashboard')->with('error', "Usuario destinatario no encontrado en la red Creator.");
        if ($receptor->id === $creator->id) return redirect()->route('creator.dashboard')->with('error', "Operación inválida: No puedes enviarte fondos a ti mismo.");

        // Validar Disponibilidad líquida en cuenta
        $querySaldo = DB::select("SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as saldo FROM transactions WHERE user_id = ?", [$creator->id]);
        if (($querySaldo[0]->saldo ?? 0) < $monto) return redirect()->route('creator.dashboard')->with('error', "Fondos insuficientes en billetera.");

        DB::transaction(function() use ($creator, $receptor, $monto) {
            DB::table('transactions')->insert(['user_id' => $creator->id, 'description' => "Envío P2P a " . $receptor->name, 'amount' => $monto, 'type' => 'expense', 'created_at' => now()]);
            DB::table('transactions')->insert(['user_id' => $receptor->id, 'description' => "Recibido P2P de " . $creator->name, 'amount' => $monto, 'type' => 'income', 'created_at' => now()]);
            DB::table('notifications')->insert(['user_id' => $receptor->id, 'message' => "💰 Has recibido $monto FC de " . $creator->name, 'type' => 'success', 'created_at' => now()]);
        });

        return redirect()->route('creator.dashboard')->with('msg', 'p2p_ok');
    }

    /**
     * API de validación en vivo para el buscador dinámico de remesas
     */
    public function checkEmailP2P(Request $request)
    {
        $name = DB::table('users')->where('email', trim($request->query('email')))->where('role', 'creator')->value('name');
        return response()->json(['name' => $name ?: 'NOT_FOUND']);
    }
}
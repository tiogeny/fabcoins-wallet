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
        $missionId = $request->input('mission_id');

        $mission = DB::table('missions')->where('id', $missionId)->where('status', 'open')->first();

        if (!$mission) {
            return redirect()->route('creator.dashboard')->with('error', 'La misión ya no está disponible.');
        }

        // Evitar postulación doble
        $yaPostulado = DB::table('mission_applications')
            ->where('mission_id', $missionId)
            ->where('creator_id', $creator->id)
            ->exists();

        if ($yaPostulado) {
            return redirect()->route('creator.dashboard')->with('error', 'Ya te has postulado a esta misión.');
        }

        DB::transaction(function() use ($creator, $mission, $request) {
            DB::table('mission_applications')->insert([
                'mission_id' => $mission->id,
                'creator_id' => $creator->id,
                'cover_letter' => $request->input('message'),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('notifications')->insert([
                'user_id' => $mission->lab_id,
                'message' => '🚀 Nueva postulación de ' . $creator->name . ' a la misión: ' . $mission->title,
                'type' => 'info',
                'created_at' => now()
            ]);
        });

        return redirect()->route('creator.dashboard')->with('msg', 'mission_applied_ok');
    }

    public function signCredit(Request $request)
    {
        $creator = auth()->user();
        $contractId = $request->input('contract_id');

        $contract = DB::table('financing_agreements')->where('id', $contractId)->where('creator_id', $creator->id)->where('status', 'pending')->first();

        if ($contract) {
            DB::transaction(function() use ($creator, $contract) {
                DB::table('financing_agreements')->where('id', $contract->id)->update(['status' => 'active']);
                
                // 🚀 CIRUGÍA: Actualización forzada a la base de datos (Ignora el $fillable)
                DB::table('users')->where('id', $creator->id)->update([
                    'deuda_fc' => $contract->amount_initial, 
                    'deuda_inicial_fc' => $contract->amount_initial, 
                    'deuda_lab_id' => $contract->lab_id
                ]);
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

    /**
     * El Creador ACEPTA una invitación directa
     */
    public function acceptInvite(Request $request)
    {
        $creator = auth()->user();
        $missionId = $request->input('mission_id');

        DB::transaction(function() use ($creator, $missionId) {
            // 1. Cambiamos el estado a 'accepted'
            DB::table('mission_applications')
                ->where('mission_id', $missionId)
                ->where('creator_id', $creator->id)
                ->update(['status' => 'accepted', 'updated_at' => now()]);

            // 2. Incrementamos la cuenta de vacantes ocupadas en la misión
            DB::table('missions')
                ->where('id', $missionId)
                ->increment('spots_filled');
        });

        return redirect()->back()->with('msg', 'invite_accepted_ok');
    }

    /**
     * El Creador RECHAZA una invitación directa
     */
    public function rejectInvite(Request $request)
    {
        $creator = auth()->user();
        $missionId = $request->input('mission_id');

        DB::table('mission_applications')
            ->where('mission_id', $missionId)
            ->where('creator_id', $creator->id)
            ->update(['status' => 'rejected', 'updated_at' => now()]);

        return redirect()->back()->with('error', 'Invitación rechazada.');
    }

    /**
     * El Creador realiza un pago voluntario de su deuda
     */
    public function payDebt(Request $request)
    {
        $creator = auth()->user();
        $contractId = $request->input('contract_id');
        $amountToPay = floatval($request->input('amount_to_pay'));

        $contract = DB::table('financing_agreements')->where('id', $contractId)->where('creator_id', $creator->id)->where('status', 'active')->first();
        
        if (!$contract) return redirect()->back()->with('error', 'Contrato no encontrado o inválido.');

        // Verificamos saldo líquido en tiempo real
        $querySaldo = DB::select("SELECT SUM(CASE WHEN type IN ('income', 'mint') THEN amount ELSE -amount END) as saldo FROM transactions WHERE user_id = ?", [$creator->id]);
        $saldoActual = $querySaldo[0]->saldo ?? 0;

        if ($amountToPay > $saldoActual || $amountToPay <= 0) {
            return redirect()->back()->with('error', 'Saldo insuficiente para este abono.');
        }

        // Limitamos el pago al máximo de la deuda
        if ($amountToPay > $contract->amount_remaining) {
            $amountToPay = $contract->amount_remaining;
        }

        DB::transaction(function() use ($creator, $contract, $amountToPay) {
            // 1. Descontamos de la billetera del creador
            DB::table('transactions')->insert([
                'user_id' => $creator->id,
                'description' => 'Abono voluntario de Crédito al Laboratorio',
                'amount' => $amountToPay,
                'type' => 'expense',
                'created_at' => now()
            ]);

            // 2. Ingresamos a la billetera del Lab como VALOR QUEMADO (Consumed)
            DB::table('transactions')->insert([
                'user_id' => $contract->lab_id,
                'description' => "Amortización de crédito recibida de {$creator->name}",
                'amount' => $amountToPay,
                'type' => 'consumed', // 🚀 ANTES DECÍA 'income'. AHORA SE QUEMA DE INMEDIATO.
                'created_at' => now()
            ]);

            // 3. Calculamos la nueva deuda
            $nuevaDeuda = $contract->amount_remaining - $amountToPay;
            $estadoContrato = ($nuevaDeuda <= 0) ? 'completed' : 'active';
            $labIdActual = ($nuevaDeuda <= 0) ? null : $contract->lab_id;

            // 4. Actualizamos el contrato y al usuario
            DB::table('financing_agreements')->where('id', $contract->id)->update([
                'amount_remaining' => $nuevaDeuda,
                'status' => $estadoContrato,
                'updated_at' => now()
            ]);

            DB::table('users')->where('id', $creator->id)->update([
                'deuda_fc' => $nuevaDeuda,
                'deuda_lab_id' => $labIdActual
            ]);

            // 5. Notificamos al Lab
            DB::table('notifications')->insert([
                'user_id' => $contract->lab_id,
                'message' => "💰 {$creator->name} te ha realizado un pago de {$amountToPay} FC por su financiamiento.",
                'type' => 'success',
                'created_at' => now()
            ]);
        });

        return redirect()->back()->with('msg', 'debt_paid_ok');
    }
}
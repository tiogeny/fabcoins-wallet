<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    /**
     * 🎯 1. PUBLICAR MISIÓN Y CONGELAR AUTO-ESCROW
     */
    public function store(Request $request)
    {
        $labId = auth()->id();
        $rewardUnit = floatval($request->input('reward_fc', 0));
        $targetCreatorId = $request->input('target_creator_id') ? intval($request->input('target_creator_id')) : null;
        
        $spots = $targetCreatorId ? 1 : intval($request->input('spots_total', 1));
        $totalEscrowRequired = $rewardUnit * $spots;

        $saldo = DB::table('transactions')
            ->where('user_id', $labId)
            ->selectRaw("SUM(CASE WHEN type IN ('income', 'mint') THEN amount ELSE -amount END) as total")
            ->value('total') ?? 0;

        if ($totalEscrowRequired > $saldo) {
            return redirect()->back()->with('error', __('messages.swal_insufficient_escrow_desc'));
        }

        // ... Todo el inicio de tu método store() se queda exactamente igual
        try {
            DB::transaction(function () use ($labId, $request, $rewardUnit, $targetCreatorId, $spots, $totalEscrowRequired) {
                DB::table('missions')->insert([
                    'lab_id'            => $labId,
                    'title'             => trim($request->input('title')),
                    'description'       => trim($request->input('description')),
                    'deadline'          => $request->input('deadline'),
                    'reference_link'    => trim($request->input('reference_link')),
                    'reward_fc'         => $rewardUnit,
                    'target_creator_id' => $targetCreatorId,
                    'spots_total'       => $spots,
                    'spots_filled'      => 0,
                    'status'            => 'open',
                    'created_at'        => now(),
                    'updated_at'        => now()
                ]);

                DB::table('transactions')->insert([
                    'user_id'     => $labId,
                    'description' => "Reserva en Custodia: " . trim($request->input('title')) . " ($spots cupos)",
                    'amount'  => $totalEscrowRequired,
                    'type'        => 'escrow',
                    'created_at'  => now(),
                    'updated_at'  => now()
                ]);

                if ($targetCreatorId) {
                    DB::table('notifications')->insert([
                        'user_id'    => $targetCreatorId,
                        'message'    => __('messages.notif_exclusive_mission'),
                        'type'       => 'info',
                        'created_at' => now()
                    ]);
                }
            });

            // 🚀 REPARACIÓN AQUÍ: Solo dispara el correo si la misión fue DIRIGIDA a alguien específico
            if ($targetCreatorId) {
                $cUser = DB::table('users')->where('id', $targetCreatorId)->first();
                if ($cUser) { 
                    MailService::misionAsignadaAlCreator(
                        $cUser->email, 
                        $cUser->name, 
                        auth()->user()->name, 
                        trim($request->input('title')), 
                        $rewardUnit
                    ); 
                }
            }

            return redirect()->route('lab.dashboard')->with('msg', 'mission_published_ok');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * 🔒 2. ASIGNAR UN TALENTO A UN CUPO
     */
    public function assignCreator(Request $request)
    {
        $missionId = $request->input('mission_id');
        $creatorId = $request->input('creator_id');

        try {
            DB::transaction(function () use ($missionId, $creatorId) {
                DB::table('mission_applications')
                    ->where('mission_id', $missionId)
                    ->where('creator_id', $creatorId)
                    ->update(['status' => 'accepted', 'updated_at' => now()]);

                DB::table('missions')->where('id', $missionId)->increment('spots_filled');

                $mission = DB::table('missions')->where('id', $missionId)->first();
                if ($mission->spots_filled >= $mission->spots_total) {
                    DB::table('missions')->where('id', $missionId)->update(['status' => 'assigned']);
                    DB::table('mission_applications')
                        ->where('mission_id', $missionId)
                        ->where('status', 'pending')
                        ->update(['status' => 'rejected', 'updated_at' => now()]);
                }

                DB::table('notifications')->insert([
                    'user_id'    => $creatorId,
                    'message'    => __('messages.notif_mission_assigned', ['title' => $mission->title]),
                    'type'       => 'success',
                    'created_at' => now()
                ]);
            });

            // 🚀 EL COMPONENTE FALTANTE: Extraemos las entidades para alimentar la pasarela bilingüe
            $mission = DB::table('missions')->where('id', $missionId)->first();
            $creator = DB::table('users')->where('id', $creatorId)->first();
            $lab = DB::table('users')->where('id', $mission->lab_id)->first();

            if ($creator && $mission && $lab) {
                // Envía el correo premium 3B estructurado en el paso anterior
                MailService::misionAsignadaAlCreator(
                    $creator->email,
                    $creator->name,
                    $lab->name,
                    $mission->title,
                    $mission->reward_fc
                );
            }

            return redirect()->route('lab.dashboard')->with('msg', 'mission_assigned_ok');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * 🗑️ 3. DESCARTAR UN POSTULANTE
     */
    public function rejectCreator(Request $request)
    {
        DB::table('mission_applications')
            ->where('mission_id', $request->input('mission_id'))
            ->where('creator_id', $request->input('creator_id'))
            ->update(['status' => 'rejected', 'updated_at' => now()]);

        return redirect()->route('lab.dashboard');
    }

    /**
     * 📉 4. LIQUIDACIÓN CONTABLE FINAL (AMORTIZACIÓN O TRANSFERENCIA)
     */
    public function completeMission(Request $request)
    {
        $missionId = $request->input('mission_id');
        $creatorId = $request->input('creator_id');
        $rating = intval($request->input('rating'));
        $comment = trim($request->input('comment'));
        $labId = auth()->id();

        try {
            $msgRedirect = DB::transaction(function () use ($missionId, $creatorId, $rating, $comment, $labId) {
                $mission = DB::table('missions')->where('id', $missionId)->first();
                $creator = DB::table('users')->where('id', $creatorId)->first();

                $pagoRestante = $mission->reward_fc;
                $esMisionDeRetorno = ($mission->target_creator_id == $creatorId);
                $retornoEjecutado = 0;

                if ($creator->deuda_lab_id == $labId && $creator->deuda_fc > 0 && $esMisionDeRetorno) {
                    if ($pagoRestante >= $creator->deuda_fc) {
                        $retornoEjecutado = $creator->deuda_fc;
                        $pagoRestante -= $creator->deuda_fc;
                        
                        DB::table('users')->where('id', $creatorId)->update(['deuda_lab_id' => null, 'deuda_fc' => 0]);
                        DB::table('financing_agreements')->where('creator_id', $creatorId)->where('lab_id', $labId)->where('status', 'active')->update(['status' => 'completed', 'amount_remaining' => 0, 'updated_at' => now()]);
                    } else {
                        $retornoEjecutado = $pagoRestante;
                        $nuevoSaldoDeuda = $creator->deuda_fc - $pagoRestante;
                        $pagoRestante = 0;

                        DB::table('users')->where('id', $creatorId)->update(['deuda_fc' => $nuevoSaldoDeuda]);
                        DB::table('financing_agreements')->where('creator_id', $creatorId)->where('lab_id', $labId)->where('status', 'active')->update(['amount_remaining' => $nuevoSaldoDeuda, 'updated_at' => now()]);
                    }

                    DB::table('transactions')->insert([
                        'user_id'     => $labId,
                        'description' => __('messages.tx_credit_return', ['id' => $missionId]),
                        'amount'      => $retornoEjecutado,
                        'type'        => 'income',
                        'created_at'  => now(),
                        'updated_at'  => now()
                    ]);
                }

                if ($pagoRestante > 0) {
                    DB::table('transactions')->insert([
                        'user_id'     => $creatorId,
                        'description' => __('messages.tx_payment_received', ['id' => $missionId, 'name' => auth()->user()->name]),
                        'amount'      => $pagoRestante,
                        'type'        => 'income',
                        'created_at'  => now(),
                        'updated_at'  => now()
                    ]);
                }

                $descripcionTransaccion = __('messages.tx_release_prefix', ['id' => $missionId]) . ' ' . (
                    $esMisionDeRetorno 
                        ? __('messages.tx_release_amortized', ['amount' => number_format($mission->reward_fc)]) 
                        : __('messages.tx_release_transferred', ['name' => $creator->name])
                );

                DB::table('transactions')->insert([
                    'user_id'     => $labId,
                    'description' => $descripcionTransaccion,
                    'amount'      => 0.00,
                    'type'        => 'info',
                    'created_at'  => now(),
                    'updated_at'  => now()
                ]);

                DB::table('reviews')->insertGetId([
                    'reviewer_id'  => $labId,
                    'reviewee_id'  => $creatorId,
                    'context_type' => 'mission',
                    'context_id'   => $missionId,
                    'rating'       => $rating,
                    'comment'      => $comment,
                    'created_at'   => now()
                ]);

                DB::table('mission_applications')->where('mission_id', $missionId)->where('creator_id', $creatorId)->update(['is_reviewed' => true]);
                $nuevoPromedio = DB::table('reviews')->where('reviewee_id', $creatorId)->avg('rating');
                DB::table('users')->where('id', $creatorId)->update(['reputation_score' => round($nuevoPromedio, 1)]);

                // 🚀 TRIGGER: Envío de comprobante de pago bilingüe con las estrellas ganadas
                $cUser = DB::table('users')->where('id', $creatorId)->first();
                if ($cUser) { MailService::liquidacionMisionAlCreator($cUser->email, $cUser->name, $mission->title, $mission->reward_fc, $rating, ($retornoEjecutado > 0)); }

                $pendientesDeEvaluacion = DB::table('mission_applications')->where('mission_id', $missionId)->where('status', 'accepted')->where('is_reviewed', false)->count();
                if ($pendientesDeEvaluacion == 0 && $mission->spots_filled >= $mission->spots_total) {
                    DB::table('missions')->where('id', $missionId)->update(['status' => 'completed', 'updated_at' => now()]);
                }

                return $retornoEjecutado > 0 ? 'amortization_ok' : 'payout_ok';
            });

            $routeMsg = ($msgRedirect === 'amortization_ok') ? 'amortize_completed' : 'mission_completed';
            return redirect()->route('lab.dashboard')->with('msg', $routeMsg);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
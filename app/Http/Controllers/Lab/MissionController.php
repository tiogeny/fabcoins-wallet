<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MissionController extends Controller
{
    public function createMission(Request $request)
    {
        $lab = auth()->user();
        $recompensaUnitaria = floatval($request->input('reward_fc'));
        $targetMakerId = $request->input('target_maker_id') ? intval($request->input('target_maker_id')) : null;
        $spotsTotal = $targetMakerId ? 1 : intval($request->input('spots_total', 1));
        $totalACongelar = $recompensaUnitaria * $spotsTotal;

        if ($totalACongelar > $lab->saldo_total) {
            return redirect()->route('lab.dashboard')->with('error', "Saldo insuficiente para cubrir Escrow. Requieres " . number_format($totalACongelar, 2) . " FC.");
        }

        DB::transaction(function () use ($request, $lab, $recompensaUnitaria, $targetMakerId, $spotsTotal, $totalACongelar) {
            $lab->misiones()->create([
                'title' => trim($request->input('title')), 'description' => trim($request->input('description')),
                'deadline' => $request->input('deadline'), 'reference_link' => trim($request->input('reference_link')),
                'reward_fc' => $recompensaUnitaria, 'target_maker_id' => $targetMakerId, 'spots_total' => $spotsTotal, 'spots_filled' => 0
            ]);

            $lab->transacciones()->create(['description' => "Reserva en Escrow: " . trim($request->input('title')) . " ($spotsTotal cupos)", 'amount' => $totalACongelar, 'type' => 'escrow']);
            if ($targetMakerId) {
                DB::table('notifications')->insert(['user_id' => $targetMakerId, 'message' => "🎯 Misión exclusiva: " . trim($request->input('title')), 'type' => 'info', 'created_at' => now()]);
            }
        ]);
        return redirect()->route('lab.dashboard')->with('msg', $targetMakerId ? 'mission_ok_targeted' : 'mission_ok');
    }

    public function assignMaker(Request $request)
    {
        $lab = auth()->user();
        $mision = Mission::where('id', $request->input('mission_id'))->where('lab_id', $lab->id)->where('status', 'open')->firstOrFail();

        DB::transaction(function () use ($mision, $request, $lab) {
            DB::table('mission_applications')->where('mission_id', $mision->id)->where('maker_id', $request->input('maker_id'))->update(['status' => 'accepted']);
            $mision->increment('spots_filled');

            if ($mision->spots_filled >= $mision->spots_total) {
                $mision->update(['status' => 'assigned']);
                DB::table('mission_applications')->where('mission_id', $mision->id)->where('status', 'pending')->update(['status' => 'rejected']);
            }
            DB::table('notifications')->insert(['user_id' => $request->input('maker_id'), 'message' => "¡Asignado a la misión: " . $mision->title, 'type' => 'success', 'created_at' => now()]);
        ]);
        return redirect()->route('lab.dashboard')->with('msg', 'escrow_ok');
    }

    public function rejectMaker(Request $request)
    {
        $lab = auth()->user();
        DB::table('mission_applications')
            ->join('missions', 'mission_applications.mission_id', '=', 'missions.id')
            ->where('missions.lab_id', $lab->id)
            ->where('mission_applications.mission_id', $request->input('mission_id'))
            ->where('mission_applications.maker_id', $request->input('maker_id'))
            ->update(['mission_applications.status' => 'rejected']);

        return redirect()->route('lab.dashboard');
    }

    public function completeMission(Request $request)
    {
        $lab = auth()->user();
        $mision = Mission::where('id', $request->input('mission_id'))->where('lab_id', $lab->id)->firstOrFail();
        $maker = User::where('id', $request->input('maker_id'))->firstOrFail();

        DB::transaction(function () use ($mision, $maker, $lab, $request) {
            $pagoRestante = $mision->reward_fc;
            $nuevaDeuda = $maker->deuda_fc;
            $fcRecuperados = 0;
            $esMisionDeRetorno = ($mision->target_maker_id === $maker->id);

            if ($maker->deuda_lab_id === $lab->id && $nuevaDeuda > 0 && $esMisionDeRetorno) {
                if ($pagoRestante >= $nuevaDeuda) {
                    $fcRecuperados = $nuevaDeuda; $pagoRestante -= $nuevaDeuda; $nuevaDeuda = 0;
                    $maker->update(['deuda_lab_id' => null, 'deuda_fc' => 0]);
                    DB::table('financing_agreements')->where('maker_id', $maker->id)->where('lab_id', $lab->id)->where('status', 'active')->update(['status' => 'completed', 'amount_remaining' => 0]);
                } else {
                    $fcRecuperados = $pagoRestante; $nuevaDeuda -= $pagoRestante; $pagoRestante = 0;
                    $maker->update(['deuda_fc' => $nuevaDeuda]);
                    DB::table('financing_agreements')->where('maker_id', $maker->id)->where('lab_id', $lab->id)->where('status', 'active')->update(['amount_remaining' => $nuevaDeuda]);
                }
            }

            if ($pagoRestante > 0) {
                DB::table('transactions')->insert(['user_id' => $maker->id, 'description' => "Pago: " . $mision->title, 'amount' => $pagoRestante, 'type' => 'income', 'created_at' => now()]);
            }
            if ($fcRecuperados > 0) {
                $lab->transacciones()->create(['description' => "Retorno de Crédito Fab (Misión #" . $mision->id . ")", 'amount' => $fcRecuperados, 'type' => 'income']);
            }

            $reviewId = DB::table('reviews')->insertGetId(['reviewer_id' => $lab->id, 'reviewee_id' => $maker->id, 'context_type' => 'mission', 'context_id' => $mision->id, 'rating' => intval($request->input('rating')), 'comment' => trim($request->input('comment')), 'created_at' => now()]);
            DB::table('mission_applications')->where('mission_id', $mision->id)->where('maker_id', $maker->id)->update(['is_reviewed' => true]);
            $maker->update(['reputation_score' => round(DB::table('reviews')->where('reviewee_id', $maker->id)->avg('rating'), 1)]);
            DB::table('notifications')->insert(['user_id' => $maker->id, 'message' => "Misión \"" . $mision->title . "\" evaluada.", 'type' => 'info', 'created_at' => now()]);
        });
        return redirect()->route('lab.dashboard')->with('msg', 'mission_completed');
    }
}
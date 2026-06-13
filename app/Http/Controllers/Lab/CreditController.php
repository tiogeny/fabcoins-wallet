<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    public function processReservation(Request $request)
    {
        $lab = auth()->user();
        $orderId = $request->input('order_id');
        $accion = $request->input('accion');

        $order = DB::table('orders')->join('lab_assets', 'orders.asset_id', '=', 'lab_assets.id')->where('orders.id', $orderId)->where('lab_assets.lab_id', $lab->id)->where('orders.status', 'pending')->select('orders.*', 'lab_assets.custom_name')->first();

        if ($order) {
            DB::transaction(function () use ($order, $accion, $lab) {
                if ($accion === 'aprobar') {
                    DB::table('orders')->where('id', $order->id)->update(['status' => 'completed']);
                    DB::table('transactions')->where('user_id', $order->creador_id)->where('amount', $order->total_fc)->where('type', 'escrow')->latest('id')->limit(1)->update(['type' => 'burn', 'description' => '🔥 Servicio consumido (Quema): ' . $order->custom_name]);
                    DB::table('lab_assets')->where('id', $order->asset_id)->increment('consumed_hours', $order->hours_requested);
                } else {
                    DB::table('orders')->where('id', $order->id)->update(['status' => 'rejected']);
                    DB::table('transactions')->insert(['user_id' => $order->creador_id, 'description' => "Reembolso: " . $order->custom_name, 'amount' => $order->total_fc, 'type' => 'income', 'created_at' => now()]);
                }
            });
            return redirect()->route('lab.dashboard')->with('msg', $accion === 'aprobar' ? 'order_approved' : 'order_rejected');
        }
        return redirect()->route('lab.dashboard');
    }

    public function reschedule(Request $request)
    {
        DB::table('orders')->where('id', $request->input('order_id'))->update(['status' => 'rescheduled', 'reservation_date' => $request->input('nueva_fecha')]);
        return redirect()->route('lab.dashboard')->with('msg', 'rescheduled_ok');
    }

    public function proposeCredit(Request $request)
    {
        $creador = User::where('email', trim($request->input('email_creador')))->where('role', 'creador')->first();
        if (!$creador) return redirect()->route('lab.dashboard')->with('error', "No se encontró ningún creador registrado con ese correo.");

        $monto = floatval($request->input('monto_fc'));
        DB::table('financing_agreements')->insert([
            'lab_id' => auth()->id(), 'creador_id' => $creador->id, 'amount_initial' => $monto, 'amount_remaining' => $monto,
            'description' => trim($request->input('motivo')), 'status' => 'pending', 'created_at' => now(), 'updated_at' => now()
        ]);
        return redirect()->route('lab.dashboard')->with('msg', 'credit_proposed');
    }

    public function cancelCredit(Request $request)
    {
        DB::table('financing_agreements')->where('id', $request->input('contract_id'))->where('lab_id', auth()->id())->where('status', 'pending')->update(['status' => 'cancelled']);
        return redirect()->route('lab.dashboard')->with('msg', 'credit_cancelled');
    }
}
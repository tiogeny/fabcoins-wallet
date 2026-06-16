<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function approve(Request $request)
    {
        $orderId = $request->input('order_id');
        $order = DB::table('orders')->where('id', $orderId)->first();

        if (!$order || $order->status !== 'pending') return redirect()->back();

        DB::transaction(function() use ($order) {
            // 1. Aprobar la orden
            DB::table('orders')->where('id', $order->id)->update([
                'status' => 'completed',
                'updated_at' => now()
            ]);

            // 2. 🚀 DESCONTAREMOS LAS HORAS DEL INVENTARIO REAL
            DB::table('lab_assets')->where('id', $order->asset_id)->increment('consumed_hours', $order->hours_requested);

            // 3. 🎨 CAMBIAR TIPO DE TRANSACCIÓN A 'consumed'
            DB::table('transactions')->insert([
                'user_id' => auth()->id(),
                'description' => 'Servicio consumido: Orden #' . $order->id,
                'amount' => $order->total_fc,
                'type' => 'consumed', // Antes decía 'income'
                'created_at' => now()
            ]);
            
            DB::table('notifications')->insert([
                'user_id' => $order->creator_id,
                'message' => '✅ Tu reserva ha sido aprobada por el Lab.',
                'type' => 'success',
                'created_at' => now()
            ]);
        });

        return redirect()->back()->with('msg', 'applied_ok');
    }

    public function reject(Request $request)
    {
        // Lógica de rechazo y reembolso al creador...
        return redirect()->back();
    }

    public function reschedule(Request $request)
    {
        // Lógica de reprogramación...
        return redirect()->back();
    }
}
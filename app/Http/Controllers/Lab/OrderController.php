<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function approve(Request $request)
    {
        $orderId = $request->input('order_id');
        
        // 🎯 CIRUGÍA DE CONTROL: Unimos la tabla con lab_assets para recuperar el nombre real y su tipo
        $order = DB::table('orders')
            ->join('lab_assets', 'orders.asset_id', '=', 'lab_assets.id')
            ->where('orders.id', $orderId)
            ->select('orders.*', 'lab_assets.custom_name', 'lab_assets.asset_type')
            ->first();

        if (!$order || $order->status !== 'pending') {
            return redirect()->back()->with('error', 'La orden no existe o ya fue procesada.');
        }

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
                'type' => 'consumed',
                'created_at' => now()
            ]);
            
            DB::table('notifications')->insert([
                'user_id' => $order->creator_id,
                'message' => '✅ Tu reserva ha sido aprobada por el Lab.',
                'type' => 'success',
                'created_at' => now()
            ]);
        });

        // 🚀 TRIGGER CLEAN: Alerta al creador que su reserva de activo está confirmada y asegurada
        $creatorUser = DB::table('users')->where('id', $order->creator_id)->first();
        if ($creatorUser) {
            MailService::confirmacionReservaActivo($creatorUser->email, $creatorUser->name, auth()->user()->name, $order->custom_name, $order->reservation_date, $order->hours_requested, $order->asset_type);
        }

        return redirect()->back()->with('msg', 'reservation_approved_ok');
    }

    public function reject(Request $request)
    {
        $orderId = $request->input('order_id');
        $order = DB::table('orders')->where('id', $orderId)->first();

        // 1. Candado de seguridad: Solo se pueden rechazar órdenes en estado pendiente
        if (!$order || $order->status !== 'pending') {
            return redirect()->back()->with('error', 'La orden no existe o ya fue procesada.');
        }

        $asset = DB::table('lab_assets')->where('id', $order->asset_id)->first();
        $assetName = $asset ? $asset->custom_name : 'Infraestructura';

        try {
            DB::transaction(function() use ($order, $assetName) {
                // 2. Reembolso total inmediato al Creador
                DB::table('transactions')->insert([
                    'user_id'     => $order->creator_id,
                    'description' => 'Reembolso por reserva rechazada: ' . $assetName,
                    'amount'      => $order->total_fc,
                    'type'        => 'income', // Retorna el dinero a su circulante líquido
                    'created_at'  => now()
                ]);

                // 3. Cambiar estado de la orden a rechazada
                DB::table('orders')->where('id', $order->id)->update([
                    'status'     => 'rejected',
                    'updated_at' => now()
                ]);

                // 4. Notificación interna de la campanita
                DB::table('notifications')->insert([
                    'user_id'    => $order->creator_id,
                    'message'    => '❌ Tu solicitud de reserva para ' . $assetName . ' fue rechazada. Fondos devueltos.',
                    'type'       => 'danger',
                    'created_at' => now()
                ]);
            });

            // 5. 📨 TRIGGER CLEAN: Despacha la plantilla limpia y ordenada del Bloque 4
            $creator = DB::table('users')->where('id', $order->creator_id)->first();
            if ($creator) {
                MailService::reservaRechazadaAlCreator($creator->email, $creator->name, auth()->user()->name, $assetName, $asset->asset_type ?? 'machine');
            }

            return redirect()->back()->with('msg', 'reservation_rejected_ok');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en el Ledger: ' . $e->getMessage());
        }
    }

    /**
     * 🔄 EL LAB PROPONE UNA REPROGRAMACIÓN DE CALENDARIO
     */
    public function reschedule(Request $request)
    {
        $request->validate([
            'order_id' => 'required|integer',
            'nueva_fecha' => 'required|date'
        ]);

        $orderId = $request->input('order_id');
        $nuevaFecha = $request->input('nueva_fecha');
        
        $order = DB::table('orders')->where('id', $orderId)->first();

        if (!$order || $order->status !== 'pending') {
            return redirect()->back()->with('error', 'La orden no se puede reprogramar.');
        }

        $asset = DB::table('lab_assets')->where('id', $order->asset_id)->first();
        $assetName = $asset ? $asset->custom_name : 'Infraestructura';

        try {
            DB::transaction(function() use ($order, $nuevaFecha, $assetName) {
                // 1. Mover la orden al estado 'rescheduled' y actualizar la fecha propuesta
                DB::table('orders')->where('id', $order->id)->update([
                    'status' => 'rescheduled',
                    'reservation_date' => $nuevaFecha,
                    'updated_at' => now()
                ]);

                // 2. Notificar al creador mediante la campanita para que tome una decisión
                DB::table('notifications')->insert([
                    'user_id' => $order->creator_id,
                    'message' => '🔄 El Lab ha propuesto una nueva fecha para tu reserva de ' . $assetName,
                    'type' => 'warning',
                    'created_at' => now()
                ]);
            });

            // 📨 TRIGGER CLEAN: Despacha la plantilla de reprogramación oficial del Bloque 4
            $creator = DB::table('users')->where('id', $order->creator_id)->first();
            if ($creator) {
                MailService::propuestaReprogramacionActivo($creator->email, $creator->name, auth()->user()->name, $assetName, $nuevaFecha, $asset->asset_type ?? 'machine');
            }

            return redirect()->back()->with('msg', 'reservation_rescheduled_ok');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en el sistema de asignación: ' . $e->getMessage());
        }
    }
}
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
        $order = DB::table('orders')->where('id', $orderId)->first();

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

        // 🚀 TRIGGER ASENTADO: Alerta al creador que su máquina/espacio/taller está listo
        $creatorUser = DB::table('users')->where('id', $order->creator_id)->first();
        if ($creatorUser) {
            MailService::reservaAprobadaAlCreator($creatorUser->email, $creatorUser->name, auth()->user()->name, $order->custom_name ?? 'Activo', $order->reservation_date ?? now());
        }

        return redirect()->back()->with('msg', 'applied_ok');
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
            ]);

            // 5. 📨 TRIGGER DE CORREO (BILINGÜE AUTOMÁTICO):
            $creator = DB::table('users')->where('id', $order->creator_id)->first();
            if ($creator) {
                MailService::enviar(
                    $creator->email, 
                    "❌ Reserva Rechazada - FabCoins", 
                    "❌ Reservation Declined - FabCoins", 
                    "❌ Solicitud no procesada", 
                    "❌ Request not processed", 
                    "<p>Hola <strong>" . htmlspecialchars($creator->name) . "</strong>,</p><p>Te informamos que tu solicitud de reserva para el activo <strong>" . htmlspecialchars($assetName) . "</strong> fue rechazada por el laboratorio. Los fondos han sido devueltos íntegramente a tu saldo circulante.</p>", 
                    "<p>Hello <strong>" . htmlspecialchars($creator->name) . "</strong>,</p><p>We inform you that your reservation request for the asset <strong>" . htmlspecialchars($assetName) . "</strong> was declined by the laboratory. The funds have been fully refunded to your circulating balance.</p>"
                );
            }

            return redirect()->back()->with('msg', 'applied_ok');

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
            ]);

            // 📨 TRIGGER (BILINGÜE): Despacha el aviso con la nueva fecha y abre los botones de acción del Creator
            $creator = DB::table('users')->where('id', $order->creator_id)->first();
            if ($creator) {
                $fechaFormateada = date('d/m/Y', strtotime($nuevaFecha));
                MailService::enviar(
                    $creator->email,
                    "🔄 Propuesta de Cambio de Fecha - FabCoins",
                    "🔄 Reservation Reschedule Proposed - FabCoins",
                    "🔄 Actualización de Calendario",
                    "🔄 Schedule Update",
                    "<p>Hola <strong>" . htmlspecialchars($creator->name) . "</strong>,</p><p>El laboratorio no cuenta con disponibilidad inmediata en la fecha original. Han propuesto mover tu reserva de <strong>" . htmlspecialchars($assetName) . "</strong> para el día: <strong>$fechaFormateada</strong>.</p><p>Ingresa a tu panel donde podrás **Aceptar** este nuevo día o **Cancelar** la reserva para recibir un reembolso automático.</p>",
                    "<p>Hello <strong>" . htmlspecialchars($creator->name) . "</strong>,</p><p>The laboratory does not have immediate availability on your original date. They have proposed to reschedule your reservation for <strong>" . htmlspecialchars($assetName) . "</strong> to: <strong>$fechaFormateada</strong>.</p><p>Log into your dashboard where you can **Accept** this new date or **Cancel** the reservation to get an automatic refund.</p>"
                );
            }

            return redirect()->back()->with('msg', 'applied_ok');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error en el sistema de asignación: ' . $e->getMessage());
        }
    }
}
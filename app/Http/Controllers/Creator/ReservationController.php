<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    /**
     * Procesa la solicitud de reserva de un equipo o servicio
     */
    public function book(Request $request)
    {
        $creator = auth()->user();
        $assetId = $request->input('asset_id');
        $horas = floatval($request->input('hours'));
        $fecha = $request->input('reservation_date');
        $isCreditRequest = $request->has('request_credit'); // Detectar si pide crédito

        $asset = DB::table('lab_assets')->where('id', $assetId)->first();
        if (!$asset) {
            return redirect()->route('creator.dashboard')->with('error', __('messages.err_asset_not_found'));
        }

        $costoTotal = $horas * $asset->set_price_fc;
        $querySaldo = DB::select("SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as saldo FROM transactions WHERE user_id = ?", [$creator->id]);
        $saldoTotal = $querySaldo[0]->saldo ?? 0;

        // Si no tiene saldo y tampoco pidió crédito, lo rebotamos por seguridad
        if ($saldoTotal < $costoTotal && !$isCreditRequest) {
            return redirect()->route('creator.dashboard')->with('error', __('messages.swal_insufficient_escrow_desc'));
        }

        DB::transaction(function() use ($creator, $assetId, $horas, $fecha, $costoTotal, $asset, $saldoTotal, $isCreditRequest) {
            
            // 1. Insertar siempre la Orden de Reserva
            DB::table('orders')->insert([
                'creator_id'       => $creator->id, 
                'asset_id'         => $assetId, 
                'hours_requested'  => $horas, 
                'total_fc'         => $costoTotal, 
                'reservation_date' => $fecha, 
                'status'           => 'pending', 
                'created_at'       => now(), 
                'updated_at'       => now()
            ]);
            
            if ($isCreditRequest && $saldoTotal < $costoTotal) {
                $diferencia = $costoTotal - $saldoTotal;

                // Si tiene algo de saldo (> 0), se lo congelamos como "Pago Parcial"
                if ($saldoTotal > 0) {
                    DB::table('transactions')->insert([
                        'user_id'     => $creator->id, 
                        'description' => __('messages.tx_reserve_partial', ['asset' => $asset->custom_name]), 
                        'amount'      => $saldoTotal, 
                        'type'        => 'expense', 
                        'created_at'  => now()
                    ]);
                }

                // Generamos la propuesta de financiamiento (ISA) para el Lab
                DB::table('financing_agreements')->insert([
                    'creator_id' => $creator->id,
                    'lab_id' => $asset->lab_id,
                    'amount_initial' => $diferencia,
                    'amount_remaining' => $diferencia,
                    'description' => __('messages.isa_desc_auto', ['asset' => $asset->custom_name, 'hours' => $horas]),
                    'status' => 'pending', 
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Notificamos al Lab sobre la reserva con crédito
                DB::table('notifications')->insert([
                    'user_id'    => $asset->lab_id, 
                    'message'    => __('messages.notif_reserve_credit', [
                                        'creator' => $creator->name, 
                                        'asset' => $asset->custom_name, 
                                        'credit' => number_format($diferencia, 0)
                                    ]), 
                    'type'       => 'warning', 
                    'created_at' => now()
                ]);

            } else {
                // FLUJO NORMAL: Se le descuenta el 100% y se notifica normal
                DB::table('transactions')->insert([
                    'user_id'     => $creator->id, 
                    'description' => __('messages.tx_reserve_desc', ['asset' => $asset->custom_name]), 
                    'amount'      => $costoTotal, 
                    'type'        => 'expense', 
                    'created_at'  => now()
                ]);
                
                DB::table('notifications')->insert([
                    'user_id'    => $asset->lab_id, 
                    'message'    => __('messages.notif_reserve_req', [
                                        'creator' => $creator->name, 
                                        'asset' => $asset->custom_name, 
                                        'hours' => $horas, 
                                        'date' => date('d/m', strtotime($fecha))
                                    ]), 
                    'type'       => 'warning', 
                    'created_at' => now()
                ]);
            }
        });

        // 📨 TRIGGER: Despacha correo bilingüe de alerta de reserva hacia el Laboratorio dueño del activo
        $lab = DB::table('users')->where('id', $asset->lab_id)->first();
        if ($lab) {
            $parametroTexto = ($asset->asset_type === 'workshop') ? "$horas cupos solicitado(s)" : "$horas horas solicitadas";
            MailService::reservaHardwareAlLab($lab->email, $lab->name, $creator->name, $asset->custom_name, $parametroTexto, $costoTotal, $fecha);
        }

        // Podemos usar el mismo mensaje de éxito por ahora
        return redirect()->route('creator.dashboard')->with('msg', 'rental_pending');
    }

    /**
     * Aceptación de reprogramación de calendario
     */
    public function acceptDate(Request $request)
    {
        $orderId = $request->input('order_id');
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) return redirect()->route('creator.dashboard');
        
        $asset = DB::table('lab_assets')->where('id', $order->asset_id)->first();

        DB::transaction(function() use ($order, $asset) {
            DB::table('orders')->where('id', $order->id)->update(['status' => 'pending']);
            
            DB::table('notifications')->insert([
                'user_id'    => $asset->lab_id, 
                'message'    => __('messages.notif_date_accepted'), 
                'type'       => 'success', 
                'created_at' => now()
            ]);
        });
        
        // 📨 TRIGGER: Notifica al Lab que el creador dio el visto bueno a la nueva fecha propuesta
        $lab = DB::table('users')->where('id', $asset->lab_id)->first();
        $creator = auth()->user();
        if ($lab) {
            $msgEs = "<p>Hola <strong>{$lab->name}</strong>,</p><p>El Creador <strong>{$creator->name}</strong> ha **ACEPTADO** tu propuesta de nueva fecha de calendario para usar el activo <strong>{$asset->custom_name}</strong>.</p><p>Por favor ingresa a tu panel para proceder con la aprobación de la reserva.</p>";
            $msgEn = "<p>Hello <strong>{$lab->name}</strong>,</p><p>The Creator <strong>{$creator->name}</strong> has **ACCEPTED** your new calendar date proposal to use the asset <strong>{$asset->custom_name}</strong>.</p><p>Please log into your dashboard to proceed with the reservation approval.</p>";
            MailService::enviar($lab->email, "📅 Fecha de Reprogramación Aceptada", "📅 Rescheduling Date Accepted", "📅 Calendario Confirmado", "📅 Schedule Confirmed", $msgEs, $msgEn);
        }

        return redirect()->route('creator.dashboard')->with('msg', 'date_accepted');
    }

    /**
     * Rechazo de reprogramación de calendario (Reembolso)
     */
    public function rejectDate(Request $request)
    {
        $orderId = $request->input('order_id');
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) return redirect()->route('creator.dashboard');
        
        $asset = DB::table('lab_assets')->where('id', $order->asset_id)->first();

        DB::transaction(function() use ($order, $asset) {
            DB::table('transactions')->insert([
                'user_id'     => $order->creator_id, 
                'description' => __('messages.tx_refund_desc', ['asset' => $asset->custom_name]), 
                'amount'      => $order->total_fc, 
                'type'        => 'income', 
                'created_at'  => now()
            ]);
            
            DB::table('orders')->where('id', $order->id)->update(['status' => 'rejected']);
            
            DB::table('notifications')->insert([
                'user_id'    => $asset->lab_id, 
                'message'    => __('messages.notif_date_rejected'), 
                'type'       => 'danger', 
                'created_at' => now()
            ]);
        });

        // 📨 TRIGGER: Alerta al Lab que la reserva fue cancelada y archivada por rechazo de fecha
        $lab = DB::table('users')->where('id', $asset->lab_id)->first();
        $creator = auth()->user();
        if ($lab) {
            $msgEs = "<p>Hola <strong>{$lab->name}</strong>,</p><p>Te informamos que el Creador <strong>{$creator->name}</strong> ha declinado la nueva propuesta de calendario para el activo <strong>{$asset->custom_name}</strong>.</p><p>La orden ha sido archivada como cancelada y los FabCoins en garantía fueron reembolsados de inmediato al usuario.</p>";
            $msgEn = "<p>Hello <strong>{$lab->name}</strong>,</p><p>We inform you that the Creator <strong>{$creator->name}</strong> has declined the new schedule proposal for the asset <strong>{$asset->custom_name}</strong>.</p><p>The order has been archived as canceled and the collateral FabCoins were instantly refunded to the user.</p>";
            MailService::enviar($lab->email, "❌ Reserva Reclamada y Cancelada", "❌ Reservation Declined and Canceled", "❌ Cancelación de Calendario", "❌ Schedule Cancellation", $msgEs, $msgEn);
        }
        
        return redirect()->route('creator.dashboard')->with('msg', 'date_rejected');
    }

    /**
     * Emite una reseña hacia un laboratorio (Soporta Mercado y Misiones de forma polimórfica)
     */
    public function rateLab(Request $request)
    {
        $creatorId = auth()->id();
        $labId     = $request->input('lab_id');
        $rating    = intval($request->input('rating'));
        $comment   = trim($request->input('comment'));

        // Interceptamos los dos posibles detonantes de contexto
        $missionId = $request->input('mission_id');
        $orderId   = $request->input('order_id');

        DB::transaction(function() use ($creatorId, $labId, $rating, $comment, $missionId, $orderId) {
            
            // 🎯 MOTOR DE DETECCIÓN DINÁMICA DE CONTEXTO
            if (!empty($missionId)) {
                // 🔵 CONTEXTO: VIENE DEL HUB DE MISIONES COMPLETADAS
                $contextType = 'mission';
                $contextId   = $missionId;
            } else {
                // 🟡 CONTEXTO: VIENE DEL MONITOR DEL MERCADO (ALQUILERES)
                $contextType = 'market';
                $contextId   = $orderId;
            }

            // 1. Inserción blindada en la tabla única de reviews de la red
            DB::table('reviews')->insert([
                'reviewer_id'  => $creatorId, 
                'reviewee_id'  => $labId, 
                'context_type' => $contextType, 
                'context_id'   => $contextId, 
                'rating'       => $rating, 
                'comment'      => $comment, 
                'created_at'   => now(), 
                'updated_at'   => now()
            ]);
            
            // 2. Marcado de bandera correspondiente para cerrar el ciclo de vida del botón
            if ($contextType === 'market') {
                DB::table('orders')
                    ->where('id', $orderId)
                    ->update(['is_reviewed' => true]);
            }
            
            // 3. Recalcular y actualizar la reputación promedio en tiempo real del laboratorio
            $avg = DB::table('reviews')->where('reviewee_id', $labId)->avg('rating');
            DB::table('users')->where('id', $labId)->update(['reputation_score' => round($avg, 1)]);
            
            // 4. Despachar notificación de éxito al Laboratorio calificado
            DB::table('notifications')->insert([
                'user_id'    => $labId, 
                'message'    => __('messages.notif_new_rating', ['rating' => $rating]), 
                'type'       => 'success', 
                'created_at' => now()
            ]);
        });

        // 📨 TRIGGER: Envía la alerta de estrellas y reseña ganada al correo oficial del Lab calificado
        $lab = DB::table('users')->where('id', $labId)->first();
        if ($lab) {
            MailService::notificarNuevaResenaAlLab($lab->email, $lab->name, auth()->user()->name, $rating);
        }
        
        return redirect()->route('creator.dashboard')->with('msg', 'review_ok');
    }
}
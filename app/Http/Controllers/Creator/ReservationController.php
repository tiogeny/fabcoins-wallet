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
            
            // 1. Insertar siempre la Orden de Reserva capturando su ID autogenerado
            $orderId = DB::table('orders')->insertGetId([
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
                    'order_id' => $orderId,
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

        // 📨 TRIGGER INTERACTIVO: Envía el correo correcto según el tipo de pago (Líquido o Crédito ISA)
        $lab = DB::table('users')->where('id', $asset->lab_id)->first();
        if ($lab) {
            $parametroTexto = ($asset->asset_type === 'workshop') ? "$horas cupos" : "$horas horas";
            
            if ($isCreditRequest && $saldoTotal < $costoTotal) {
                // Llama al correo de solicitud de financiamiento que armamos antes
                MailService::solicitudCreditoActivo($lab->email, $lab->name, $creator->name, $asset->custom_name, $parametroTexto, $costoTotal, $asset->asset_type);
            } else {
                // 🎯 REPARADO: Pasamos la variable correcta ($costoTotal) para evitar el error de ejecucion
                MailService::reservaActivoAlLab($lab->email, $lab->name, $creator->name, $asset->custom_name, $parametroTexto, $costoTotal, $fecha, $asset->asset_type);
            }
        }

        // 🎯 DETECTOR FINTECH: Si requirió crédito, despacha una alerta del ecosistema financiero
        if ($isCreditRequest && $saldoTotal < $costoTotal) {
            return redirect()->route('creator.dashboard')->with('msg', 'credit_pending');
        }

        // Flujo tradicional con saldo líquido ordinario
        return redirect()->route('creator.dashboard')->with('msg', 'rental_pending');
    }

    /**
     * Aceptación de reprogramación de calendario con Cierre Contable Automático (Camino B)
     */
    public function acceptDate(Request $request)
    {
        $orderId = $request->input('order_id');
        $order = DB::table('orders')->where('id', $orderId)->first();
        if (!$order) return redirect()->route('creator.dashboard');
        
        $asset = DB::table('lab_assets')->where('id', $order->asset_id)->first();
        $lab = DB::table('users')->where('id', $asset->lab_id)->first();
        $creator = auth()->user();

        // 🎯 LEDGER INTELLIGENT: El Creador acepta la fecha propuesta y el trato se cierra automáticamente
        DB::transaction(function() use ($order, $asset) {
            // 1. Mutar la orden directamente al estado final de completada
            DB::table('orders')->where('id', $order->id)->update([
                'status'     => 'completed',
                'updated_at' => now()
            ]);

            // 2. Descontar las horas/cupos del inventario real del laboratorio
            DB::table('lab_assets')->where('id', $order->asset_id)->increment('consumed_hours', $order->hours_requested);

            // 3. Registrar el consumo definitivo en el Libro Contable para los KPI globales
            DB::table('transactions')->insert([
                'user_id'     => $asset->lab_id,
                'description' => 'Servicio consumido via Reprogramación: Orden #' . $order->id,
                'amount'      => $order->total_fc,
                'type'        => 'consumed',
                'created_at'  => now()
            ]);
            
            // Alerta interna en la campanita del Administrador del Lab
            DB::table('notifications')->insert([
                'user_id'    => $asset->lab_id,
                'message'    => '📅 ' . auth()->user()->name . ' aceptó tu propuesta de fecha. Cupo asegurado y liquidado.',
                'type'       => 'success',
                'created_at' => now()
            ]);
        });
        
        // 📨 TRIGGERS EMISORES DE COHESIÓN DIGITAL
        if ($lab) {
            // Notificar al correo institucional del Lab la confirmación del alumno
            MailService::respuestaReprogramacionAlLab($lab->email, $lab->name, $creator->name, $asset->custom_name, true);
            
            // Enviar al buzón del Creador su pase de entrada definitivo con normativas de seguridad bilingües
            MailService::confirmacionReservaActivo($creator->email, $creator->name, $lab->name, $asset->custom_name, $order->reservation_date, $order->hours_requested, $asset->asset_type ?? 'machine');
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
            DB::table('transactions')->insert(['user_id' => $order->creator_id, 'description' => __('messages.tx_refund_desc', ['asset' => $asset->custom_name]), 'amount' => $order->total_fc, 'type' => 'income', 'created_at' => now()]);
            DB::table('orders')->where('id', $order->id)->update(['status' => 'rejected']);
            DB::table('notifications')->insert(['user_id' => $asset->lab_id, 'message' => __('messages.notif_date_rejected'), 'type' => 'danger', 'created_at' => now()]);
        });

        // 📨 TRIGGER CLEAN: Alerta de rechazo al laboratorio
        $lab = DB::table('users')->where('id', $asset->lab_id)->first();
        if ($lab) {
            MailService::respuestaReprogramacionAlLab($lab->email, $lab->name, auth()->user()->name, $asset->custom_name, false);
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
        $missionId = $request->input('mission_id');
        $orderId   = $request->input('order_id');

        // 🎯 OBTENER EL NOMBRE DEL ACTIVO/MISIÓN ANTES DE LA TRANSACCIÓN PARA EL CORREO
        $tituloContexto = 'Infraestructura';
        if (!empty($missionId)) {
            $tituloContexto = DB::table('missions')->where('id', $missionId)->value('title') ?? 'Misión';
        } else if (!empty($orderId)) {
            $tituloContexto = DB::table('orders')->join('lab_assets', 'orders.asset_id', '=', 'lab_assets.id')->where('orders.id', $orderId)->value('lab_assets.custom_name') ?? 'Servicio';
        }

        DB::transaction(function() use ($creatorId, $labId, $rating, $comment, $missionId, $orderId) {
            if (!empty($missionId)) {
                $contextType = 'mission';
                $contextId   = $missionId;
            } else {
                $contextType = 'market';
                $contextId   = $orderId;
            }

            DB::table('reviews')->insert([
                'reviewer_id' => $creatorId, 'reviewee_id' => $labId, 'context_type' => $contextType, 'context_id' => $contextId, 'rating' => $rating, 'comment' => $comment, 'created_at' => now(), 'updated_at' => now()
            ]);
            
            if ($contextType === 'market') {
                DB::table('orders')->where('id', $orderId)->update(['is_reviewed' => true]);
            }
            
            $avg = DB::table('reviews')->where('reviewee_id', $labId)->avg('rating');
            DB::table('users')->where('id', $labId)->update(['reputation_score' => round($avg, 1)]);
            
            DB::table('notifications')->insert([
                'user_id' => $labId, 'message' => __('messages.notif_new_rating', ['rating' => $rating]), 'type' => 'success', 'created_at' => now()
            ]);
        });

        // 📨 TRIGGER CORREGIDO: Ahora sí viajan los 5 datos requeridos completos
        $lab = DB::table('users')->where('id', $labId)->first();
        if ($lab) {
            MailService::notificarNuevaResenaAlLab($lab->email, $lab->name, auth()->user()->name, $tituloContexto, $rating);
        }
        
        return redirect()->route('creator.dashboard')->with('msg', 'review_ok');
    }
}
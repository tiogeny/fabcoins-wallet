<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    /**
     * El Lab APRUEBA la solicitud de crédito del Creador.
     */
    /**
     * El Lab APRUEBA la solicitud de crédito del Creador y liquida la reserva en combo.
     */
    public function approve(Request $request)
    {
        $creditId = $request->input('credit_id');
        $labId = auth()->id();

        $credit = DB::table('financing_agreements')->where('id', $creditId)->where('lab_id', $labId)->where('status', 'pending')->first();
        if (!$credit) return redirect()->back()->with('error', 'El crédito no existe.');

        // 👤 Buscamos al creador antes para tener su nombre listo para los conceptos contables
        $cUser = DB::table('users')->where('id', $credit->creator_id)->first();

        DB::transaction(function() use ($credit, $labId, $cUser) {
            
            // 1. 🎯 RELACIÓN DIRECTA Y ABSOLUTA: Localizar la orden de reserva exacta vinculada por ID
            $order = DB::table('orders')
                ->join('lab_assets', 'orders.asset_id', '=', 'lab_assets.id')
                ->where('orders.id', $credit->order_id)
                ->select('orders.id', 'lab_assets.custom_name', 'orders.hours_requested', 'orders.total_fc', 'orders.asset_id')
                ->first();

            // 2. Activar formalmente el acuerdo de financiamiento (ISA)
            DB::table('financing_agreements')->where('id', $credit->id)->update([
                'status' => 'active', 
                'updated_at' => now()
            ]);

            if ($order) {
                // 3. CONTABILIDAD CREADOR: Inyectar el préstamo en su cuenta (Ingreso por Crédito)
                DB::table('transactions')->insert([
                    'user_id'     => $credit->creator_id,
                    'description' => __('messages.tx_credit_disbursed', ['asset' => $order->custom_name]) ?? 'Desembolso de crédito educativo para: ' . $order->custom_name,
                    'amount'      => $credit->amount_initial,
                    'type'        => 'income',
                    'created_at'  => now()
                ]);

                // 4. CONTABILIDAD CREADOR: Transferir el saldo restante del taller (Gasto Liquidado)
                DB::table('transactions')->insert([
                    'user_id'     => $credit->creator_id,
                    'description' => __('messages.tx_reserve_desc', ['asset' => $order->custom_name]) ?? 'Reserva de infraestructura: ' . $order->custom_name,
                    'amount'      => $credit->amount_initial,
                    'type'        => 'expense',
                    'created_at'  => now()
                ]);

                // 5. 🏭 DESCUENTO DE CUPOS FISICOS: Incrementamos las horas/cupos consumidos en el activo
                DB::table('lab_assets')->where('id', $order->asset_id)->increment('consumed_hours', $order->hours_requested);

                // 6A. 📉 SALIDA DE TESORERÍA DEL LAB: Resta el dinero líquido de su bóveda corriente
                DB::table('transactions')->insert([
                    'user_id'     => $labId,
                    'description' => 'Financiamiento otorgado (Crédito ISA) a ' . ($cUser->name ?? 'Creador') . ' para: ' . $order->custom_name,
                    'amount'      => $credit->amount_initial,
                    'type'        => 'expense', 
                    'created_at'  => now()
                ]);

                // 6B. 📊 REALIZACIÓN DE CAPACIDAD (QUEMADO): Registra la quema oficial del servicio para los KPIs
                DB::table('transactions')->insert([
                    'user_id'     => $labId,
                    'description' => 'Capacidad realizada / consumida por ' . ($cUser->name ?? 'Creador') . ': ' . $order->custom_name,
                    'amount'      => $order->total_fc, 
                    'type'        => 'consumed', // 🔥 ¡ESTA ES LA LLAVE QUE AJUSTA TU GRÁFICO!
                    'created_at'  => now()
                ]);

                // 7. FLUJO VISUAL: Mudar la orden a 'completed' de forma automática
                DB::table('orders')->where('id', $order->id)->update([
                    'status'     => 'completed',
                    'updated_at' => now()
                ]);

                // Campanita de confirmación automática de inscripción
                DB::table('notifications')->insert([
                    'user_id'    => $credit->creator_id,
                    'message'    => "✅ ¡Tu inscripción a '" . $order->custom_name . "' ha sido confirmada automáticamente!",
                    'type'       => 'success',
                    'created_at' => now()
                ]);
            }

            // Notificación base del crédito aprobado
            DB::table('notifications')->insert([
                'user_id' => $credit->creator_id, 
                'message' => __('messages.notif_credit_approved') ?? '✅ Tu financiamiento ha sido aprobado.',
                'type' => 'success',
                'created_at' => now()
            ]);
        });

        // 📨 TRIGGER: Correo enviado al Creator
        if ($cUser) {
            MailService::resolucionCreditoAlCreator($cUser->email, $cUser->name, auth()->user()->name, $credit->amount_initial, true);
        }

        return redirect()->back()->with('msg', 'credit_accepted');
    }

    /**
     * El Lab RECHAZA la solicitud de crédito del Creador.
     */
    public function reject(Request $request)
    {
        $creditId = $request->input('credit_id');
        $labId = auth()->id();

        $credit = DB::table('financing_agreements')->where('id', $creditId)->where('lab_id', $labId)->where('status', 'pending')->first();
        if (!$credit) return redirect()->back();

        DB::transaction(function() use ($credit) {
            DB::table('financing_agreements')->where('id', $credit->id)->update(['status' => 'cancelled', 'updated_at' => now()]);

            // 🌐 MULTIDIOMA: Campanita traducida
            DB::table('notifications')->insert([
                'user_id' => $credit->creator_id,
                'message' => __('messages.notif_credit_rejected') ?? '❌ Tu solicitud de crédito fue rechazada.',
                'type' => 'error',
                'created_at' => now()
            ]);
        });

        // 📨 TRIGGER: Correo enviado al Creator
        $cUser = DB::table('users')->where('id', $credit->creator_id)->first();
        if ($cUser) {
            MailService::resolucionCreditoAlCreator($cUser->email, $cUser->name, auth()->user()->name, $credit->amount_initial, false);
        }

        return redirect()->back()->with('msg', 'credit_cancelled');
    }
}
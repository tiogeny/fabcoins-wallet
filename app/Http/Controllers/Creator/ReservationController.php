<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
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

        $asset = DB::table('lab_assets')->where('id', $assetId)->first();
        if (!$asset) {
            return redirect()->route('creator.dashboard')->with('error', __('messages.err_asset_not_found'));
        }

        $costoTotal = $horas * $asset->set_price_fc;

        $querySaldo = DB::select("SELECT SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as saldo FROM transactions WHERE user_id = ?", [$creator->id]);
        $saldoTotal = $querySaldo[0]->saldo ?? 0;

        if ($saldoTotal < $costoTotal) {
            return redirect()->route('creator.dashboard')->with('error', __('messages.swal_insufficient_escrow_desc'));
        }

        DB::transaction(function() use ($creator, $assetId, $horas, $fecha, $costoTotal, $asset) {
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
        });

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
        
        return redirect()->route('creator.dashboard')->with('msg', 'date_rejected');
    }

    /**
     * Emite una reseña hacia un laboratorio
     */
    public function rateLab(Request $request)
    {
        $creatorId = auth()->id();
        $orderId = $request->input('order_id');
        $labId = $request->input('lab_id');
        $rating = intval($request->input('rating'));

        DB::transaction(function() use ($creatorId, $labId, $orderId, $rating, $request) {
            DB::table('reviews')->insert([
                'reviewer_id'  => $creatorId, 
                'reviewee_id'  => $labId, 
                'context_type' => 'market', 
                'context_id'   => $orderId, 
                'rating'       => $rating, 
                'comment'      => trim($request->input('comment')), 
                'created_at'   => now(), 
                'updated_at'   => now()
            ]);
            
            DB::table('orders')->where('id', $orderId)->update(['is_reviewed' => true]);
            
            $avg = DB::table('reviews')->where('reviewee_id', $labId)->avg('rating');
            DB::table('users')->where('id', $labId)->update(['reputation_score' => round($avg, 1)]);
            
            DB::table('notifications')->insert([
                'user_id'    => $labId, 
                'message'    => __('messages.notif_new_rating', ['rating' => $rating]), 
                'type'       => 'success', 
                'created_at' => now()
            ]);
        });
        
        return redirect()->route('creator.dashboard')->with('msg', 'review_ok');
    }
}
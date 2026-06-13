<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationController extends Controller
{
    public function book(Request $request)
    {
        $creator = auth()->user();
        $assetId = $request->input('asset_id');
        $horas = floatval($request->input('hours'));
        $fecha = $request->input('reservation_date');

        $asset = DB::table('lab_assets')->where('id', $assetId)->first();
        $costoTotal = $horas * $asset->set_price_fc;

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
                'description' => "Reserva en custodia: " . $asset->custom_name, 
                'amount'      => $costoTotal, 
                'type'        => 'expense', 
                'created_at'  => now()
            ]);
            
            DB::table('notifications')->insert([
                'user_id'    => $asset->lab_id, 
                'message'    => "📅 " . $creator->name . " solicitó " . $asset->custom_name . " ($horas h) para el " . date('d/m', strtotime($fecha)), 
                'type'       => 'warning', 
                'created_at' => now()
            ]);
        ]);

        return redirect()->route('creator.dashboard')->with('msg', 'rental_pending');
    }

    public function acceptDate(Request $request)
    {
        $orderId = $request->input('order_id');
        $order = DB::table('orders')->where('id', $orderId)->first();
        $asset = DB::table('lab_assets')->where('id', $order->asset_id)->first();

        DB::transaction(function() use ($order, $asset) {
            DB::table('orders')->where('id', $order->id)->update(['status' => 'pending']);
            DB::table('notifications')->insert([
                'user_id'    => $asset->lab_id, 
                'message'    => "✅ El Creator aceptó la nueva fecha de reprogramación.", 
                'type'       => 'success', 
                'created_at' => now()
            ]);
        ]);
        return redirect()->route('creator.dashboard')->with('msg', 'date_accepted');
    }

    public function rejectDate(Request $request)
    {
        $orderId = $request->input('order_id');
        $order = DB::table('orders')->where('id', $orderId)->first();
        $asset = DB::table('lab_assets')->where('id', $order->asset_id)->first();

        DB::transaction(function() use ($order, $asset) {
            DB::table('transactions')->insert([
                'user_id'     => $order->creator_id, 
                'description' => "Reembolso por reserva cancelada (Incompatibilidad): " . $asset->custom_name, 
                'amount'      => $order->total_fc, 
                'type'        => 'income', 
                'created_at'  => now()
            ]);
            
            DB::table('orders')->where('id', $order->id)->update(['status' => 'rejected']);
            
            DB::table('notifications')->insert([
                'user_id'    => $asset->lab_id, 
                'message'    => "❌ Reserva cancelada por el Creator debido a incompatibilidad de calendario.", 
                'type'       => 'danger', 
                'created_at' => now()
            ]);
        ]);
        return redirect()->route('creator.dashboard')->with('msg', 'date_rejected');
    }

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
                'message'    => "⭐ Has recibido una nueva calificación de " . $rating . " estrellas de un Creator.", 
                'type'       => 'success', 
                'created_at' => now()
            ]);
        ]);
        return redirect()->route('creator.dashboard')->with('msg', 'review_ok');
    }
}
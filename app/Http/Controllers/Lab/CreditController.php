<?php

namespace App\Http\Controllers\Lab;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CreditController extends Controller
{
    /**
     * El Lab APRUEBA la solicitud de crédito del Creador.
     */
    public function approve(Request $request)
    {
        $creditId = $request->input('credit_id');
        $labId = auth()->id();

        // Buscamos el crédito pendiente que pertenece a este Lab
        $credit = DB::table('financing_agreements')
            ->where('id', $creditId)
            ->where('lab_id', $labId)
            ->where('status', 'pending')
            ->first();

        if (!$credit) {
            return redirect()->back()->with('error', 'El crédito no existe o ya fue procesado.');
        }

        DB::transaction(function() use ($credit) {
            // 1. Activar el crédito formalmente
            DB::table('financing_agreements')
                ->where('id', $credit->id)
                ->update([
                    'status' => 'active',
                    'updated_at' => now()
                ]);

            // 2. Avisarle al alumno por la campanita
            $idAlumno = $credit->maker_id ?? $credit->creator_id;
            DB::table('notifications')->insert([
                'user_id' => $idAlumno, 
                'message' => '✅ El Lab ha aprobado tu solicitud de financiamiento. ¡Tu reserva está lista para ser liberada!',
                'type' => 'success',
                'created_at' => now()
            ]);
        });

        // Retornamos con un mensaje de éxito para el SweetAlert
        return redirect()->back()->with('msg', 'credit_accepted');
    }

    /**
     * El Lab RECHAZA la solicitud de crédito del Creador.
     */
    public function reject(Request $request)
    {
        $creditId = $request->input('credit_id');
        $labId = auth()->id();

        $credit = DB::table('financing_agreements')
            ->where('id', $creditId)
            ->where('lab_id', $labId)
            ->where('status', 'pending')
            ->first();

        if (!$credit) {
            return redirect()->back();
        }

        DB::transaction(function() use ($credit) {
            // 1. Cancelar el crédito
            DB::table('financing_agreements')
                ->where('id', $credit->id)
                ->update([
                    'status' => 'cancelled',
                    'updated_at' => now()
                ]);

            // 2. Avisarle al alumno del rechazo
            $idAlumno = $credit->maker_id ?? $credit->creator_id;
            DB::table('notifications')->insert([
                'user_id' => $idAlumno,
                'message' => '❌ El Lab ha rechazado tu solicitud de crédito ISA.',
                'type' => 'error',
                'created_at' => now()
            ]);
        });

        return redirect()->back()->with('msg', 'credit_cancelled');
    }
}
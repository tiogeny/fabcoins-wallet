<?php

namespace App\Http\Controllers\Creator;

use App\Http\Controllers\Controller;
use App\Services\MailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function apply(Request $request)
    {
        $creator = auth()->user();
        $missionId = $request->input('mission_id');

        $mission = DB::table('missions')->where('id', $missionId)->where('status', 'open')->first();

        if (!$mission) {
            return redirect()->route('creator.dashboard')->with('error', 'La misión ya no está disponible.');
        }

        // Evitar postulación doble
        $yaPostulado = DB::table('mission_applications')
            ->where('mission_id', $missionId)
            ->where('creator_id', $creator->id)
            ->exists();

        if ($yaPostulado) {
            return redirect()->route('creator.dashboard')->with('error', 'Ya te has postulado a esta misión.');
        }

        DB::transaction(function() use ($creator, $mission, $request) {
            DB::table('mission_applications')->insert([
                'mission_id' => $mission->id,
                'creator_id' => $creator->id,
                'cover_letter' => $request->input('message'),
                'status' => 'pending',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::table('notifications')->insert([
                'user_id' => $mission->lab_id,
                'message' => '🚀 Nueva postulación de ' . $creator->name . ' a la misión: ' . $mission->title,
                'type' => 'info',
                'created_at' => now()
            ]);
        });

        // 📨 TRIGGER: Avisa al Laboratorio emisor que tiene un nuevo especialista en el radar
        $lab = DB::table('users')->where('id', $mission->lab_id)->first();
        if ($lab) {
            MailService::postulacionMisionAlLab($lab->email, $lab->name, $creator->name, $mission->title);
        }

        return redirect()->route('creator.dashboard')->with('msg', 'mission_applied_ok');
    }

    public function signCredit(Request $request)
    {
        $creator = auth()->user();
        $contractId = $request->input('contract_id');

        $contract = DB::table('financing_agreements')->where('id', $contractId)->where('creator_id', $creator->id)->where('status', 'pending')->first();

        if ($contract) {
            DB::transaction(function() use ($creator, $contract) {
                DB::table('financing_agreements')->where('id', $contract->id)->update(['status' => 'active']);
                
                // 🚀 CIRUGÍA: Actualización forzada a la base de datos (Ignora el $fillable)
                DB::table('users')->where('id', $creator->id)->update([
                    'deuda_fc' => $contract->amount_initial, 
                    'deuda_inicial_fc' => $contract->amount_initial, 
                    'deuda_lab_id' => $contract->lab_id
                ]);
            });

            // 📨 TRIGGER: Notifica al Lab que el creador firmó formalmente el acuerdo de honor ISA
            $lab = DB::table('users')->where('id', $contract->lab_id)->first();
            if ($lab) {
                $msgEs = "<p>Hola <strong>{$lab->name}</strong>,</p><p>¡Buenas noticias! El Creador <strong>{$creator->name}</strong> ha firmado y aceptado el contrato de financiamiento ISA de honor por <strong>" . number_format($contract->amount_initial, 0) . " FC</strong>.</p><p>El sistema comenzará a auditar y retener los saldos de forma automática en misiones futuras.</p>";
                $msgEn = "<p>Hello <strong>{$lab->name}</strong>,</p><p>Great news! The Creator <strong>{$creator->name}</strong> has signed and accepted the ISA honor financing agreement for <strong>" . number_format($contract->amount_initial, 0) . " FC</strong>.</p><p>The system will automatically audit and hold balances on future milestones.</p>";
                MailService::enviar($lab->email, "🎓 Contrato ISA Firmado y Activo", "🎓 ISA Contract Signed and Active", "🎓 Acuerdo de Honor Concluido", "🎓 Honor Agreement Concluded", $msgEs, $msgEn);
            }

            return redirect()->route('creator.dashboard')->with('msg', 'credit_accepted');
        }
        return redirect()->route('creator.dashboard');
    }

    public function transferP2P(Request $request)
{
    $creator = auth()->user();
    $emailDestino = trim($request->input('dest_email'));
    $monto = floatval($request->input('monto_p2p'));
    $motivo = $request->input('mensaje_p2p') ? trim($request->input('mensaje_p2p')) : 'Transferencia directa P2P';

    // 🛡️ CONTROL ADICIONAL (Mitiga VULN-09): Evitar montos negativos o cero
    if ($monto <= 0) {
        return redirect()->route('creator.dashboard')->with('error', "El monto de la transferencia debe ser mayor a cero.");
    }

    $receptor = DB::table('users')->where('email', $emailDestino)->where('role', 'creator')->first();

    if (!$receptor) return redirect()->route('creator.dashboard')->with('error', "Usuario destinatario no encontrado en la red.");
    if ($receptor->id === $creator->id) return redirect()->route('creator.dashboard')->with('error', "Operación inválida: No puedes enviarte fondos a ti mismo.");

    try {
        // 🔒 CIRUGÍA DE SEGURIDAD (VULN-03): Todo el proceso ocurre congelado en la base de datos
        DB::transaction(function() use ($creator, $receptor, $monto) {
            
            // Bloqueamos temporalmente los registros de este creador para evitar doble gasto en milisegundos concurrentes
            $saldo = DB::table('transactions')
                ->where('user_id', $creator->id)
                ->lockForUpdate()
                ->selectRaw("SUM(CASE WHEN type = 'income' THEN amount ELSE -amount END) as saldo")
                ->value('saldo') ?? 0;

            // La validación se ejecuta AQUÍ ADENTRO con la seguridad de que el saldo no cambiará
            if ($saldo < $monto) {
                throw new \Exception("Fondos insuficientes en billetera.");
            }

            // Si pasa la prueba, insertamos de inmediato de forma atómica
            DB::table('transactions')->insert(['user_id' => $creator->id, 'description' => "Envío P2P a " . $receptor->name, 'amount' => $monto, 'type' => 'expense', 'created_at' => now()]);
            DB::table('transactions')->insert(['user_id' => $receptor->id, 'description' => "Recibido P2P de " . $creator->name, 'amount' => $monto, 'type' => 'income', 'created_at' => now()]);
            DB::table('notifications')->insert(['user_id' => $receptor->id, 'message' => "💰 Has recibido $monto FC de " . $creator->name, 'type' => 'success', 'created_at' => now()]);
        });

    } catch (\Exception $e) {
        // Si hay doble gasto o falta de saldo, la base de datos cancela todo (Rollback) y avisa al usuario
        return redirect()->route('creator.dashboard')->with('error', $e->getMessage());
    }

    // 📨 TRIGGER RECEPTOR: Alerta de abono inmediato en su cuenta
    $msgRecEs = "<p>Hola <strong>{$receptor->name}</strong>,</p><p>Has recibido una transferencia digital directa de <strong>{$creator->name}</strong> por un valor de:</p><h3 style='color:#2ecc71; text-align:center; font-size:24px;'>+" . number_format($monto, 0) . " FC</h3><p>Concepto adjunto: <em>\"$motivo\"</em></p>";
    $msgRecEn = "<p>Hello <strong>{$receptor->name}</strong>,</p><p>You have received a direct digital transfer from <strong>{$creator->name}</strong> for a value of:</p><h3 style='color:#2ecc71; text-align:center; font-size:24px;'>+" . number_format($monto, 0) . " FC</h3><p>Concept attached: <em>\"$motivo\"</em></p>";
    MailService::enviar($receptor->email, "💰 Has recibido FabCoins P2P", "💰 You received FabCoins P2P", "💰 Saldo Acreditado", "💰 Balance Credited", $msgRecEs, $msgRecEn);

    // 📨 TRIGGER EMISOR: Comprobante digital de débito de remesa
    $msgEmiEs = "<p>Hola <strong>{$creator->name}</strong>,</p><p>Tu transferencia P2P hacia <strong>{$receptor->name}</strong> por un valor de <strong>" . number_format($monto, 0) . " FC</strong> ha sido procesada y debitada con éxito de tu libro contable.</p>";
    $msgEmiEn = "<p>Hello <strong>{$creator->name}</strong>,</p><p>Your P2P transfer to <strong>{$receptor->name}</strong> for an amount of <strong>" . number_format($monto, 0) . " FC</strong> has been successfully processed and debited from your ledger.</p>";
    MailService::enviar($creator->email, "💸 Comprobante de Envío P2P", "💸 P2P Transfer Receipt", "💸 Remesa Despachada", "💸 Remittance Dispatched", $msgEmiEs, $msgEmiEn);

    return redirect()->route('creator.dashboard')->with('msg', 'p2p_ok');
}

    /**
     * API de validación en vivo para el buscador dinámico de remesas
     */
    public function checkEmailP2P(Request $request)
    {
        $name = DB::table('users')->where('email', trim($request->query('email')))->where('role', 'creator')->value('name');
        return response()->json(['name' => $name ?: 'NOT_FOUND']);
    }

    /**
     * El Creador ACEPTA una invitación directa con Alerta Contable al Lab
     */
    public function acceptInvite(Request $request)
    {
        $creator = auth()->user();
        $missionId = $request->input('mission_id');

        DB::transaction(function() use ($creator, $missionId) {
            // 1. Cambiamos el estado a 'accepted'
            DB::table('mission_applications')
                ->where('mission_id', $missionId)
                ->where('creator_id', $creator->id)
                ->update(['status' => 'accepted', 'updated_at' => now()]);

            // 2. Incrementamos la cuenta de vacantes ocupadas en la misión
            DB::table('missions')
                ->where('id', $missionId)
                ->increment('spots_filled');
        });

        // 🚀 TRIGGER DE COMPROMISO: Alerta al laboratorio que el alumno aceptó trabajar en la misión dirigida
        $mission = DB::table('missions')->where('id', $missionId)->first();
        $labUser = DB::table('users')->where('id', $mission->lab_id ?? null)->first();

        if ($mission && $mission->target_creator_id == $creator->id && $labUser) {
            
            // 🔔 INYECCIÓN EN BASE DE DATOS PARA LA CAMPANITA DEL LAB
            DB::table('notifications')->insert([
                'user_id'    => $mission->lab_id,
                'message'    => "🤝 " . $creator->name . " ha aceptado tu Misión de Honor dirigida: '" . $mission->title . "'",
                'type'       => 'info',
                'created_at' => now()
            ]);

            MailService::misionDirigidaAceptadaAlLab(
                $labUser->email, 
                $labUser->name, 
                $creator->name, 
                $mission->title, 
                $mission->reward_fc
            );
        }

        return redirect()->back()->with('msg', 'invite_accepted_ok');
    }

    /**
     * El Creador RECHAZA una invitación directa
     */
    public function rejectInvite(Request $request)
    {
        $creator = auth()->user();
        $missionId = $request->input('mission_id');

        DB::table('mission_applications')
            ->where('mission_id', $missionId)
            ->where('creator_id', $creator->id)
            ->update(['status' => 'rejected', 'updated_at' => now()]);

        return redirect()->back()->with('error', 'Invitación rechazada.');
    }

    /**
     * El Creador realiza un pago voluntario de su deuda
     */
    public function payDebt(Request $request)
    {
        $creator = auth()->user();
        $contractId = $request->input('contract_id');
        $amountToPay = floatval($request->input('amount_to_pay'));

        $contract = DB::table('financing_agreements')->where('id', $contractId)->where('creator_id', $creator->id)->where('status', 'active')->first();
        
        if (!$contract) return redirect()->back()->with('error', 'Contrato no encontrado o inválido.');

        // Verificamos saldo líquido en tiempo real
        $querySaldo = DB::select("SELECT SUM(CASE WHEN type IN ('income', 'mint') THEN amount ELSE -amount END) as saldo FROM transactions WHERE user_id = ?", [$creator->id]);
        $saldoActual = $querySaldo[0]->saldo ?? 0;

        if ($amountToPay > $saldoActual || $amountToPay <= 0) {
            return redirect()->back()->with('error', 'Saldo insuficiente para este abono.');
        }

        // Limitamos el pago al máximo de la deuda
        if ($amountToPay > $contract->amount_remaining) {
            $amountToPay = $contract->amount_remaining;
        }

        DB::transaction(function() use ($creator, $contract, $amountToPay) {
            // 1. Descontamos de la billetera del creador
            DB::table('transactions')->insert([
                'user_id' => $creator->id,
                'description' => 'Abono voluntario de Crédito al Laboratorio',
                'amount' => $amountToPay,
                'type' => 'expense',
                'created_at' => now()
            ]);

            // 2. Ingresamos a la billetera del Lab como VALOR QUEMADO (Consumed)
            DB::table('transactions')->insert([
                'user_id' => $contract->lab_id,
                'description' => "Amortización de crédito recibida de {$creator->name}",
                'amount' => $amountToPay,
                'type' => 'consumed', // 🚀 ANTES DECÍA 'income'. AHORA SE QUEMA DE INMEDIATO.
                'created_at' => now()
            ]);

            // 3. Calculamos la nueva deuda
            $nuevaDeuda = $contract->amount_remaining - $amountToPay;
            $estadoContrato = ($nuevaDeuda <= 0) ? 'completed' : 'active';
            $labIdActual = ($nuevaDeuda <= 0) ? null : $contract->lab_id;

            // 4. Actualizamos el contrato y al usuario
            DB::table('financing_agreements')->where('id', $contract->id)->update([
                'amount_remaining' => $nuevaDeuda,
                'status' => $estadoContrato,
                'updated_at' => now()
            ]);

            DB::table('users')->where('id', $creator->id)->update([
                'deuda_fc' => $nuevaDeuda,
                'deuda_lab_id' => $labIdActual
            ]);

            // 5. Notificamos al Lab
            DB::table('notifications')->insert([
                'user_id' => $contract->lab_id,
                'message' => "💰 {$creator->name} te ha realizado un pago de {$amountToPay} FC por su financiamiento.",
                'type' => 'success',
                'created_at' => now()
            ]);
        });

        // 📨 TRIGGER: Alerta al Lab sobre el reingreso contable por pasarela voluntaria
        $lab = DB::table('users')->where('id', $contract->lab_id)->first();
        if ($lab) {
            $msgEs = "<p>Hola <strong>{$lab->name}</strong>,</p><p>El Creador de la red <strong>{$creator->name}</strong> ha ejecutado un abono voluntario de amortización a su cuenta por un valor de:</p><h3 style='color:#e67e22; text-align:center; font-size:24px;'>{$amountToPay} FC</h3><p>Los fondos han ingresado directamente como capacidad consumida e inyectada a tus bóvedas.</p>";
            $msgEn = "<p>Hello <strong>{$lab->name}</strong>,</p><p>The network Creator <strong>{$creator->name}</strong> has executed a voluntary amortization payment to their account for a value of:</p><h3 style='color:#e67e22; text-align:center; font-size:24px;'>{$amountToPay} FC</h3><p>Funds have entered directly as consumed capacity injected into your vaults.</p>";
            MailService::enviar($lab->email, "💰 Amortización Voluntaria Recibida", "💰 Voluntary Amortization Received", "💰 Fondos Recuperados", "💰 Funds Recovered", $msgEs, $msgEn);
        }

        return redirect()->back()->with('msg', 'debt_paid_ok');
    }
}
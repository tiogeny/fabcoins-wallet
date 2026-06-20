<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * Motor base de renderizado y despacho bilingüe con Inline CSS Premium
     */
    public static function enviar($emailDestino, $asuntoEs, $asuntoEn, $tituloEs, $tituloEn, $msgEs, $msgEn)
    {
        $userLang = DB::table('users')->where('email', $emailDestino)->value('preferred_lang') ?? 'es';

        $asunto = ($userLang === 'en') ? $asuntoEn : $asuntoEs;
        $tituloInterno = ($userLang === 'en') ? $tituloEn : $tituloEs;
        $mensajeHtml = ($userLang === 'en') ? $msgEn : $msgEs;
        $footerText = ($userLang === 'en') 
            ? "This is an automated message, please do not reply to this email." 
            : "Este es un mensaje automático, por favor no respondas a este correo.";

        // Maquetación Dark Mode Industrial que hereda la paleta de lab.css y creator.css
        $bodyHtml = "
        <div style='background-color: #11151d; padding: 40px 20px; font-family: \"Segoe UI\", Roboto, sans-serif;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #1c2230; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 15px 35px rgba(0,0,0,0.5);'>
                <div style='background-color: #1c2230; padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.04);'>
                    <h1 style='color: #ffffff; margin: 0; font-size: 24px; letter-spacing: 1px; font-weight:700;'>🪙 FabCoins</h1>
                </div>
                <div style='padding: 35px; color: #cbd5e0; font-size: 14.5px; line-height: 1.6;'>
                    <h2 style='color: #f1c40f; margin-top: 0; font-size: 19px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;'>$tituloInterno</h2>
                    $mensajeHtml
                </div>
                <div style='background-color: #131722; padding: 20px; text-align: center; color: #7f8c8d; font-size: 11.5px; border-top: 1px solid rgba(255,255,255,0.02);'>
                    Red Global de Creadores FabCoins &copy; " . date('Y') . "<br>
                    <span style='font-size: 10px; color:#4a5568; display:block; margin-top:4px;'>$footerText</span>
                </div>
            </div>
        </div>";

        try {
            Mail::html($bodyHtml, function ($message) use ($emailDestino, $asunto) {
                $message->to($emailDestino)->subject($asunto)->from('no-reply@fabcoins.co', 'FabCoins');
            });
            return true;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Fallo de Mailing: " . $e->getMessage());
            return false;
        }
    }

    // =========================================================================
    // 🗂️ CATÁLOGO DE MÉTODOS DE ENVÍO DE LA RED GLOBAL
    // =========================================================================

    public static function bienvenidaLab($email, $nombre, $claveTemporal)
    {
        $msgEs = "<p>Hola <strong>" . htmlspecialchars($nombre) . "</strong>,</p><p>Tu lab ha sido invitado formalmente por el Super Admin para formar parte de la red oficial de FabCoins.</p><div style='background: #131722; padding: 15px; border-radius: 6px; margin: 20px 0; border:1px solid rgba(255,255,255,0.05);'><strong>Credenciales temporales de acceso:</strong><br>Usuario: $email<br>Clave Temporal: $claveTemporal</div><p>Por seguridad, el sistema te pedirá cambiar esta contraseña al ingresar por primera vez.</p>";
        $msgEn = "<p>Hello <strong>" . htmlspecialchars($nombre) . "</strong>,</p><p>Your lab has been formally invited by the Super Admin to join the official FabCoins network.</p><div style='background: #131722; padding: 15px; border-radius: 6px; margin: 20px 0; border:1px solid rgba(255,255,255,0.05);'><strong>Temporary access credentials:</strong><br>Username: $email<br>Temporary Password: $claveTemporal</div><p>For security reasons, the system will ask you to change this password upon your first login.</p>";
        return self::enviar($email, "¡Bienvenido a la Red Oficial FabCoins!", "Welcome to the Official FabCoins Network!", "🏢 Invitación a la Red", "🏢 Invitation to the Network", $msgEs, $msgEn);
    }

    public static function notificarTokenizacion($emailLab, $nombreLab, $detallesActivos, $totalFc)
    {
        $msgEs = "<p>Hola <strong>$nombreLab</strong>,</p><p>Has completado exitosamente la tokenización de infraestructura por valor de: <strong style='color:#2ecc71;'>+" . number_format($totalFc, 0) . " FC</strong>.</p><p>Detalle del proceso: <em>$detallesActivos</em></p><p>Los fondos ya se encuentran líquidos en tu Bóveda.</p>";
        $msgEn = "<p>Hello <strong>$nombreLab</strong>,</p><p>You have successfully completed infrastructure tokenization for a value of: <strong style='color:#2ecc71;'>+" . number_format($totalFc, 0) . " FC</strong>.</p><p>Process breakdown: <em>$detallesActivos</em></p><p>Funds are now liquid inside your Vault.</p>";
        return self::enviar($emailLab, "📈 Emisión de FabCoins Procesada", "📈 FabCoins Minting Processed", "📈 Confirmación de Emisión Base", "📈 Base Minting Confirmation", $msgEs, $msgEn);
    }

    public static function reservaHardwareAlLab($emailLab, $nombreLab, $nombreCreator, $activo, $parametro, $monto, $fecha)
    {
        $fechaF = date('d/m/Y', strtotime($fecha));
        $msgEs = "<p>Hola <strong>$nombreLab</strong>,</p><p>El Creador <strong>$nombreCreator</strong> solicita una reserva de tus activos:</p><div style='background:#131722; padding:15px; border-radius:6px;'>🔹 <strong>Descriptor:</strong> $activo<br>🔹 <strong>Fecha:</strong> $fechaF<br>🔹 <strong>Uso/Volumen:</strong> $parametro<br>🔹 <strong>Reingreso Contable:</strong> $monto FC</div>";
        $msgEn = "<p>Hello <strong>$nombreLab</strong>,</p><p>The Creator <strong>$nombreCreator</strong> requests an asset reservation:</p><div style='background:#131722; padding:15px; border-radius:6px;'>🔹 <strong>Asset:</strong> $activo<br>🔹 <strong>Date:</strong> $fechaF<br>🔹 <strong>Volume/Usage:</strong> $parametro<br>🔹 <strong>Accounting Return:</strong> $monto FC</div>";
        return self::enviar($emailLab, "⚙️ Nueva Solicitud de Reserva Recibida", "⚙️ New Reservation Request Received", "⚙️ Solicitud de Infraestructura", "⚙️ Infrastructure Request", $msgEs, $msgEn);
    }

    public static function reservaAprobadaAlCreator($emailCreator, $nombreCreator, $nombreLab, $maquina, $fecha)
    {
        $fechaF = date('d/m/Y', strtotime($fecha));
        $msgEs = "<p>¡Buenas noticias <strong>$nombreCreator</strong>!</p><p>El Fab Lab <strong>$nombreLab</strong> ha aprobado tu reserva.</p><div style='background:rgba(46,204,113,0.05); padding:15px; border-radius:6px; border-left:4px solid #2ecc71;'>⚙️ <strong>Equipo/Servicio:</strong> $maquina<br>📅 <strong>Fecha Confirmada:</strong> $fechaF</div><p>Los canales de WhatsApp oficiales ya están abiertos para coordinar el archivo de diseño.</p>";
        $msgEn = "<p>Good news <strong>$nombreCreator</strong>!</p><p>The Fab Lab <strong>$nombreLab</strong> has approved your reservation.</p><div style='background:rgba(46,204,113,0.05); padding:15px; border-radius:6px; border-left:4px solid #2ecc71;'>⚙️ <strong>Equipment/Service:</strong> $maquina<br>📅 <strong>Confirmed Date:</strong> $fechaF</div><p>Official WhatsApp channels are now open to coordinate your file submissions.</p>";
        return self::enviar($emailCreator, "✅ Reserva Aprobada - FabCoins", "✅ Reservation Approved - FabCoins", "✅ Reserva Confirmada", "✅ Reservation Confirmed", $msgEs, $msgEn);
    }

    public static function postulacionMisionAlLab($emailLab, $nombreLab, $nombreCreator, $tituloMision)
    {
        $msgEs = "<p>Hola <strong>$nombreLab</strong>,</p><p>El Creador especialista <strong>$nombreCreator</strong> se ha postulado para la misión: <em>\"$tituloMision\"</em>.</p><p>Ingresa a tu consola para auditar sus habilidades validadas.</p>";
        $msgEn = "<p>Hello <strong>$nombreLab</strong>,</p><p>The specialist Creator <strong>$nombreCreator</strong> has applied for the mission: <em>\"$tituloMision\"</em>.</p><p>Log into your dashboard to audit their validated skills.</p>";
        return self::enviar($emailLab, "🚀 Nuevo Postulante en el Radar", "🚀 New Candidate on Radar", "🚀 Candidato en Espera", "🚀 Candidate Awaiting Review", $msgEs, $msgEn);
    }

    public static function misionAsignadaAlCreator($emailCreator, $nombreCreator, $nombreLab, $tituloMision, $recompensa)
    {
        $msgEs = "<p>¡Felicidades <strong>$nombreCreator</strong>!</p><p>El Lab <strong>$nombreLab</strong> te ha asignado la misión: <strong>\"$tituloMision\"</strong>.</p><p>La recompensa de <strong>$recompensa FC</strong> ha sido congelada en Escrow de forma segura.</p>";
        $msgEn = "<p>Congratulations <strong>$nombreCreator</strong>!</p><p>The Lab <strong>$nombreLab</strong> has assigned you the mission: <strong>\"$tituloMision\"</strong>.</p><p>The reward of <strong>$recompensa FC</strong> has been safely locked in Escrow.</p>";
        return self::enviar($emailCreator, "🎯 Misión Asignada - FabCoins", "🎯 Mission Assigned - FabCoins", "🎯 ¡A trabajar!", "🎯 Time to get to work!", $msgEs, $msgEn);
    }

    public static function liquidacionMisionAlCreator($emailCreator, $nombreCreator, $tituloMision, $fc, $estrellas, $isCredit)
    {
        $estrellasHtml = str_repeat('⭐', $estrellas);
        $detalleContable = $isCredit 
            ? "destinado automáticamente a reducir el balance de tu Crédito ISA de Honor."
            : "transferido directamente a tu billetera líquida circulante.";

        $msgEs = "<p>Hola <strong>$nombreCreator</strong>,</p><p>La misión <em>\"$tituloMision\"</em> ha sido marcada como completada con éxito.</p><div style='background:#131722; padding:20px; border-radius:8px; text-align:center;'><h3 style='color:#2ecc71; margin:0; font-size:24px;'>+" . number_format($fc, 0) . " FC</h3><p style='margin:10px 0;'>Calificación: $estrellasHtml</p></div><p>El pago ha sido $detalleContable</p>";
        $msgEn = "<p>Hello <strong>$nombreCreator</strong>,</p><p>The mission <em>\"$tituloMision\"</em> has been successfully completed.</p><div style='background:#131722; padding:20px; border-radius:8px; text-align:center;'><h3 style='color:#2ecc71; margin:0; font-size:24px;'>+" . number_format($fc, 0) . " FC</h3><p style='margin:10px 0;'>Rating: $estrellasHtml</p></div><p>The payment has been " . ($isCredit ? "allocated directly to reducing your ISA Honor Credit." : "transferred directly into your liquid wallet.") . "</p>";

        return self::enviar($emailCreator, "💰 Liquidación Contable y Calificación", "💰 Payment Settlement and Rating", "💰 Misión Finiquitada", "💰 Mission Settled", $msgEs, $msgEn);
    }

    public static function creditoIsaAprobadoAlCreator($emailCreator, $nombreCreator, $nombreLab, $monto)
    {
        $msgEs = "<p>Hola <strong>$nombreCreator</strong>,</p><p>El Lab <strong>$nombreLab</strong> ha **APROBADO** tu solicitud de financiamiento de honor por un monto de <strong>" . number_format($monto, 0) . " FC</strong>.</p><p>Tu acceso o cupo al activo ha sido liberado de forma transparente.</p>";
        $msgEn = "<p>Hello <strong>$nombreCreator</strong>,</p><p>The Lab <strong>$nombreLab</strong> has **APPROVED** your honor funding application for an amount of <strong>" . number_format($monto, 0) . " FC</strong>.</p><p>Your asset access or workshop spot has been released transparently.</p>";
        return self::enviar($emailCreator, "🎓 Financiación ISA Aprobada", "🎓 ISA Funding Approved", "🎓 Contrato de Honor Activo", "🎓 Honor Contract Active", $msgEs, $msgEn);
    }

    public static function notificarNuevaResenaAlLab($emailLab, $nombreLab, $nombreCreator, $estrellas)
    {
        $estrellasHtml = str_repeat('⭐', $estrellas);
        $msgEs = "<p>Hola <strong>$nombreLab</strong>,</p><p>El Creador <strong>$nombreCreator</strong> ha dejado una calificación de infraestructura sobre tu servicio:</p><div style='background:#131722; padding:15px; border-radius:6px; text-align:center; font-size:18px;'>$estrellasHtml</div><p>Tu puntaje reputacional en el ranking global ha sido recalculado.</p>";
        $msgEn = "<p>Hello <strong>$nombreLab</strong>,</p><p>The Creator <strong>$nombreCreator</strong> has left an infrastructure review on your service:</p><div style='background:#131722; padding:15px; border-radius:6px; text-align:center; font-size:18px;'>$estrellasHtml</div><p>Your reputational score on the global ranking has been updated.</p>";
        return self::enviar($emailLab, "⭐ Nueva Calificación de Infraestructura", "⭐ New Infrastructure Review", "⭐ Reseña Registrada", "⭐ Review Registered", $msgEs, $msgEn);
    }
}
<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MailService
{
    /**
     * ==========================================================================
     * 🌐 MOTOR CORE: MAQUETACIÓN INDUSTRIAL Y DESPACHO BILINGÜE
     * ==========================================================================
     * Detecta automáticamente el idioma de preferencia del usuario ('es' o 'en')
     * e inyecta los Inline CSS Premium adaptados para modo oscuro.
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

        // Maquetación Dark Mode Industrial que unifica el branding global de la suite
        $bodyHtml = "
        <div style='background-color: #0b0c10; padding: 40px 20px; font-family: \"Segoe UI\", Roboto, sans-serif; min-height: 100%;'>
            <div style='max-width: 600px; margin: 0 auto; background-color: #1c2230; border-radius: 12px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); box-shadow: 0 15px 35px rgba(0,0,0,0.5);'>
                
                <div style='background-color: #16181f; padding: 25px; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.04);'>
                    <a href='" . url('/') . "' target='_blank' style='text-decoration: none; display: inline-block;'>
                        <img src='" . asset('images/logo-full-mail.png') . "' 
                             alt='FabCoins Global' 
                             height='35' 
                             style='height: 35px; width: auto; display: block; margin: 0 auto; border: 0;'>
                    </a>
                </div>

                <div style='padding: 35px; color: #ecf0f1; font-size: 14.5px; line-height: 1.6;'>
                    <h2 style='color: #f1c40f; margin-top: 0; font-size: 18px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px;'>$tituloInterno</h2>
                    $mensajeHtml
                </div>
                <div style='background-color: #131722; padding: 20px; text-align: center; color: #7f8c8d; font-size: 11.5px; border-top: 1px solid rgba(255,255,255,0.02);'>
                    Red Global de Creadores FabCoins &copy; 2026<br>
                    <span style='font-size: 10px; color:#4a5568; display:block; margin-top:4px;'>$footerText</span>
                </div>
            </div>
        </div>";

        try {
            Mail::html($bodyHtml, function ($message) use ($emailDestino, $asunto) {
                $message->to($emailDestino)
                        ->subject($asunto)
                        ->from('no-reply@fabcoins.co', 'FabCoins');
            });
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * ==========================================================================
     * 🏢 BLOQUE 01 - ONBOARDING, ACCESO E INCLUSIÓN A LA RED
     * ==========================================================================
     */

    /**
     * CORREO 1A: INVITACIÓN Y ACTIVACIÓN DE CUENTA PARA LABS
     * Disparado exclusivamente por el SuperAdmin desde SystemController@createLab
     */
    public static function bienvenidaLab($emailLab, $nombreLab, $passwordTemporal)
    {
        $linkAcceso = route('login');

        // Cuerpo en Español
        $msgEs = "
        <p>Hola <strong>$nombreLab</strong>,</p>
        <p>Has sido invitado formalmente para unirte a la red global de infraestructura de <strong>FabCoins</strong>.</p>
        <p>Tu nodo de administración ha sido creado exitosamente. A partir de este momento, puedes ingresar al portal para activar tus activos físicos, tokenizar tu capacidad instalada anual y acuñar tus primeras monedas de respaldo contable.</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Credenciales de Acceso:</p>
            <p style='margin:0 0 4px 0;'><strong>Usuario / Email:</strong> <span style='color:#3498db;'>$emailLab</span></p>
            <p style='margin:0;'><strong>Clave Temporal:</strong> <span style='color:#2ecc71; font-family:monospace;'>$passwordTemporal</span></p>
        </div>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; box-shadow:0 4px 12px rgba(52,152,219,0.2);'>Ingresar al Portal del Lab</a>
        </p>
        <p style='font-size:12px; color:#cbd5e0; font-style:italic; margin-top:15px;'>* Por motivos de seguridad, el sistema te obligará a cambiar esta contraseña en tu primer inicio de sesión.</p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hello <strong>$nombreLab</strong>,</p>
        <p>You have been formally invited to join the <strong>FabCoins</strong> global infrastructure network.</p>
        <p>Your administration node has been successfully deployed. From this moment on, you can access the suite to activate your physical assets, tokenize your annual capacity, and mint your first backup tokens.</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Access Credentials:</p>
            <p style='margin:0 0 4px 0;'><strong>Username / Email:</strong> <span style='color:#3498db;'>$emailLab</span></p>
            <p style='margin:0;'><strong>Temporary Password:</strong> <span style='color:#2ecc71; font-family:monospace;'>$passwordTemporal</span></p>
        </div>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; box-shadow:0 4px 12px rgba(52,152,219,0.2);'>Login to Lab Portal</a>
        </p>
        <p style='font-size:12px; color:#cbd5e0; font-style:italic; margin-top:15px;'>* For security reasons, the system will require you to change this password upon your first login.</p>";

        return self::enviar($emailLab, "🏢 Invitación de Nodo de Red - FabCoins", "🏢 Network Node Invitation - FabCoins", "🏢 Activación de Cuenta Corporativa", "🏢 Corporate Node Activation", $msgEs, $msgEn);
    }

    /**
     * CORREO 1B: BIENVENIDA AUTOMÁTICA A CREADORES (MAKERS)
     * Disparado por RegisteredUserController@store tras el autoregistro público web
     */
    public static function bienvenidaCreator($emailCreator, $nombreCreator)
    {
        $linkAcceso = route('login');

        // Cuerpo en Español
        $msgEs = "
        <p>¡Hola <strong>$nombreCreator</strong>!</p>
        <p>Te damos una bienvenida oficial a la Red Global de Creadores de <strong>FabCoins</strong>.</p>
        <p>Tu cuenta como <strong>Creator</strong> ha sido validada. A partir de este momento, tienes el poder de explorar el mercado global de misiones, postular a desafíos tecnológicos financiados por laboratorios y reservar horas de uso en maquinaria industrial de alta resolución utilizando tus saldos líquidos.</p>
        <p>La fabricación digital descentralizada acaba de comenzar para ti. ¡Comienza a acumular reputación y pon tus habilidades en marcha!</p>
        <p style='text-align:center; margin:30px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#2ecc71; color:#0b0c10; padding:12px 35px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; box-shadow:0 4px 12px rgba(46,204,113,0.2);'>Explorar el Mercado de Misiones</a>
        </p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hello <strong>$nombreCreator</strong>!</p>
        <p>Welcome to the <strong>FabCoins</strong> Global Creators Network.</p>
        <p>Your account as a <strong>Creator</strong> has been verified. From this moment on, you are empowered to explore the global mission marketplace, apply for high-tech challenges funded by official laboratories, and reserve manufacturing hours on high-resolution industrial machinery using your token balance.</p>
        <p>Decentralized digital fabrication has just begun for you. Start building your reputational score and put your skills to work!</p>
        <p style='text-align:center; margin:30px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#2ecc71; color:#0b0c10; padding:12px 35px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; box-shadow:0 4px 12px rgba(46,204,113,0.2);'>Explore Mission Marketplace</a>
        </p>";

        return self::enviar($emailCreator, "🚀 ¡Bienvenido a la Red Global! - FabCoins", "🚀 Welcome to the Global Network! - FabCoins", "🚀 Cuenta de Creator Activada", "🚀 Creator Account Activated", $msgEs, $msgEn);
    }

    /**
     * CORREO 1C: PASAPORTE DE RECOVERY / RESTAURACIÓN DE CLAVE
     * Disparado por PasswordResetLinkController@store mediante los tokens nativos de Laravel
     */
    public static function recuperarPassword($emailDestino, $tokenSecureUrl)
    {
        // Cuerpo en Español
        $msgEs = "
        <p>Hola,</p>
        <p>Estás recibiendo este correo porque hemos recibido una solicitud de restauración de contraseña para tu cuenta vinculada a la red FabCoins.</p>
        <p>Si tú ejecutaste esta solicitud, puedes restablecer tus llaves de seguridad presionando el siguiente botón de auditoría:</p>
        <p style='text-align:center; margin:30px 0 20px 0;'>
            <a href='$tokenSecureUrl' style='background-color:#e74c3c; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; box-shadow:0 4px 12px rgba(231,76,60,0.2);'>Restablecer Contraseña</a>
        </p>
        <p style='font-size:12.5px; color:#bdc3c7;'>* Este enlace de recuperación expirará automáticamente en 60 minutos por motivos de seguridad criptográfica.</p>
        <hr style='border:0; border-top:1px solid rgba(255,255,255,0.05); margin:20px 0;'>
        <p style='font-size:11.5px; color:#7f8c8d; margin:0;'>Si tú no solicitaste este cambio, puedes ignorar este mensaje de forma segura; tu frase semilla y llaves de acceso siguen estando totalmente a salvo.</p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hello,</p>
        <p>You are receiving this email because we received a password reset request for your account linked to the FabCoins network.</p>
        <p>If you made this request, you can restore your security keys by clicking the following auditing button:</p>
        <p style='text-align:center; margin:30px 0 20px 0;'>
            <a href='$tokenSecureUrl' style='background-color:#e74c3c; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; box-shadow:0 4px 12px rgba(231,76,60,0.2);'>Reset Password</a>
        </p>
        <p style='font-size:12.5px; color:#bdc3c7;'>* This recovery link will automatically expire in 60 minutes for cryptographic security reasons.</p>
        <hr style='border:0; border-top:1px solid rgba(255,255,255,0.05); margin:20px 0;'>
        <p style='font-size:11.5px; color:#7f8c8d; margin:0;'>If you did not request a password reset, no further action is required; your seed phrase and access credentials remain completely safe.</p>";

        return self::enviar($emailDestino, "🔒 Restablecer Contraseña - FabCoins", "🔒 Reset Password - FabCoins", "🔒 Pasaporte de Recuperación Activo", "🔒 Security Recovery Passport", $msgEs, $msgEn);
    }

    /**
     * CORREO 1D: ALERTA DE SEGURIDAD POR CAMBIOS EN EL PERFIL
     * Disparado desde DashboardController@updateProfile
     */
    public static function notificarActualizacionPerfil($emailDestino, $nombreUsuario)
    {
        $msgEs = "
        <p>Hola <strong>$nombreUsuario</strong>,</p>
        <p>Te informamos que la información pública de tu perfil (dirección, enlaces o biografía) ha sido actualizada correctamente en la plataforma.</p>
        <p style='color:#e74c3c; font-weight:bold;'>⚠️ ¿No fuiste tú?</p>
        <p>Si no has realizado ninguna modificación en tu panel de control, por favor ingresa de inmediato a la sección de seguridad para cambiar tus claves de acceso o comunícate con el equipo de soporte central.</p>";

        $msgEn = "
        <p>Hello <strong>$nombreUsuario</strong>,</p>
        <p>We are writing to inform you that your public profile information (address, social links, or bio) has been successfully updated.</p>
        <p style='color:#e74c3c; font-weight:bold;'>⚠️ Wasn't it you?</p>
        <p>If you did not execute these changes, please log into your dashboard immediately to reset your password or contact the support team.</p>";

        return self::enviar($emailDestino, "Perfil Actualizado", "Profile Updated", "🔒 Modificación de Perfil Confirmada", "🔒 Profile Modification Confirmed", $msgEs, $msgEn);
    }

    /**
     * ==========================================================================
     * ⚙️ BLOQUE 02 - RESPALDO DE FONDOS Y EMISIÓN DE MONEDA (MINT)
     * ==========================================================================
     */

    /**
     * CORREO 2A: CONFIRMACIÓN DETALLADA DE INFRAESTRUCTURA TOKENIZADA
     * Disparado automáticamente desde AssetController@tokenise tras validar el lote
     */
    public static function notificarTokenizacion($emailLab, $nombreLab, $asuntoDummy, $totalFc, $detallesLote = [])
    {
        // Detectamos el idioma del usuario para las etiquetas fijas de la tabla
        $userLang = DB::table('users')->where('email', $emailLab)->value('preferred_lang') ?? 'es';

        $thActivo = ($userLang === 'en') ? 'Asset' : 'Activo';
        $thTokenizado = ($userLang === 'en') ? 'Tokenized' : 'Tokenizado';
        $thPrecio = ($userLang === 'en') ? 'Unit Price' : 'Precio Unitario';
        $thTotal = ($userLang === 'en') ? 'FC Minted' : 'FC Emitidos';

        $tablaHtml = '';
        if (!empty($detallesLote) && is_array($detallesLote)) {
            $tablaHtml = "
            <table style='width:100%; border-collapse:collapse; margin:20px 0; font-size:13px; background-color:#131722; border-radius:8px; overflow:hidden;'>
                <thead>
                    <tr style='background-color:rgba(255,255,255,0.02); border-bottom:1px solid rgba(255,255,255,0.06);'>
                        <th style='padding:12px 10px; text-align:left; color:#7f8c8d; font-weight:700;'>$thActivo</th>
                        <th style='padding:12px 10px; text-align:center; color:#7f8c8d; font-weight:700;'>$thTokenizado</th>
                        <th style='padding:12px 10px; text-align:center; color:#7f8c8d; font-weight:700;'>$thPrecio</th>
                        <th style='padding:12px 10px; text-align:right; color:#7f8c8d; font-weight:700;'>$thTotal</th>
                    </tr>
                </thead>
                <tbody>";
                
            foreach ($detallesLote as $item) {
                // 🎨 Asignación de color idéntico al CSS de la plataforma
                $colorBadge = '#7f8c8d'; // Gris por defecto
                if ($item['tipo'] === 'machine') { $colorBadge = '#1abc9c'; }
                elseif ($item['tipo'] === 'service') { $colorBadge = '#3498db'; }
                elseif ($item['tipo'] === 'lab') { $colorBadge = '#9b59b6'; }

                // 🎯 Selección dinámica de la unidad de medida según categoría e idioma
                $unidad = 'hrs';
                if ($item['tipo'] === 'service' && $item['subcategory'] === 'workshop') {
                    $unidad = ($userLang === 'en') ? 'slots' : 'cupos';
                }

                $tablaHtml .= "
                    <tr style='border-bottom:1px solid rgba(255,255,255,0.02);'>
                        <td style='padding:12px 10px; vertical-align: middle;'>
                            <span style='background-color:{$colorBadge}; color:#ffffff; padding:3px 8px; border-radius:4px; font-size:11px; font-weight:700; display:inline-block; white-space:nowrap;'>
                                {$item['nombre']}
                            </span>
                        </td>
                        <td style='padding:12px 10px; text-align:center; color:#cbd5e0; font-weight:600;'>
                            " . number_format($item['cantidad'], 0) . " $unidad
                        </td>
                        <td style='padding:12px 10px; text-align:center; color:#f1c40f; font-weight:600;'>
                            " . number_format($item['precio'], 0) . " FC
                        </td>
                        <td style='padding:12px 10px; text-align:right; color:#2ecc71; font-weight:700;'>
                            +" . number_format($item['fc'], 0) . " FC
                        </td>
                    </tr>";
            }
            
            $tablaHtml .= "</tbody></table>";
        }

        $msgEs = "
        <p>Hola <strong>$nombreLab</strong>,</p>
        <p>Has completado con éxito el proceso de respaldo de fondos en base a tu infraestructura y servicios disponibles.</p>
        <div style='background:rgba(46,204,113,0.04); border:1px dashed #2ecc71; padding:18px; border-radius:8px; text-align:center; margin:20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;'>Total Depositado en tu Bóveda:</span>
            <span style='font-size:26px; font-weight:800; color:#2ecc71;'>+" . number_format($totalFc, 0) . " FC</span>
        </div>
        
        <p style='margin-bottom:8px; font-weight:700; color:#ffffff; font-size:13.5px;'>📋 Resumen del lote procesado:</p>
        $tablaHtml

        <p style='margin-top:20px;'>Estos fondos ya se encuentran líquidos en tu Bóveda y listos para ser invertidos en el desarrollo de proyectos dentro de la plataforma.</p>";

        $msgEn = "
        <p>Hello <strong>$nombreLab</strong>,</p>
        <p>You have successfully completed the backing process based on your available infrastructure and services.</p>
        <div style='background:rgba(46,204,113,0.04); border:1px dashed #2ecc71; padding:18px; border-radius:8px; text-align:center; margin:20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;'>Total Deposited in Your Vault:</span>
            <span style='font-size:26px; font-weight:800; color:#2ecc71;'>+" . number_format($totalFc, 0) . " FC</span>
        </div>
        
        <p style='margin-bottom:8px; font-weight:700; color:#ffffff; font-size:13.5px;'>📋 Summary of the processed batch:</p>
        $tablaHtml

        <p style='margin-top:20px;'>These funds are now fully liquid in your Vault and ready to be used for projects and community requests within the platform.</p>";

        return self::enviar($emailLab, "🪙 Emisión de FabCoins Procesada con Éxito", "🪙 FabCoins Minting Processed Successfully", "🪙 Reporte de Emisión e Infraestructura", "🪙 Infrastructure Backup Report", $msgEs, $msgEn);
    }

    /**
     * ==========================================================================
     * 🎯 BLOQUE 03 - MERCADO DE MISIONES Y TRABAJOS (BOLSA DE TRABAJO)
     * ==========================================================================
     */

    /**
     * CORREO 3A: NOTIFICACIÓN DE NUEVA POSTULACIÓN PARA EL LAB
     * Disparado automáticamente cuando un Creator aplica a una vacante activa
     */
    public static function postulacionMisionAlLab($emailLab, $nombreLab, $nombreCreator, $tituloMision)
    {
        $linkAcceso = route('login');

        $msgEs = "
        <p>Hola <strong>$nombreLab</strong>,</p>
        <p>Te informamos que el creador <strong>$nombreCreator</strong> se ha postulado para desarrollar tu misión activa: <em>\"$tituloMision\"</em>.</p>
        <p>Puedes ingresar de inmediato a tu panel de control para auditar su perfil de trabajo, revisar sus habilidades validadas por la red y decidir si apruebas su postulación para comenzar con el desarrollo.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Revisar Postulantes en el Panel</a>
        </p>";

        $msgEn = "
        <p>Hello <strong>$nombreLab</strong>,</p>
        <p>We are writing to inform you that the creator <strong>$nombreCreator</strong> has applied to your active mission: <em>\"$tituloMision\"</em>.</p>
        <p>You can log into your dashboard immediately to review their work profile, verify their validated skills, and decide whether to approve their application to start the development process.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Review Applicants in Dashboard</a>
        </p>";

        return self::enviar($emailLab, "🚀 Nueva postulación recibida para tu misión", "🚀 New application received for your mission", "🚀 Postulación en Espera de Revisión", "🚀 Application Awaiting Review", $msgEs, $msgEn);
    }

    /**
     * CORREO 3B: CONFIRMACIÓN DE MISIÓN ASIGNADA PARA EL CREATOR
     * Disparado desde MissionController@assignCreator cuando el Lab elige al ganador
     */
    public static function misionAsignadaAlCreator($emailCreator, $nombreCreator, $nombreLab, $tituloMision, $recompensa)
    {
        $linkAcceso = route('login');

        $msgEs = "
        <p>¡Hola <strong>$nombreCreator</strong>!</p>
        <p>Te traemos excelentes noticias. El lab <strong>$nombreLab</strong> ha revisado tu perfil y te ha seleccionado para encargarte de la misión: <strong>\"$tituloMision\"</strong>.</p>
        <div style='background:rgba(52,152,219,0.04); border:1px dashed #3498db; padding:15px; border-radius:8px; text-align:center; margin:20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px;'>Fondos Asegurados en Garantía:</span>
            <span style='font-size:22px; font-weight:800; color:#3498db;'>" . number_format($recompensa, 0) . " FC</span>
        </div>
        <p><strong>¡Ya puedes empezar a trabajar!</strong> Los fondos de la recompensa ya han sido retirados de la cuenta del lab y se encuentran retenidos de forma segura. El pago está 100% garantizado y se liberará en tu billetera en cuanto entregues el trabajo terminado.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#2ecc71; color:#0b0c10; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Ver Detalles de la Misión</a>
        </p>";

        $msgEn = "
        <p>Hello <strong>$nombreCreator</strong>!</p>
        <p>We have great news for you. The lab <strong>$nombreLab</strong> has reviewed your application and selected you for the mission: <strong>\"$tituloMision\"</strong>.</p>
        <div style='background:rgba(52,152,219,0.04); border:1px dashed #3498db; padding:15px; border-radius:8px; text-align:center; margin:20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px;'>Funds Secured in Escrow:</span>
            <span style='font-size:22px; font-weight:800; color:#3498db;'>" . number_format($recompensa, 0) . " FC</span>
        </div>
        <p><strong>You are clear to start working!</strong> The reward tokens have been successfully moved from the lab's balance into a secure escrow. Payment is 100% guaranteed and will be released to your wallet as soon as the completed work is delivered.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#2ecc71; color:#0b0c10; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>View Mission Details</a>
        </p>";

        return self::enviar($emailCreator, "🎯 ¡Misión Asignada con Éxito! - FabCoins", "🎯 Mission Successfully Assigned! - FabCoins", "🎯 ¡Felicidades, el trabajo es tuyo!", "🎯 Congratulations, the job is yours!", $msgEs, $msgEn);
    }

    /**
     * CORREO 3C: LIQUIDACIÓN CONTABLE Y ENTREGA DE RECOMPENSA (PAGO)
     * Disparado desde MissionController@completeMission cuando el Lab aprueba la entrega final
     */
    public static function liquidacionMisionAlCreator($emailCreator, $nombreCreator, $tituloMision, $fc, $estrellas, $isCredit)
    {
        // Generamos el bloque visual de las estrellas otorgadas por el laboratorio
        $estrellasHtml = str_repeat('⭐', $estrellas);
        
        // Explicación sencilla de la naturaleza del pago (Amortización o Billetera Líquida)
        $detalleContableEs = $isCredit 
            ? "aplicado automáticamente como un abono directo para reducir el saldo pendiente de tu Crédito."
            : "depositado de forma directa y se encuentra 100% disponible en tu billetera líquida.";

        $detalleContableEn = $isCredit 
            ? "automatically applied as a direct payment to reduce your outstanding Credit balance."
            : "transferred directly into your liquid wallet balance and is 100% available.";

        $msgEs = "
        <p>Hola <strong>$nombreCreator</strong>,</p>
        <p>El lab ha revisado tu entrega para la misión <em>\"$tituloMision\"</em> y la ha marcado como completada con éxito.</p>
        <div style='background:#131722; padding:20px; border-radius:8px; text-align:center; margin:20px 0; border:1px solid rgba(255,255,255,0.02);'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;'>Pago Liberado:</span>
            <span style='color:#2ecc71; margin:0; font-size:26px; font-weight:800;'>+" . number_format($fc, 0) . " FC</span>
            <p style='margin:10px 0 0 0; font-size:14px; color:#ffffff;'>Calificación del Lab: <span style='letter-spacing:2px;'>$estrellasHtml</span></p>
        </div>
        <p>Siguiendo las reglas de la operación, el monto ha sido $detalleContableEs</p>
        <p style='margin-top:15px;'>Tu puntaje de reputación pública ha sido actualizado en el mapa del mercado. ¡Gracias por tu gran trabajo técnico!</p>";

        $msgEn = "
        <p>Hello <strong>$nombreCreator</strong>,</p>
        <p>The laboratory has reviewed your delivery for the mission <em>\"$tituloMision\"</em> and has marked it as successfully completed.</p>
        <div style='background:#131722; padding:20px; border-radius:8px; text-align:center; margin:20px 0; border:1px solid rgba(255,255,255,0.02);'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;'>Payment Released:</span>
            <span style='color:#2ecc71; margin:0; font-size:26px; font-weight:800;'>+" . number_format($fc, 0) . " FC</span>
            <p style='margin:10px 0 0 0; font-size:14px; color:#ffffff;'>Lab Evaluation: <span style='letter-spacing:2px;'>$estrellasHtml</span></p>
        </div>
        <p>Following the operation rules, the amount has been $detalleContableEn</p>
        <p style='margin-top:15px;'>Your public reputation score has been updated in the global network ranking. Thank you for your excellent technical work!</p>";

        return self::enviar($emailCreator, "💰 Pago Recibido - Misión Completada", "💰 Payment Received - Mission Completed", "💰 Comprobante de Pago y Calificación", "💰 Payment Settlement and Review", $msgEs, $msgEn);
    }

    /**
     * CORREO 3D: ALERTA DE NUEVA RESEÑA RECIBIDA PARA EL LAB
     * Disparado automáticamente desde el controlador cuando un Creador califica un nodo
     */
    public static function notificarNuevaResenaAlLab($emailLab, $nombreLab, $nombreCreator, $tituloContexto, $rating)
    {
        $estrellasHtml = str_repeat('⭐', $rating);

        $msgEs = "
        <p>Hola <strong>$nombreLab</strong>,</p>
        <p>El creador <strong>$nombreCreator</strong> ha registrado una nueva calificación sobre tu soporte en la misión/servicio: <strong>\"$tituloContexto\"</strong>.</p>
        <div style='background:#131722; padding:15px; border-radius:8px; text-align:center; margin:20px 0; border:1px solid rgba(255,255,255,0.02);'>
            <p style='margin:0; font-size:14px; color:#ffffff;'>Puntuación Otorgada: <span style='letter-spacing:2px;'>$estrellasHtml</span></p>
        </div>
        <p>Ingresa a tu perfil público o panel para revisar la bitácora de comentarios y seguir optimizando tu reputación de red.</p>";

        $msgEn = "
        <p>Hello <strong>$nombreLab</strong>,</p>
        <p>The creator <strong>$nombreCreator</strong> has left a new review regarding your support on the mission/service: <strong>\"$tituloContexto\"</strong>.</p>
        <div style='background:#131722; padding:15px; border-radius:8px; text-align:center; margin:20px 0; border:1px solid rgba(255,255,255,0.02);'>
            <p style='margin:0; font-size:14px; color:#ffffff;'>Rating Given: <span style='letter-spacing:2px;'>$estrellasHtml</span></p>
        </div>
        <p>Log into your public profile or dashboard to check the comment logs and keep optimizing your network reputation score.</p>";

        return self::enviar($emailLab, "⭐ Nueva Calificación Recibida - FabCoins", "⭐ New Review Received - FabCoins", "⭐ Evaluación de Reputación", "⭐ Reputation Evaluation", $msgEs, $msgEn);
    }

    /**
     * ==========================================================================
     * 📅 BLOQUE 04 - ALQUILER CONTABLE DE HARDWARE Y RESERVAS (DINÁMICO)
     * ==========================================================================
     */

    /**
     * CORREO 4A: ALERTA DE RESERVA ENTRANTE (PAGO LÍQUIDO) PARA EL LAB
     */
    public static function reservaActivoAlLab($emailLab, $nombreLab, $nombreCreator, $nombreEquipo, $parametroTexto, $totalFc, $fecha, $tipoActivo = 'machine')
    {
        // 🎯 DETECTOR DE PALABRAS SEGÚN NATURALEZA
        $conceptoEs = "un activo"; $unidadEs = "Cantidad";
        $conceptoEn = "an asset";  $unidadEn = "Requested usage";

        if ($tipoActivo === 'workshop' || $tipoActivo === 'service') {
            $conceptoEs = "un taller / servicio"; $unidadEs = "Cupos reservados";
            $conceptoEn = "a workshop / service";  $unidadEn = "Reserved slots";
        } elseif ($tipoActivo === 'lab') {
            $conceptoEs = "un espacio de trabajo"; $unidadEs = "Horas reservadas";
            $conceptoEn = "a workspace";           $unidadEn = "Reserved hours";
        } elseif ($tipoActivo === 'machine') {
            $conceptoEs = "una maquinaria";        $unidadEs = "Horas reservadas";
            $conceptoEn = "a machinery asset";     $unidadEn = "Reserved hours";
        }

        $msgEs = "
        <p>Hola <strong>$nombreLab</strong>,</p>
        <p>El creador <strong>$nombreCreator</strong> ha realizado la reserva de <strong>$conceptoEs</strong> en tu inventario utilizando sus FabCoins líquidos.</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Resumen de la Orden:</p>
            <p style='margin:0 0 4px 0;'><strong>Nombre:</strong> $nombreEquipo</p>
            <p style='margin:0 0 4px 0;'><strong>$unidadEs:</strong> $parametroTexto</p>
            <p style='margin:0 0 4px 0;'><strong>Fecha de la cita:</strong> " . date('d/m/Y', strtotime($fecha)) . "</p>
            <p style='margin:0;'><strong>Total transferido:</strong> <span style='color:#2ecc71; font-weight:bold;'>" . number_format($totalFc, 0) . " FC</span></p>
        </div>
        <p>Ingresa a tu panel de control para revisar el calendario y coordinar la entrega del servicio o la preparación del recurso.</p>";

        $msgEn = "
        <p>Hello <strong>$nombreLab</strong>,</p>
        <p>The creator <strong>$nombreCreator</strong> has successfully booked <strong>$conceptoEn</strong> from your inventory using liquid FabCoins.</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Order Summary:</p>
            <p style='margin:0 0 4px 0;'><strong>Name:</strong> $nombreEquipo</p>
            <p style='margin:0 0 4px 0;'><strong>$unidadEn:</strong> $parametroTexto</p>
            <p style='margin:0 0 4px 0;'><strong>Scheduled date:</strong> " . date('d/m/Y', strtotime($fecha)) . "</p>
            <p style='margin:0;'><strong>Total transferred:</strong> <span style='color:#2ecc71; font-weight:bold;'>" . number_format($totalFc, 0) . " FC</span></p>
        </div>
        <p>Log into your dashboard to check the schedule logs and prepare the resource for the user.</p>";

        $asuntoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "📅 Nueva Inscripción a Taller - FabCoins" : "📅 Nueva Reserva de Infraestructura - FabCoins";
        $asuntoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "📅 New Workshop Registration - FabCoins" : "📅 New Infrastructure Booking - FabCoins";

        return self::enviar($emailLab, $asuntoEs, $asuntoEn, "📅 Reserva Líquida Recibida", "📅 Liquid Booking Received", $msgEs, $msgEn);
    }

    /**
     * CORREO 4B: PROPUESTA DE CAMBIO DE FECHA (RESCHEDULE)
     */
    public static function propuestaReprogramacionActivo($emailCreator, $nombreCreator, $nombreLab, $nombreEquipo, $nuevaFecha, $tipoActivo = 'machine')
    {
        $linkAcceso = route('login');

        $recursoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "del taller" : (($tipoActivo === 'lab') ? "del espacio" : "del equipo");
        $recursoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "of the workshop" : (($tipoActivo === 'lab') ? "of the space" : "of the equipment");

        $msgEs = "
        <p>Hola <strong>$nombreCreator</strong>,</p>
        <p>El equipo técnico de <strong>$nombreLab</strong> nos informa que debido a problemas de agenda o mantenimiento físico, se debe cambiar la fecha de tu reserva $recursoEs <strong>$nombreEquipo</strong>.</p>
        <p>El laboratorio te propone el siguiente día alternativo:</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 8px; text-align:center; margin: 20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase;'>Nueva Fecha Propuesta:</span>
            <span style='font-size:18px; font-weight:800; color:#f1c40f;'>" . date('d M Y', strtotime($nuevaFecha)) . "</span>
        </div>
        <p>Por favor, ingresa a tu cuenta para <strong>Aceptar</strong> o <strong>Rechazar</strong> este cambio. Si decides rechazarlo, tus FabCoins en garantía serán devueltos de inmediato a tu billetera.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#f39c12; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Responder Reprogramación</a>
        </p>";

        $msgEn = "
        <p>Hello <strong>$nombreCreator</strong>,</p>
        <p>The technical support team at <strong>$nombreLab</strong> has informed us that due to calendar constraints or maintenance, they need to update your booking date $recursoEn <strong>$nombreEquipo</strong>.</p>
        <p>The laboratory proposes the following alternative date:</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 8px; text-align:center; margin: 20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase;'>New Proposed Date:</span>
            <span style='font-size:18px; font-weight:800; color:#f1c40f;'>" . date('d M Y', strtotime($nuevaFecha)) . "</span>
        </div>
        <p>Please log into your dashboard to either <strong>Accept</strong> or <strong>Reject</strong> this change. If you decline, your escrowed tokens will be immediately returned to your wallet balance.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#f39c12; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Respond to Reschedule</a>
        </p>";

        return self::enviar($emailCreator, "⏳ Propuesta de Cambio de Fecha - FabCoins", "⏳ Date Change Proposal - FabCoins", "⏳ Reprogramación de Calendario", "⏳ Schedule Reschedule Proposal", $msgEs, $msgEn);
    }

    /**
     * CORREO 4C: RESPUESTA A LA REPROGRAMACIÓN (AVISO PARA EL LAB)
     */
    public static function respuestaReprogramacionAlLab($emailLab, $nombreLab, $nombreCreator, $nombreEquipo, $esAceptado, $tipoActivo = 'machine')
    {
        $estadoEs = $esAceptado ? "ACEPTADO tu propuesta de nueva fecha" : "DECLINADO tu propuesta de fecha y cancelado la orden";
        $estadoEn = $esAceptado ? "ACCEPTED your new schedule proposal" : "DECLINED your schedule proposal and canceled the order";

        $recursoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "al taller" : (($tipoActivo === 'lab') ? "al espacio" : "al equipo");
        $recursoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "the workshop" : (($tipoActivo === 'lab') ? "the space" : "the equipment");

        $msgEs = "
        <p>Hola <strong>$nombreLab</strong>,</p>
        <p>El creador <strong>$nombreCreator</strong> ha <strong>$estadoEs</strong> relativo $recursoEs: <strong>$nombreEquipo</strong>.</p>
        <p>Por favor ingresa a tu panel de control para revisar los detalles actualizados de la agenda.</p>";

        $msgEn = "
        <p>Hello <strong>$nombreLab</strong>,</p>
        <p>The creator <strong>$nombreCreator</strong> has <strong>$estadoEn</strong> regarding $recursoEn: <strong>$nombreEquipo</strong>.</p>
        <p>Please log into your dashboard to check the updated schedule logs.</p>";

        return self::enviar($emailLab, "📅 Respuesta de Reprogramación - FabCoins", "📅 Reschedule Response - FabCoins", "📅 Actualización de Calendario", "📅 Schedule Update", $msgEs, $msgEn);
    }

    /**
     * CORREO 4D: CONFIRMACIÓN DE RESERVA APROBADA
     */
    public static function confirmacionReservaActivo($emailCreator, $nombreCreator, $nombreLab, $nombreEquipo, $fecha, $horas, $tipoActivo = 'machine')
    {
        // 🎯 ETIQUETAS DINÁMICAS DE UNIDAD Y TÍTULOS
        $tituloEs = "Tu turno de uso ha sido asegurado en la agenda del laboratorio.";
        $tituloEn = "Your time slot is now secured in the laboratory logbook.";
        $unidadEs = "Horas reservadas"; $unidadEn = "Reserved hours";
        $cantTexto = "$horas horas";

        if ($tipoActivo === 'workshop' || $tipoActivo === 'service') {
            $tituloEs = "Tu inscripción ha sido confirmada en la lista de asistencia.";
            $tituloEn = "Your registration has been confirmed in the attendance logs.";
            $unidadEs = "Cupos asegurados"; $unidadEn = "Secured slots";
            $cantTexto = "$horas cupos";
        }

        $msgEs = "
        <p>¡Hola <strong>$nombreCreator</strong>!</p>
        <p>Tu solicitud ha sido aprobada por el equipo de <strong>$nombreLab</strong>. $tituloEs</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Datos Confirmados:</p>
            <p style='margin:0 0 4px 0;'><strong>Nombre:</strong> $nombreEquipo</p>
            <p style='margin:0 0 4px 0;'><strong>Fecha programada:</strong> " . date('d M Y', strtotime($fecha)) . "</p>
            <p style='margin:0;'><strong>$unidadEs:</strong> $cantTexto</p>
        </div>
        <p style='margin-bottom:6px; font-weight:bold; color:#ffffff;'>⚠️ Normativas de Asistencia y Seguridad:</p>
        <ul style='margin-top:0; padding-left:20px; color:#cbd5e0; font-size:13.5px;'>
            <li>Llega con 15 minutes de anticipación para coordinar con los encargados de soporte.</li>
            <li>Usa calzado cerrado y cabello recogido si vas a operar herramientas o maquinaria física.</li>
            <li>Si requieres materiales adicionales o archivos de preparación previa, revísalos antes con el nodo.</li>
        </ul>";

        $msgEn = "
        <p>Hello <strong>$nombreCreator</strong>!</p>
        <p>Your request has been officially approved by the team at <strong>$nombreLab</strong>. $tituloEn</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Confirmed Data:</p>
            <p style='margin:0 0 4px 0;'><strong>Name:</strong> $nombreEquipo</p>
            <p style='margin:0 0 4px 0;'><strong>Scheduled Date:</strong> " . date('d M Y', strtotime($fecha)) . "</p>
            <p style='margin:0;'><strong>$unidadEn:</strong> $cantTexto</p>
        </div>
        <p style='margin-bottom:6px; font-weight:bold; color:#ffffff;'>⚠️ Attendance & Safety Regulations:</p>
        <ul style='margin-top:0; padding-left:20px; color:#cbd5e0; font-size:13.5px;'>
            <li>Please arrive 15 minutes early to coordinate with the support staff.</li>
            <li>Wear closed-toe shoes and tie back long hair if operating physical tools or machinery.</li>
            <li>If you need extra raw materials or setup files, verify them with the node beforehand.</li>
        </ul>";

        $asuntoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "✅ Inscripción Confirmada al Taller - FabCoins" : "✅ Tu Reserva ha sido Confirmada - FabCoins";
        $asuntoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "✅ Workshop Registration Confirmed - FabCoins" : "✅ Your Reservation has been Confirmed - FabCoins";

        return self::enviar($emailCreator, $asuntoEs, $asuntoEn, ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "✅ Registro Exitoso" : "✅ Reserva Confirmada", ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "✅ Registration Successful" : "✅ Booking Confirmed", $msgEs, $msgEn);
    }

    /**
     * CORREO 4E: SOLICITUD DE CRÉDITO ISA / ALQUILER DE HARDWARE
     */
    public static function solicitudCreditoActivo($emailLab, $nombreLab, $nombreCreator, $nombreEquipo, $horas, $montoCredito, $tipoActivo = 'machine')
    {
        $linkAcceso = route('login');

        $conceptoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "un taller / servicio" : (($tipoActivo === 'lab') ? "un espacio" : "un equipo");
        $conceptoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "a workshop / service" : (($tipoActivo === 'lab') ? "a space" : "an equipment");
        $unidadEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "Cupos requeridos" : "Tiempo estimado";
        $unidadEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "Requested slots" : "Estimated time";

        $msgEs = "
        <p>Hola <strong>$nombreLab</strong>,</p>
        <p>El creador <strong>$nombreCreator</strong> ha solicitado reservar <strong>$conceptoEs</strong> en tu sede utilizando un crédito debido a que no cuenta con saldo líquido suficiente.</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Detalles de la Solicitud:</p>
            <p style='margin:0 0 4px 0;'><strong>Nombre:</strong> $nombreEquipo</p>
            <p style='margin:0 0 4px 0;'><strong>$unidadEs:</strong> $horas</p>
            <p style='margin:0; '><strong>Crédito solicitado:</strong> <span style='color:#f1c40f; font-weight:bold;'>" . number_format($montoCredito, 0) . " FC</span></p>
        </div>
        <p>Por favor, ingresa a tu panel de control para revisar esta propuesta de financiamiento, evaluar el perfil del alumno y decidir si apruebas la solicitud.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Revisar Solicitud en el Panel</a>
        </p>";

        $msgEn = "
        <p>Hello <strong>$nombreLab</strong>,</p>
        <p>The creator <strong>$nombreCreator</strong> has requested to reserve <strong>$conceptoEn</strong> at your lab using a credit because they do not have enough liquid balance.</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Request Details:</p>
            <p style='margin:0 0 4px 0;'><strong>Name:</strong> $nombreEquipo</p>
            <p style='margin:0 0 4px 0;'><strong>$unidadEn:</strong> $horas</p>
            <p style='margin:0; '><strong>Requested Credit:</strong> <span style='color:#f1c40f; font-weight:bold;'>" . number_format($montoCredito, 0) . " FC</span></p>
        </div>
        <p>Please log into your dashboard to review this financing proposal, evaluate the creator's profile, and decide whether to approve the request.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Review Request in Dashboard</a>
        </p>";

        $asuntoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "📅 Solicitud de Taller con Crédito - FabCoins" : "📅 Nueva Solicitud de Alquiler con Crédito ISA - FabCoins";
        $asuntoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "📅 Workshop Request with Credit - FabCoins" : "📅 New Rental Request with ISA Credit - FabCoins";

        return self::enviar($emailLab, $asuntoEs, $asuntoEn, "📅 Solicitud de Financiamiento Recibida", "📅 Financing Request Received", $msgEs, $msgEn);
    }

    /**
     * CORREO 4F: RESOLUCIÓN DE CRÉDITO ISA (AVISO PARA EL CREATOR)
     */
    public static function resolucionCreditoAlCreator($emailCreator, $nombreCreator, $nombreLab, $monto, $esAprobado, $tipoActivo = 'machine')
    {
        $asuntoEs = $esAprobado ? "✅ Crédito Aprobado - FabCoins" : "❌ Crédito Rechazado - FabCoins";
        $asuntoEn = $esAprobado ? "✅ Credit Approved - FabCoins" : "❌ Credit Declined - FabCoins";
        
        $destinoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "tu inscripción al taller" : "tu reserva de tiempo";
        $destinoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "your workshop registration" : "your time reservation";

        $msgEs = $esAprobado 
            ? "<p>¡Hola <strong>$nombreCreator</strong>!</p><p>El equipo de <strong>$nombreLab</strong> ha <strong>APROBADO</strong> tu solicitud de crédito por un valor de <strong>" . number_format($monto, 0) . " FC</strong>. Debido a esto, $destinoEs ya se encuentra activa y registrada.</p>"
            : "<p>Hola <strong>$nombreCreator</strong>,</p><p>Te informamos que tu solicitud de crédito por un valor de <strong>" . number_format($monto, 0) . " FC</strong> fue rechazada por <strong>$nombreLab</strong>. El saldo parcial que se había retenido fue devuelto a tu billetera.</p>";

        $msgEn = $esAprobado
            ? "<p>Hello <strong>$nombreCreator</strong>!</p><p>The team at <strong>$nombreLab</strong> has <strong>APPROVED</strong> your credit request for <strong>" . number_format($monto, 0) . " FC</strong>. Therefore, $destinoEn is now active and secured.</p>"
            : "<p>Hello <strong>$nombreCreator</strong>,</p><p>We inform you that your credit request for <strong>" . number_format($monto, 0) . " FC</strong> was declined by <strong>$nombreLab</strong>. Any held balance has been returned to your wallet balance.</p>";

        return self::enviar($emailCreator, $asuntoEs, $asuntoEn, $esAprobado ? "✅ Crédito Autorizado" : "❌ Crédito No Aprobado", $esAprobado ? "✅ Credit Authorized" : "❌ Credit Declined", $msgEs, $msgEn);
    }

    /**
     * CORREO 4G: RESERVA RECHAZADA (AVISO PARA EL CREATOR)
     */
    public static function reservaRechazadaAlCreator($emailCreator, $nombreCreator, $nombreLab, $nombreEquipo, $tipoActivo = 'machine')
    {
        $conceptoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "de inscripción para el taller" : "de reserva para el activo";
        $conceptoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "registration request for the workshop" : "reservation request for the asset";

        $msgEs = "
        <p>Hola <strong>$nombreCreator</strong>,</p>
        <p>Te informamos que tu solicitud $conceptoEs <strong>$nombreEquipo</strong> fue rechazada por el laboratorio <strong>$nombreLab</strong>.</p>
        <p>Los fondos en garantía han sido devueltos por completo al saldo de tu billetera líquida.</p>";

        $msgEn = "
        <p>Hello <strong>$nombreCreator</strong>,</p>
        <p>We inform you that your $conceptoEn <strong>$nombreEquipo</strong> was declined by the laboratory <strong>$nombreLab</strong>.</p>
        <p>The escrowed funds have been fully refunded to your liquid wallet balance.</p>";

        $asuntoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "❌ Inscripción Rechazada - FabCoins" : "❌ Reserva Rechazada - FabCoins";
        $asuntoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "❌ Registration Declined - FabCoins" : "❌ Reservation Declined - FabCoins";

        return self::enviar($emailCreator, $asuntoEs, $asuntoEn, "❌ Solicitud No Procesada", "❌ Request Not Processed", $msgEs, $msgEn);
    }

    /**
     * ==========================================================================
     * 🤝 EXTENSIÓN FINTECH: MISIONES EXCLUSIVAS DE RETORNO / AMORTIZACIÓN ISA
     * ==========================================================================
     */

    /**
     * CORREO 4H: NOTIFICACIÓN DE MISIÓN DIRIGIDA PARA AMORTIZACIÓN (CREACIÓN)
     * Disparado desde MissionController@store cuando la misión tiene un target_creator_id
     */
    public static function misionDirigidaAmortizacionCreada($emailCreator, $nombreCreator, $nombreLab, $tituloMision, $recompensa)
    {
        $linkAcceso = route('login');

        // Cuerpo en Español
        $msgEs = "
        <p>¡Hola <strong>$nombreCreator</strong>!</p>
        <p>El lab<strong>$nombreLab</strong> ha publicado una <strong>Misión Exclusiva</strong> dirigida especialmente a ti.</p>
        <div style='background:rgba(241,196,15,0.04); border:1px dashed #f1c40f; padding:15px; border-radius:8px; text-align:center; margin:20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px;'>Monto de Cobertura en Escrow:</span>
            <span style='font-size:22px; font-weight:800; color:#f1c40f;'>" . number_format($recompensa, 0) . " FC</span>
        </div>
        <p><strong>🚨 Nota:</strong> Al tratarse de una misión de retorno vinculada a tu línea de financiamiento activa, el valor de la recompensa se aplicará de forma directa para reducir o liquidar tu saldo deudor con este lab.</p>
        <p>Es una oportunidad perfecta para saldar tu compromiso utilizando tus habilidades técnicas. ¡Revisa los detalles y ponte en marcha!</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#f1c40f; color:#0b0c10; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Ver Detalles de la Misión</a>
        </p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hello <strong>$nombreCreator</strong>!</p>
        <p>The lab<strong>$nombreLab</strong> has published an <strong>Exclusive Mission</strong> tailored specifically for you.</p>
        <div style='background:rgba(241,196,15,0.04); border:1px dashed #f1c40f; padding:15px; border-radius:8px; text-align:center; margin:20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px;'>Escrow Coverage Amount:</span>
            <span style='font-size:22px; font-weight:800; color:#f1c40f;'>" . number_format($recompensa, 0) . " FC</span>
        </div>
        <p><strong>🚨 Note:</strong> As an exclusive payback mission linked to your active financing agreement, the reward value will be directly applied to reduce or settle your outstanding debt with this lab.</p>
        <p>It is the perfect opportunity to fulfill your commitment using your technical talent. Check the requirements and get started!</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#f1c40f; color:#0b0c10; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>View Mission Details</a>
        </p>";

        return self::enviar($emailCreator, "🎯 Misión Exclusiva de Amortización - FabCoins", "🎯 Exclusive Amortization Mission - FabCoins", "🎯 Misión de Honor Asignada", "🎯 Honor Mission Assigned", $msgEs, $msgEn);
    }

    /**
     * CORREO 4I: EL CREADOR ACEPTÓ LA MISIÓN DE HONOR (AVISO PARA EL LAB)
     * Se dispara desde el controlador del Creator cuando Henry acepta la invitación
     */
    public static function misionDirigidaAceptadaAlLab($emailLab, $nombreLab, $nombreCreator, $tituloMision, $recompensa)
    {
        $linkAcceso = route('login');

        $msgEs = "
        <p>Hola <strong>$nombreLab</strong>,</p>
        <p>Te informamos que el creador <strong>$nombreCreator</strong> ha <strong>ACEPTADO</strong> formalmente la Misión que le dirigiste: <strong>\"$tituloMision\"</strong>.</p>
        <p>El acuerdo de amortización por un valor de <strong>" . number_format($recompensa, 0) . " FC</strong> ha entrado en fase de ejecución activa. El creador ya se encuentra habilitado para desarrollar las tareas técnicas encomendadas.</p>
        <p>Una vez que el trabajo sea entregado en tu sede, recuerda ingresar al panel de control para auditar las habilidades del creador y liberar el colateral para reducir su deuda.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Monitorear Misiones en Panel</a>
        </p>";

        $msgEn = "
        <p>Hello <strong>$nombreLab</strong>,</p>
        <p>We are writing to inform you that the creator <strong>$nombreCreator</strong> has formally <strong>ACCEPTED</strong> the Mission you directed to them: <strong>\"$tituloMision\"</strong>.</p>
        <p>The amortization agreement for <strong>" . number_format($recompensa, 0) . " FC</strong> is now in active execution. The creator is now authorized to perform the technical tasks specified.</p>
        <p>Once the work is delivered to your workshop, remember to log into your dashboard to evaluate the creator's skills and release the escrowed tokens to reduce their debt.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Monitor Missions in Dashboard</a>
        </p>";

        return self::enviar($emailLab, "🤝 Misión de Honor Aceptada - FabCoins", "🤝 Honor Mission Accepted - FabCoins", "🤝 Compromiso de Amortización Activo", "🤝 Payback Commitment Active", $msgEs, $msgEn);
    }

    /**
     * CORREO 4J: COMPROBANTE DE CRÉDITO AMORTIZADO (CULMINACIÓN)
     * Disparado desde MissionController@completeMission cuando la labor de retorno se aprueba
     */
    public static function misionDirigidaAmortizacionCulminada($emailCreator, $nombreCreator, $tituloMision, $montoAmortizado, $estrellas, $deudaRestante)
    {
        $estrellasHtml = str_repeat('⭐', $estrellas);

        $saldoDeudaTextoEs = ($deudaRestante > 0) 
            ? "Tu saldo deudor remanente con el lab es de <strong>" . number_format($deudaRestante, 0) . " FC</strong>."
            : "<strong>🎉 ¡Felicidades! Tu contrato de financiamiento con este lab ha sido saldado al 100%.</strong>";

        $saldoDeudaTextoEn = ($deudaRestante > 0) 
            ? "Your remaining outstanding balance with the lab is <strong>" . number_format($deudaRestante, 0) . " FC</strong>."
            : "<strong>🎉 Congratulations! Your financing agreement with this lab has been 100% settled.</strong>";

        // Cuerpo en Español
        $msgEs = "
        <p>Hola <strong>$nombreCreator</strong>,</p>
        <p>El lab ha auditado tu entrega final para la misión <em>\"$tituloMision\"</em> y la ha aprobado satisfactoriamente.</p>
        <div style='background:#131722; padding:20px; border-radius:8px; text-align:center; margin:20px 0; border:1px solid rgba(255,255,255,0.02);'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;'>Monto Amortizado a tu Deuda:</span>
            <span style='color:#f1c40f; margin:0; font-size:26px; font-weight:800;'>-" . number_format($montoAmortizado, 0) . " FC</span>
            <p style='margin:10px 0 0 0; font-size:14px; color:#ffffff;'>Evaluación del Lab: <span style='letter-spacing:2px;'>$estrellasHtml</span></p>
        </div>
        <p>Los fondos en custodia han sido liberados y transferidos directamente a la tesorería del lab para amortizar tu crédito. $saldoDeudaTextoEs</p>
        <p style='margin-top:15px;'>Tus habilidades validadas y tu puntuación de reputación pública han sido actualizadas en la red. ¡Gracias por tu compromiso!</p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hello <strong>$nombreCreator</strong>,</p>
        <p>The lab has audited your final delivery for the mission <em>\"$tituloMision\"</em> and has successfully approved it.</p>
        <div style='background:#131722; padding:20px; border-radius:8px; text-align:center; margin:20px 0; border:1px solid rgba(255,255,255,0.02);'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;'>Amount Amortized to Your Debt:</span>
            <span style='color:#f1c40f; margin:0; font-size:26px; font-weight:800;'>-" . number_format($montoAmortizado, 0) . " FC</span>
            <p style='margin:10px 0 0 0; font-size:14px; color:#ffffff;'>Lab Evaluation: <span style='letter-spacing:2px;'>$estrellasHtml</span></p>
        </div>
        <p>The escrowed tokens have been released and transferred directly to the lab's vault to amortize your credit line. $saldoDeudaTextoEn</p>
        <p style='margin-top:15px;'>Your validated skills and public reputation score have been successfully updated in the network. Thank you for your commitment!</p>";

        return self::enviar($emailCreator, "🪙 Comprobante de Amortización - FabCoins", "🪙 Amortization Receipt - FabCoins", "🪙 Crédito Amortizado", "🪙 Credit Amortized", $msgEs, $msgEn);
    }
    
}
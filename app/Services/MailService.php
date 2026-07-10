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
    public static function bienvenidaLab($emailLab, $nombreLab, $urlAccesoSeguro)
    {
        // Cuerpo en Español (Activación Fintech Limpia)
        $msgEs = "
        <p>¡Te damos la más cordial bienvenida, <strong>$nombreLab</strong>!</p>
        <p>Nos alegra mucho contarte que tu espacio dentro de la comunidad de <strong>FabCoins</strong> ya está listo para ti.</p>
        <p>A partir de ahora, tienes acceso a tu propio panel de control, donde podrás publicar tus equipos disponibles, coordinar talleres interactivos y conectar de forma directa con los creadores de nuestra red para dar vida a proyectos increíbles.</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Tus datos de acceso institucional:</p>
            <p style='margin:0;'><strong>Usuario / Correo:</strong> <span style='color:#3498db;'>$emailLab</span></p>
        </div>
        <p>Para activar tu cuenta en la red de forma segura y establecer tu contraseña personal por primera vez, por favor haz clic en el siguiente botón:</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$urlAccesoSeguro' style='background-color:#2ecc71; color:#0b0c10; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; box-shadow:0 4px 12px rgba(46,204,113,0.2);'>Activar mi Cuenta y Panel</a>
        </p>
        <p style='font-size:12px; color:#cbd5e0; font-style:italic; margin-top:15px;'>* Este enlace de acceso es de un solo uso y se encuentra cifrado digitalmente para tu seguridad.</p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>A very warm welcome, <strong>$nombreLab</strong>!</p>
        <p>We are thrilled to let you know that your space within the <strong>FabCoins</strong> community is ready for you.</p>
        <p>From now on, you have access to your personal dashboard where you can easily share your available equipment, host interactive workshops, and connect directly with creators in our network to bring amazing projects to life.</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Your institutional access details:</p>
            <p style='margin:0;'><strong>Username / Email:</strong> <span style='color:#3498db;'>$emailLab</span></p>
        </div>
        <p>To securely activate your network account and configure your unique personal password, please click the button below:</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$urlAccesoSeguro' style='background-color:#2ecc71; color:#0b0c10; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; box-shadow:0 4px 12px rgba(46,204,113,0.2);'>Activate My Account</a>
        </p>
        <p style='font-size:12px; color:#cbd5e0; font-style:italic; margin-top:15px;'>* This invitation token is for single-use only and is cryptographically secured.</p>";

        return self::enviar($emailLab, "🏢 ¡Te damos la bienvenida a FabCoins!", "🏢 Welcome to FabCoins!", "🏢 Activación de Cuenta", "🏢 Account Activation", $msgEs, $msgEn);
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
        <p>¡Hola, <strong>$nombreCreator</strong>!</p>
        <p>Qué alegría que te unas a la Red Global de Creadores de <strong>FabCoins</strong>. ¡Tu cuenta ya está activa y lista para usar!</p>
        <p>A partir de este momento, puedes explorar diversas misiones de innovación abiertas, postular a desafíos tecnológicos impulsados por labs y reservar horas de uso en las mejores máquinas para fabricar tus propias ideas.</p>
        <p>El viaje del diseño y la fabricación digital descentralizada acaba de comenzar para ti. ¡Es hora de poner tus habilidades en marcha y sumar tus primeros puntos!</p>
        <p style='text-align:center; margin:30px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#2ecc71; color:#0b0c10; padding:12px 35px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; box-shadow:0 4px 12px rgba(46,204,113,0.2);'>Explorar Misiones Disponibles</a>
        </p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hi <strong>$nombreCreator</strong>!</p>
        <p>We're so excited to welcome you to the <strong>FabCoins</strong> Global Creators Network. Your account is active and ready to go!</p>
        <p>From this moment on, you are fully empowered to explore open innovation missions, apply for exciting tech challenges funded by official labs, and book time slots on high-end machinery to turn your ideas into reality.</p>
        <p>Your digital fabrication journey has just started. It's time to put your talents to work and build up your creator score!</p>
        <p style='text-align:center; margin:30px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#2ecc71; color:#0b0c10; padding:12px 35px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; box-shadow:0 4px 12px rgba(46,204,113,0.2);'>Explore Open Missions</a>
        </p>";

        return self::enviar($emailCreator, "🚀 ¡Te damos la bienvenida a la comunidad! - FabCoins", "🚀 Welcome to the Community! - FabCoins", "🚀 Tu cuenta de Creator está lista", "🚀 Creator Account Ready", $msgEs, $msgEn);
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
        <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en la red de FabCoins.</p>
        <p>No te preocupes, puedes elegir una nueva clave de forma segura haciendo clic en el siguiente botón:</p>
        <p style='text-align:center; margin:30px 0 20px 0;'>
            <a href='$tokenSecureUrl' style='background-color:#e74c3c; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; box-shadow:0 4px 12px rgba(231,76,60,0.2);'>Crear Nueva Contraseña</a>
        </p>
        <p style='font-size:12.5px; color:#bdc3c7;'>* Por razones de seguridad, este enlace estará activo únicamente durante los próximos 60 minutos.</p>
        <hr style='border:0; border-top:1px solid rgba(255,255,255,0.05); margin:20px 0;'>
        <p style='font-size:11.5px; color:#7f8c8d; margin:0;'>Si tú no solicitaste este cambio, no te preocupes; puedes ignorar este correo tranquilamente. Tus datos de acceso siguen estando completamente protegidos y a salvo.</p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hello there,</p>
        <p>We received a request to reset the password for your FabCoins network account.</p>
        <p>Don't worry, you can easily set a new password by clicking the button below:</p>
        <p style='text-align:center; margin:30px 0 20px 0;'>
            <a href='$tokenSecureUrl' style='background-color:#e74c3c; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block; box-shadow:0 4px 12px rgba(231,76,60,0.2);'>Set New Password</a>
        </p>
        <p style='font-size:12.5px; color:#bdc3c7;'>* For your security, this reset link will only be valid for the next 60 minutes.</p>
        <hr style='border:0; border-top:1px solid rgba(255,255,255,0.05); margin:20px 0;'>
        <p style='font-size:11.5px; color:#7f8c8d; margin:0;'>If you didn't make this request, you can safely ignore this email. Your current login credentials remain perfectly secure.</p>";

        return self::enviar($emailDestino, "🔒 Restablecer Contraseña - FabCoins", "🔒 Reset Password - FabCoins", "🔒 Recuperación de Contraseña", "🔒 Password Recovery", $msgEs, $msgEn);
    }

    /**
     * CORREO 1D: ALERTA DE SEGURIDAD POR CAMBIOS EN EL PERFIL
     * Disparado desde DashboardController@updateProfile
     */
    public static function notificarActualizacionPerfil($emailDestino, $nombreUsuario)
    {
        // Cuerpo en Español
        $msgEs = "
        <p>Hola, <strong>$nombreUsuario</strong>:</p>
        <p>Te escribimos para avisarte que los datos de tu perfil (como tu biografía, dirección o enlaces de redes) se actualizaron correctamente desde tu panel de control.</p>
        <p style='color:#e74c3c; font-weight:bold;'>⚠️ ¿No realizaste este cambio?</p>
        <p>Si no has modificado tu perfil recientemente, por favor ingresa de inmediato a la sección de seguridad de tu cuenta para cambiar tu contraseña, o ponte en contacto con nuestro equipo de soporte para ayudarte a proteger tu espacio.</p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hello <strong>$nombreUsuario</strong>,</p>
        <p>We are writing to let you know that your profile details (such as your bio, address, or social links) were just successfully updated from your dashboard.</p>
        <p style='color:#e74c3c; font-weight:bold;'>⚠️ Wasn't this you?</p>
        <p>If you haven't made any changes lately, please log into your account settings right away to update your password, or reach out to our support team so we can help you keep your account safe.</p>";

        return self::enviar($emailDestino, "Perfil Actualizado", "Profile Updated", "🔒 Confirmación de cambios en tu perfil", "🔒 Profile Updates Confirmed", $msgEs, $msgEn);
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

        $thActivo = ($userLang === 'en') ? 'Asset / Service' : 'Equipo o Taller';
        $thTokenizado = ($userLang === 'en') ? 'Available' : 'Disponible';
        $thPrecio = ($userLang === 'en') ? 'Value' : 'Valor Unitario';
        $thTotal = ($userLang === 'en') ? 'Total FC' : 'Total en FC';

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
                $colorBadge = '#7f8c8d';
                if ($item['tipo'] === 'machine') { $colorBadge = '#1abc9c'; }
                elseif ($item['tipo'] === 'service') { $colorBadge = '#3498db'; }
                elseif ($item['tipo'] === 'lab') { $colorBadge = '#9b59b6'; }

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

        // Cuerpo en Español (Adiós términos fríos, hola valor real)
        $msgEs = "
        <p>¡Hola, <strong>$nombreLab</strong>!</p>
        <p>Queremos confirmarte que tus nuevos equipos y servicios ya se registraron y habilitaron correctamente en el sistema.</p>
        <div style='background:rgba(46,204,113,0.04); border:1px dashed #2ecc71; padding:18px; border-radius:8px; text-align:center; margin:20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;'>Capacidad total respaldada en tu cuenta:</span>
            <span style='font-size:26px; font-weight:800; color:#2ecc71;'>+" . number_format($totalFc, 0) . " FC</span>
        </div>
        
        <p style='margin-bottom:8px; font-weight:700; color:#ffffff; font-size:13.5px;'>📋 Este es el resumen de lo que acabas de habilitar:</p>
        $tablaHtml

        <p style='margin-top:20px;'>Este balance ya figura disponible en tu cuenta. A partir de ahora, la comunidad podrá ver tus recursos y usarlos para colaborar en los diferentes proyectos y misiones de la plataforma.</p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hi <strong>$nombreLab</strong>!</p>
        <p>We are happy to confirm that your new equipment and services are now fully registered and live in the system.</p>
        <div style='background:rgba(46,204,113,0.04); border:1px dashed #2ecc71; padding:18px; border-radius:8px; text-align:center; margin:20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px;'>Total capacity backed in your account:</span>
            <span style='font-size:26px; font-weight:800; color:#2ecc71;'>+" . number_format($totalFc, 0) . " FC</span>
        </div>
        
        <p style='margin-bottom:8px; font-weight:700; color:#ffffff; font-size:13.5px;'>📋 Here is the summary of what you just enabled:</p>
        $tablaHtml

        <p style='margin-top:20px;'>This balance is now updated in your account. From this moment on, the community can find your resources and book them to collaborate on open projects and missions.</p>";

        return self::enviar($emailLab, "🪙 Tus equipos y servicios ya están listos en la red", "🪙 Your equipment and services are now live", "🪙 Reporte de recursos habilitados", "🪙 Enabled Resources Report", $msgEs, $msgEn);
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

        // Cuerpo en Español
        $msgEs = "
        <p>¡Hola, <strong>$nombreLab</strong>!</p>
        <p>Te contamos que el creador <strong>$nombreCreator</strong> tiene mucho interés en ayudarte y se ha postulado para tu misión activa: <em>\"$tituloMision\"</em>.</p>
        <p>Date una vuelta por tu panel de control cuando quieras para conocer su perfil, ver en qué proyectos ha participado y decidir si hacen equipo para empezar a trabajar juntos.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Ver Candidatos en mi Panel</a>
        </p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hi <strong>$nombreLab</strong>!</p>
        <p>We wanted to let you know that the creator <strong>$nombreCreator</strong> is excited to help and has applied to your active mission: <em>\"$tituloMision\"</em>.</p>
        <p>Drop by your dashboard whenever you can to check out their profile, see their past projects, and decide if you want to team up to start creating together.</p>
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

        // Cuerpo en Español
        $msgEs = "
        <p>¡Hola, <strong>$nombreCreator</strong>!</p>
        <p>Te traemos una gran noticia: el equipo de <strong>$nombreLab</strong> revisó tu postulación y te ha seleccionado para hacerte cargo de la misión: <strong>\"$tituloMision\"</strong>.</p>
        <div style='background:rgba(52,152,219,0.04); border:1px dashed #3498db; padding:15px; border-radius:8px; text-align:center; margin:20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px;'>Recompensa asegurada en la plataforma:</span>
            <span style='font-size:22px; font-weight:800; color:#3498db;'>" . number_format($recompensa, 0) . " FC</span>
        </div>
        <p><strong>¡Ya tienes luz verde para empezar!</strong> Queremos contarte que los FabCoins de la recompensa ya están separados y guardados de forma segura en la plataforma. Tu pago está totalmente garantizado y se sumará a tu cuenta automáticamente apenas entregues el trabajo terminado.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#2ecc71; color:#0b0c10; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Ver Detalles de la Misión</a>
        </p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hi <strong>$nombreCreator</strong>!</p>
        <p>We have wonderful news: the team at <strong>$nombreLab</strong> reviewed your application and selected you to take on the mission: <strong>\"$tituloMision\"</strong>.</p>
        <div style='background:rgba(52,152,219,0.04); border:1px dashed #3498db; padding:15px; border-radius:8px; text-align:center; margin:20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px;'>Reward secured on the platform:</span>
            <span style='font-size:22px; font-weight:800; color:#3498db;'>" . number_format($recompensa, 0) . " FC</span>
        </div>
        <p><strong>You are all set to start working!</strong> The FabCoins for the reward are already set aside and safely held by the platform. Your payment is fully guaranteed and will be automatically added to your balance as soon as you deliver the completed work.</p>
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
        $estrellasHtml = str_repeat('⭐', $estrellas);
        
        // Explicación sencilla de la naturaleza del pago
        $detalleContableEs = $isCredit 
            ? "aplicado automáticamente como un abono directo para reducir el saldo pendiente de tu crédito con el laboratorio."
            : "añadido directamente a tu saldo y ya lo tienes 100% disponible en tu cuenta para lo que necesites.";

        $detalleContableEn = $isCredit 
            ? "automatically applied to reduce your outstanding credit balance with the laboratory."
            : "transferred directly into your account and is 100% available for you to use.";

        // Cuerpo en Español
        $msgEs = "
        <p>¡Hola, <strong>$nombreCreator</strong>!</p>
        <p>Te contamos que el laboratorio revisó tu entrega para la misión <em>\"$tituloMision\"</em> y todo quedó excelente. ¡Muchísimas gracias por tu gran trabajo técnico!</p>
        <div style='background:#131722; padding:20px; border-radius:8px; text-align:center; margin:20px 0; border:1px solid rgba(255,255,255,0.02);'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;'>Monto Liberado:</span>
            <span style='color:#2ecc71; margin:0; font-size:26px; font-weight:800;'>+" . number_format($fc, 0) . " FC</span>
            <p style='margin:10px 0 0 0; font-size:14px; color:#ffffff;'>Calificación recibida: <span style='letter-spacing:2px;'>$estrellasHtml</span></p>
        </div>
        <p>Siguiendo las reglas acordadas, el monto ha sido $detalleContableEs</p>
        <p style='margin-top:15px;'>Tu puntuación de estrella de la comunidad ya se actualizó en tu perfil público. ¡A por el siguiente desafío!</p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hi <strong>$nombreCreator</strong>,</p>
        <p>The laboratory has reviewed your delivery for the mission <em>\"$tituloMision\"</em> and everything looks amazing. Thank you for your excellent technical work!</p>
        <div style='background:#131722; padding:20px; border-radius:8px; text-align:center; margin:20px 0; border:1px solid rgba(255,255,255,0.02);'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px;'>Released Amount:</span>
            <span style='color:#2ecc71; margin:0; font-size:26px; font-weight:800;'>+" . number_format($fc, 0) . " FC</span>
            <p style='margin:10px 0 0 0; font-size:14px; color:#ffffff;'>Evaluation: <span style='letter-spacing:2px;'>$estrellasHtml</span></p>
        </div>
        <p>Following the rules, the amount has been $detalleContableEn</p>
        <p style='margin-top:15px;'>Your community score has been successfully updated on your public profile. Let's find the next challenge!</p>";

        return self::enviar($emailCreator, "💰 Pago Recibido - Misión Completada", "💰 Payment Received - Mission Completed", "💰 Comprobante de Pago y Calificación", "💰 Payment Settlement and Review", $msgEs, $msgEn);
    }

    /**
     * CORREO 3D: ALERTA DE NUEVA RESEÑA RECIBIDA PARA EL LAB
     * Disparado automáticamente desde el controlador cuando un Creador califica un nodo
     */
    public static function notificarNuevaResenaAlLab($emailLab, $nombreLab, $nombreCreator, $tituloContexto, $rating)
    {
        $estrellasHtml = str_repeat('⭐', $rating);

        // Cuerpo en Español
        $msgEs = "
        <p>¡Hola, <strong>$nombreLab</strong>!</p>
        <p>Te contamos que el creador <strong>$nombreCreator</strong> te ha dejado una nueva valoración y unas palabras sobre el apoyo que le diste en: <strong>\"$tituloContexto\"</strong>.</p>
        <div style='background:#131722; padding:15px; border-radius:8px; text-align:center; margin:20px 0; border:1px solid rgba(255,255,255,0.02);'>
            <p style='margin:0; font-size:14px; color:#ffffff;'>Puntuación otorgada: <span style='letter-spacing:2px;'>$estrellasHtml</span></p>
        </div>
        <p>Date una vuelta por tu panel cuando tengas un momento para leer sus comentarios. ¡Estas opiniones ayudan un montón a que tu espacio siga destacando y creciendo en la comunidad!</p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hello <strong>$nombreLab</strong>,</p>
        <p>The creator <strong>$nombreCreator</strong> has left a new review regarding your support on: <strong>\"$tituloContexto\"</strong>.</p>
        <div style='background:#131722; padding:15px; border-radius:8px; text-align:center; margin:20px 0; border:1px solid rgba(255,255,255,0.02);'>
            <p style='margin:0; font-size:14px; color:#ffffff;'>Rating Given: <span style='letter-spacing:2px;'>$estrellasHtml</span></p>
        </div>
        <p>Log into your dashboard whenever you can to check out their feedback. These reviews are amazing to help your space stand out and keep growing within the community!</p>";

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
        // Detector dinámico de palabras según la naturaleza del recurso
        $conceptoEs = "un equipo"; $unidadEs = "Cantidad";
        $conceptoEn = "an asset";  $unidadEn = "Requested usage";

        if ($tipoActivo === 'workshop' || $tipoActivo === 'service') {
            $conceptoEs = "un taller / servicio"; $unidadEs = "Cupos reservados";
            $conceptoEn = "a workshop / service";  $unidadEn = "Reserved slots";
        } elseif ($tipoActivo === 'lab') {
            $conceptoEs = "un espacio de trabajo"; $unidadEs = "Horas reservadas";
            $conceptoEn = "a workspace";           $unidadEn = "Reserved hours";
        } elseif ($tipoActivo === 'machine') {
            $conceptoEs = "una máquina";           $unidadEs = "Horas reservadas";
            $conceptoEn = "a machine";             $unidadEn = "Reserved hours";
        }

        // Cuerpo en Español
        $msgEs = "
        <p>¡Hola, <strong>$nombreLab</strong>!</p>
        <p>Te avisamos que el creador <strong>$nombreCreator</strong> acaba de reservar <strong>$conceptoEs</strong> en tu sede usando sus FabCoins disponibles.</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Resumen de la reserva:</p>
            <p style='margin:0 0 4px 0;'><strong>Recurso:</strong> $nombreEquipo</p>
            <p style='margin:0 0 4px 0;'><strong>$unidadEs:</strong> $parametroTexto</p>
            <p style='margin:0 0 4px 0;'><strong>Fecha agendada:</strong> " . date('d/m/Y', strtotime($fecha)) . "</p>
            <p style='margin:0;'><strong>Total recibido:</strong> <span style='color:#2ecc71; font-weight:bold;'>" . number_format($totalFc, 0) . " FC</span></p>
        </div>
        <p>Date una vuelta por tu panel para revisar tu calendario de asistencia y tener todo listo para recibirlo el día de su visita.</p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hi <strong>$nombreLab</strong>!</p>
        <p>We wanted to let you know that the creator <strong>$nombreCreator</strong> has successfully booked <strong>$conceptoEn</strong> at your lab using their available FabCoins.</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Booking Summary:</p>
            <p style='margin:0 0 4px 0;'><strong>Resource:</strong> $nombreEquipo</p>
            <p style='margin:0 0 4px 0;'><strong>$unidadEn:</strong> $parametroTexto</p>
            <p style='margin:0 0 4px 0;'><strong>Scheduled date:</strong> " . date('d/m/Y', strtotime($fecha)) . "</p>
            <p style='margin:0;'><strong>Total transferred:</strong> <span style='color:#2ecc71; font-weight:bold;'>" . number_format($totalFc, 0) . " FC</span></p>
        </div>
        <p>Check your dashboard calendar to review the schedule and have everything ready to welcome them on their visit.</p>";

        $asuntoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "📅 Nueva inscripción a taller - FabCoins" : "📅 Nueva reserva de espacio/equipo - FabCoins";
        $asuntoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "📅 New Workshop Registration - FabCoins" : "📅 New Infrastructure Booking - FabCoins";

        return self::enviar($emailLab, $asuntoEs, $asuntoEn, "📅 Nueva reserva recibida", "📅 New Booking Received", $msgEs, $msgEn);
    }

    /**
     * CORREO 4B: PROPUESTA DE CAMBIO DE FECHA (RESCHEDULE)
     */
    public static function propuestaReprogramacionActivo($emailCreator, $nombreCreator, $nombreLab, $nombreEquipo, $nuevaFecha, $tipoActivo = 'machine')
    {
        $linkAcceso = route('login');

        $recursoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "del taller" : (($tipoActivo === 'lab') ? "del espacio" : "del equipo");
        $recursoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "of the workshop" : (($tipoActivo === 'lab') ? "of the space" : "of the equipment");

        // Cuerpo en Español
        $msgEs = "
        <p>¡Hola, <strong>$nombreCreator</strong>!</p>
        <p>El equipo de <strong>$nombreLab</strong> nos avisa que, por temas de mantenimiento o cruce de horarios en su sede, necesitan mover la fecha de tu reserva de tu turno $recursoEs <strong>$nombreEquipo</strong>.</p>
        <p>Te proponen el siguiente día alternativo para asistirte de la mejor manera:</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 8px; text-align:center; margin: 20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase;'>Nueva Fecha Sugerida:</span>
            <span style='font-size:18px; font-weight:800; color:#f1c40f;'>" . date('d M Y', strtotime($nuevaFecha)) . "</span>
        </div>
        <p>Por favor, ingresa a tu cuenta para <strong>Aceptar</strong> o <strong>Rechazar</strong> este cambio. Si prefieres no tomar esta nueva fecha, no te preocupes: tus FabCoins regresarán de inmediato a tu saldo para que reserves en otro momento.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#f39c12; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Responder Reprogramación</a>
        </p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hi <strong>$nombreCreator</strong>,</p>
        <p>The team at <strong>$nombreLab</strong> reached out to let us know that due to maintenance or scheduling updates at their space, they need to propose a new date for your booking $recursoEn <strong>$nombreEquipo</strong>.</p>
        <p>They kindly suggest the following alternative day to give you the best support:</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 8px; text-align:center; margin: 20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase;'>New Proposed Date:</span>
            <span style='font-size:18px; font-weight:800; color:#f1c40f;'>" . date('d M Y', strtotime($nuevaFecha)) . "</span>
        </div>
        <p>Please log into your dashboard to either <strong>Accept</strong> or <strong>Reject</strong> this change. If you choose to decline, don't worry: your FabCoins will be instantly returned to your balance so you can rebook whenever you want.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#f39c12; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Respond to Reschedule</a>
        </p>";

        return self::enviar($emailCreator, "⏳ Propuesta de cambio de fecha - FabCoins", "⏳ Date Change Proposal - FabCoins", "⏳ Reprogramación de Calendario", "⏳ Schedule Reschedule Proposal", $msgEs, $msgEn);
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
        $tituloEs = "Tu turno ya quedó agendado y separado en el laboratorio.";
        $tituloEn = "Your time slot is now secured in the laboratory schedule.";
        $unidadEs = "Horas aseguradas"; $unidadEn = "Secured hours";
        $cantTexto = "$horas horas";

        if ($tipoActivo === 'workshop' || $tipoActivo === 'service') {
            $tituloEs = "Tu lugar ya está asegurado en la lista de asistencia del taller.";
            $tituloEn = "Your spot is now confirmed in the workshop attendance logs.";
            $unidadEs = "Cupos reservados"; $unidadEn = "Secured slots";
            $cantTexto = "$horas cupos";
        }

        // Cuerpo en Español
        $msgEs = "
        <p>¡Hola, <strong>$nombreCreator</strong>!</p>
        <p>Tu solicitud fue aprobada con gusto por el equipo de <strong>$nombreLab</strong>. $tituloEs</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Tus datos confirmados:</p>
            <p style='margin:0 0 4px 0;'><strong>Recurso / Taller:</strong> $nombreEquipo</p>
            <p style='margin:0 0 4px 0;'><strong>Fecha de la visita:</strong> " . date('d M Y', strtotime($fecha)) . "</p>
            <p style='margin:0;'><strong>$unidadEs:</strong> $cantTexto</p>
        </div>
        <p style='margin-bottom:6px; font-weight:bold; color:#ffffff;'>⚠️ Recomendaciones amigables para tu visita:</p>
        <ul style='margin-top:0; padding-left:20px; color:#cbd5e0; font-size:13.5px;'>
            <li>Intenta llegar unos 15 minutos antes para que los encargados te den la bienvenida y te ubiquen con comodidad.</li>
            <li>Recuerda usar calzado cerrado y llevar el cabello recogido si vas a operar herramientas o maquinaria física en el taller.</li>
            <li>Si necesitas traer algún material extra o llevar archivos listos, coordínalo previamente con el nodo desde la plataforma.</li>
        </ul>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hi <strong>$nombreCreator</strong>!</p>
        <p>Your request has been warmly approved by the team at <strong>$nombreLab</strong>. $tituloEn</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Your confirmed details:</p>
            <p style='margin:0 0 4px 0;'><strong>Resource / Workshop:</strong> $nombreEquipo</p>
            <p style='margin:0 0 4px 0;'><strong>Scheduled Date:</strong> " . date('d M Y', strtotime($fecha)) . "</p>
            <p style='margin:0;'><strong>$unidadEn:</strong> $cantTexto</p>
        </div>
        <p style='margin-bottom:6px; font-weight:bold; color:#ffffff;'>⚠️ Friendly recommendations for your visit:</p>
        <ul style='margin-top:0; padding-left:20px; color:#cbd5e0; font-size:13.5px;'>
            <li>Please try to arrive 15 minutes early so the staff can welcome you and set you up comfortably.</li>
            <li>Remember to wear closed-toe shoes and tie back long hair if you are operating physical tools or machinery.</li>
            <li>If you need to bring extra raw materials or have setup files ready, double check with the node beforehand.</li>
        </ul>";

        $asuntoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "✅ Tu inscripción al taller está confirmada" : "✅ Tu reserva ha sido aprobada con éxito";
        $asuntoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "✅ Workshop Registration Confirmed" : "✅ Your Reservation has been Confirmed";

        return self::enviar($emailCreator, $asuntoEs, $asuntoEn, ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "✅ Registro Exitoso" : "✅ Reserva Confirmada", ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "✅ Registration Successful" : "✅ Booking Confirmed", $msgEs, $msgEn);
    }

    /**
     * CORREO 4E: SOLICITUD DE CRÉDITO ISA / ALQUILER DE HARDWARE
     */
    public static function solicitudCreditoActivo($emailLab, $nombreLab, $nombreCreator, $nombreEquipo, $horas, $montoCredito, $tipoActivo = 'machine')
    {
        $linkAcceso = route('login');

        $conceptoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "un taller / servicio" : (($tipoActivo === 'lab') ? "un espacio" : "una máquina");
        $conceptoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "a workshop / service" : (($tipoActivo === 'lab') ? "a space" : "a machine");
        $unidadEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "Cupos solicitados" : "Tiempo estimado";
        $unidadEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "Requested slots" : "Estimated time";

        // Cuerpo en Español
        $msgEs = "
        <p>¡Hola, <strong>$nombreLab</strong>!</p>
        <p>El creador <strong>$nombreCreator</strong> desea reservar <strong>$conceptoEs</strong> en tu sede mediante una opción de financiamiento, ya que no cuenta con el saldo total de FabCoins en este momento.</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Detalles de la propuesta:</p>
            <p style='margin:0 0 4px 0;'><strong>Recurso:</strong> $nombreEquipo</p>
            <p style='margin:0 0 4px 0;'><strong>$unidadEs:</strong> $horas</p>
            <p style='margin:0; '><strong>Financiamiento solicitado:</strong> <span style='color:#f1c40f; font-weight:bold;'>" . number_format($montoCredito, 0) . " FC</span></p>
        </div>
        <p>Por favor, ingresa a tu panel de control para revisar esta solicitud, conocer el perfil del creador y decidir si apruebas este apoyo colaborativo.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Revisar Solicitud en mi Panel</a>
        </p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hi <strong>$nombreLab</strong>,</p>
        <p>The creator <strong>$nombreCreator</strong> has requested to reserve <strong>$conceptoEn</strong> at your space through a financing option, as they do not have the full FabCoins balance at the moment.</p>
        <div style='background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 15px; border-radius: 6px; margin: 20px 0;'>
            <p style='margin:0 0 5px 0; font-size:12px; color:#7f8c8d; text-transform:uppercase;'>Request Details:</p>
            <p style='margin:0 0 4px 0;'><strong>Resource:</strong> $nombreEquipo</p>
            <p style='margin:0 0 4px 0;'><strong>$unidadEn:</strong> $horas</p>
            <p style='margin:0; '><strong>Requested Credit:</strong> <span style='color:#f1c40f; font-weight:bold;'>" . number_format($montoCredito, 0) . " FC</span></p>
        </div>
        <p>Please log into your dashboard to review this proposal, check out the creator's profile, and decide whether to approve this collaborative support.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Review Request in Dashboard</a>
        </p>";

        $asuntoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "📅 Solicitud de taller con financiamiento - FabCoins" : "📅 Nueva solicitud de alquiler con crédito - FabCoins";
        $asuntoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "📅 Workshop Request with Credit - FabCoins" : "📅 New Rental Request with Credit - FabCoins";

        return self::enviar($emailLab, $asuntoEs, $asuntoEn, "📅 Solicitud de Financiamiento Recibida", "📅 Financing Request Received", $msgEs, $msgEn);
    }

    /**
     * CORREO 4F: RESOLUCIÓN DE CRÉDITO ISA (AVISO PARA EL CREATOR)
     */
    public static function resolucionCreditoAlCreator($emailCreator, $nombreCreator, $nombreLab, $monto, $esAprobado, $tipoActivo = 'machine')
    {
        $asuntoEs = $esAprobado ? "✅ ¡Tu solicitud de apoyo fue aprobada! - FabCoins" : "❌ Tu solicitud de apoyo no pudo ser aprobada - FabCoins";
        $asuntoEn = $esAprobado ? "✅ Credit Support Approved! - FabCoins" : "❌ Credit Support Declined - FabCoins";
        
        $destinoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "tu lugar en el taller" : "tu turno de uso";
        $destinoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "your workshop spot" : "your reserved time slot";

        // Cuerpos dinámicos amigables
        $msgEs = $esAprobado 
            ? "<p>¡Hola, <strong>$nombreCreator</strong>!</p><p>Te traemos excelentes noticias: el equipo de <strong>$nombreLab</strong> ha <strong>APROBADO</strong> tu solicitud de financiamiento por un valor de <strong>" . number_format($monto, 0) . " FC</strong>. Gracias a esto, $destinoEs ya se encuentra completamente activo y asegurado para ti.</p>"
            : "<p>Hola, <strong>$nombreCreator</strong>:</p><p>Te informamos que en esta ocasión tu solicitud de financiamiento por <strong>" . number_format($monto, 0) . " FC</strong> no pudo ser aprobada por <strong>$nombreLab</strong>. Cualquier balance parcial que se hubiera separado temporalmente ya está de vuelta en tu saldo disponible.</p>";

        $msgEn = $esAprobado
            ? "<p>Hi <strong>$nombreCreator</strong>!</p><p>We have great news! The team at <strong>$nombreLab</strong> has <strong>APPROVED</strong> your financing request for <strong>" . number_format($monto, 0) . " FC</strong>. Therefore, $destinoEn is now fully active and secured for you.</p>"
            : "<p>Hello <strong>$nombreCreator</strong>,</p><p>We wanted to let you know that your financing request for <strong>" . number_format($monto, 0) . " FC</strong> could not be approved by <strong>$nombreLab</strong> this time. Any partial balance that was temporarily held is already back in your available account balance.</p>";

        return self::enviar($emailCreator, $asuntoEs, $asuntoEn, $esAprobado ? "✅ Financiamiento Autorizado" : "❌ Solicitud No Aprobada", $esAprobado ? "✅ Credit Authorized" : "❌ Request Not Approved", $msgEs, $msgEn);
    }

    /**
     * CORREO 4G: RESERVA RECHAZADA (AVISO PARA EL CREATOR)
     */
    public static function reservaRechazadaAlCreator($emailCreator, $nombreCreator, $nombreLab, $nombreEquipo, $tipoActivo = 'machine')
    {
        $conceptoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "tu inscripción para el taller" : "tu turno para la máquina";
        $conceptoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "your workshop registration" : "your machine booking request";

        // Cuerpo en Español
        $msgEs = "
        <p>Hola, <strong>$nombreCreator</strong>:</p>
        <p>Te escribimos para avisarte que, por temas imprevistos de agenda, el laboratorio <strong>$nombreLab</strong> no pudo confirmar $conceptoEs: <strong>$nombreEquipo</strong>.</p>
        <p>Tus FabCoins ya han sido liberados por completo y están listos en tu saldo para que puedas usarlos en cualquier otra actividad o reserva que desees dentro de la plataforma.</p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hello <strong>$nombreCreator</strong>,</p>
        <p>We are writing to inform you that due to unexpected scheduling constraints, the laboratory <strong>$nombreLab</strong> could not confirm $conceptoEn: <strong>$nombreEquipo</strong>.</p>
        <p>Your FabCoins have been fully released and are ready in your account balance so you can use them for any other activity or booking on the platform.</p>";

        $asuntoEs = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "❌ Inscripción no confirmada - FabCoins" : "❌ Reserva no confirmada - FabCoins";
        $asuntoEn = ($tipoActivo === 'workshop' || $tipoActivo === 'service') ? "❌ Registration Not Confirmed - FabCoins" : "❌ Booking Not Confirmed - FabCoins";

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
        <p>¡Hola, <strong>$nombreCreator</strong>!</p>
        <p>El laboratorio <strong>$nombreLab</strong> ha publicado una <strong>Misión Especial y Exclusiva</strong> pensada especialmente para ti.</p>
        <div style='background:rgba(241,196,15,0.04); border:1px dashed #f1c40f; padding:15px; border-radius:8px; text-align:center; margin:20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px;'>Monto que se descontará de tu saldo pendiente:</span>
            <span style='font-size:22px; font-weight:800; color:#f1c40f;'>" . number_format($recompensa, 0) . " FC</span>
        </div>
        <p><strong>🚨 Nota importante:</strong> Al tratarse de una misión de apoyo vinculada a tu saldo pendiente con este espacio, el valor de la recompensa se aplicará de forma directa para reducir o saldar por completo tu meta con este laboratorio.</p>
        <p>Es una oportunidad perfecta para avanzar en tus objetivos usando todo tu talento técnico. ¡Revisa los detalles y ponte en marcha!</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#f1c40f; color:#0b0c10; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Ver Detalles de la Misión</a>
        </p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hi <strong>$nombreCreator</strong>!</p>
        <p>The lab <strong>$nombreLab</strong> has published an <strong>Exclusive Special Mission</strong> tailored especially for you.</p>
        <div style='background:rgba(241,196,15,0.04); border:1px dashed #f1c40f; padding:15px; border-radius:8px; text-align:center; margin:20px 0;'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px;'>Amount to be reduced from your outstanding balance:</span>
            <span style='font-size:22px; font-weight:800; color:#f1c40f;'>" . number_format($recompensa, 0) . " FC</span>
        </div>
        <p><strong>🚨 Important Note:</strong> As a payback mission connected to your agreement with this lab, the reward value will be directly applied to reduce or fully settle your outstanding balance with them.</p>
        <p>It is the perfect opportunity to fulfill your commitment using your technical talents. Check the details and get started!</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#f1c40f; color:#0b0c10; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>View Mission Details</a>
        </p>";

        return self::enviar($emailCreator, "🎯 Misión Especial de Apoyo - FabCoins", "🎯 Exclusive Payback Mission - FabCoins", "🎯 Misión de Honor Asignada", "🎯 Special Mission Assigned", $msgEs, $msgEn);
    }

    /**
     * CORREO 4I: EL CREADOR ACEPTÓ LA MISIÓN DE HONOR (AVISO PARA EL LAB)
     * Se dispara desde el controlador del Creator cuando Henry acepta la invitación
     */
    public static function misionDirigidaAceptadaAlLab($emailLab, $nombreLab, $nombreCreator, $tituloMision, $recompensa)
    {
        $linkAcceso = route('login');

        // Cuerpo en Español
        $msgEs = "
        <p>¡Hola, <strong>$nombreLab</strong>!</p>
        <p>Te contamos que el creador <strong>$nombreCreator</strong> ha <strong>ACEPTADO</strong> formalmente la misión especial que le enviaste: <strong>\"$tituloMision\"</strong>.</p>
        <p>Este acuerdo para avanzar con el pago de su saldo pendiente por un valor de <strong>" . number_format($recompensa, 0) . " FC</strong> ya está oficialmente en marcha y el creador se encuentra listo para iniciar las tareas técnicas.</p>
        <p>Una vez que el proyecto esté listo en tu sede, recuerda ingresar a tu panel de control para calificar amigablemente su trabajo y confirmar el descuento de su saldo.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Monitorear Misiones Abiertas</a>
        </p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hi <strong>$nombreLab</strong>,</p>
        <p>We are writing to let you know that the creator <strong>$nombreCreator</strong> has formally <strong>ACCEPTED</strong> the special mission you directed to them: <strong>\"$tituloMision\"</strong>.</p>
        <p>This agreement to move forward with settling their outstanding balance for <strong>" . number_format($recompensa, 0) . " FC</strong> is now officially active, and the creator is clear to start working on the specified tasks.</p>
        <p>Once the work is completed at your space, remember to log into your dashboard to leave a friendly review and release the held tokens to reduce their balance.</p>
        <p style='text-align:center; margin:25px 0 10px 0;'>
            <a href='$linkAcceso' style='background-color:#3498db; color:#ffffff; padding:12px 30px; text-decoration:none; border-radius:6px; font-weight:bold; display:inline-block;'>Monitor Active Missions</a>
        </p>";

        return self::enviar($emailLab, "🤝 Misión de Honor Aceptada - FabCoins", "🤝 Honor Mission Accepted - FabCoins", "🤝 Compromiso de Apoyo Activo", "🤝 Payback Commitment Active", $msgEs, $msgEn);
    }

    /**
     * CORREO 4J: COMPROBANTE DE CRÉDITO AMORTIZADO (CULMINACIÓN)
     * Disparado desde MissionController@completeMission cuando la labor de retorno se aprueba
     */
    public static function misionDirigidaAmortizacionCulminada($emailCreator, $nombreCreator, $tituloMision, $montoAmortizado, $estrellas, $deudaRestante)
    {
        $estrellasHtml = str_repeat('⭐', $estrellas);

        $saldoDeudaTextoEs = ($deudaRestante > 0) 
            ? "Tu balance pendiente remanente con este laboratorio es de <strong>" . number_format($deudaRestante, 0) . " FC</strong>."
            : "<strong>🎉 ¡Muchísimas felicidades! Tu compromiso de apoyo con este laboratorio ha quedado completamente saldado al 100%.</strong>";

        $saldoDeudaTextoEn = ($deudaRestante > 0) 
            ? "Your remaining outstanding balance with this lab is now <strong>" . number_format($deudaRestante, 0) . " FC</strong>."
            : "<strong>🎉 Congratulations! Your financing agreement with this lab has been 100% settled.</strong>";

        // Cuerpo en Español
        $msgEs = "
        <p>¡Hola, <strong>$nombreCreator</strong>!</p>
        <p>El laboratorio revisó y aprobó con éxito tu entrega final para la misión: <em>\"$tituloMision\"</em>.</p>
        <div style='background:#131722; padding:20px; border-radius:8px; text-align:center; margin:20px 0; border:1px solid rgba(255,255,255,0.02);'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:4px;'>Monto descontado de tu balance:</span>
            <span style='color:#f1c40f; margin:0; font-size:26px; font-weight:800;'>-" . number_format($montoAmortizado, 0) . " FC</span>
            <p style='margin:10px 0 0 0; font-size:14px; color:#ffffff;'>Calificación del Laboratorio: <span style='letter-spacing:2px;'>$estrellasHtml</span></p>
        </div>
        <p>Los FabCoins se han aplicado directamente para cubrir tu meta. $saldoDeudaTextoEs</p>
        <p style='margin-top:15px;'>Tus nuevas habilidades validadas y tu puntuación de reputación pública ya brillan actualizadas en la red. ¡Gracias por tu enorme dedicación!</p>";

        // Cuerpo en Inglés
        $msgEn = "
        <p>Hi <strong>$nombreCreator</strong>,</p>
        <p>The laboratory has successfully audited and approved your final delivery for the mission: <em>\"$tituloMision\"</em>.</p>
        <div style='background:#131722; padding:20px; border-radius:8px; text-align:center; margin:20px 0; border:1px solid rgba(255,255,255,0.02);'>
            <span style='font-size:11px; color:#7f8c8d; display:block; text-transform:uppercase; letter-spacing:0.5px;'>Amount deducted from your balance:</span>
            <span style='color:#f1c40f; margin:0; font-size:26px; font-weight:800;'>-" . number_format($montoAmortizado, 0) . " FC</span>
            <p style='margin:10px 0 0 0; font-size:14px; color:#ffffff;'>Lab Evaluation: <span style='letter-spacing:2px;'>$estrellasHtml</span></p>
        </div>
        <p>The tokens have been successfully applied to cover your support agreement. $saldoDeudaTextoEn</p>
        <p style='margin-top:15px;'>Your newly validated skills and public reputation score have been successfully updated in the ranking logs. Thank you for your commitment!</p>";

        return self::enviar($emailCreator, "🪙 Reporte de balance actualizado - FabCoins", "🪙 Amortization Balance Receipt - FabCoins", "🪙 Balance Saldado", "🪙 Account Balance Settled", $msgEs, $msgEn);
    }

    /**
     * CORREO NUEVO: CONFIRMACIÓN DE CONTRASEÑA MODIFICADA CON ÉXITO
     */
    public static function notificarCambioPassword($emailDestino, $nombreUsuario)
    {
        $msgEs = "
        <p>¡Hola, <strong>$nombreUsuario</strong>!</p>
        <p>Te escribimos rápidamente para confirmarte que la contraseña de tu cuenta en la red de <strong>FabCoins</strong> ha sido modificada con éxito hoy.</p>
        <p>Si tú mismo realizaste este cambio, ¡está todo perfecto! No necesitas hacer nada más; tus nuevas credenciales ya están activas.</p>
        <hr style='border:0; border-top:1px solid rgba(255,255,255,0.05); margin:20px 0;'>
        <p style='font-size:12.5px; color:#e74c3c; font-weight:bold;'>⚠️ ¿No fuiste tú?</p>
        <p style='font-size:13px; color:#cbd5e0; margin-top:5px;'>Si tú no solicitaste este cambio de clave, por favor ponte en contacto de inmediato con nuestro equipo de soporte para ayudarte a congelar tu cuenta y proteger tus FabCoins acumulados.</p>";

        $msgEn = "
        <p>Hi <strong>$nombreUsuario</strong>!</p>
        <p>This is a quick confirmation to let you know that your <strong>FabCoins</strong> network account password was successfully updated today.</p>
        <p>If you made this change yourself, you are all set! No further action is required; your new credentials are live.</p>
        <hr style='border:0; border-top:1px solid rgba(255,255,255,0.05); margin:20px 0;'>
        <p style='font-size:12.5px; color:#e74c3c; font-weight:bold;'>⚠️ Wasn't this you?</p>
        <p style='font-size:13px; color:#cbd5e0; margin-top:5px;'>If you did not request this password reset, please reach out to our support team immediately so we can help you freeze your account and secure your earned FabCoins.</p>";

        return self::enviar($emailDestino, "🔒 Tu contraseña ha sido cambiada", "🔒 Your password has been changed", "🔒 Confirmación de Seguridad", "🔒 Security Confirmation", $msgEs, $msgEn);
    }
}
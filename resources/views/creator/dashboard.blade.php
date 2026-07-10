@extends('layouts.app')

@section('title', __('messages.creator_portal') . ' | FabCoins')

@section('content')
<div class="container">
    
    <header class="lab-header-v2">
       <button type="button" class="lab-profile-trigger" 
        onclick="window.open('{{ route('public.profile', auth()->user()->slug ?? auth()->id()) }}', '_blank')" 
        title="👁️ {{ __('messages.view_public_profile') }}">
            <div class="lab-avatar-wrapper status-active hub-border-blue">
                <img src="{{ $creator->avatar_url ?: 'https://ui-avatars.com/api/?name='.urlencode($creator->name).'&background=3498db&color=fff' }}" alt="{{ $creator->name }}">
            </div>
            <div class="lab-identity-meta">
                <h1>{{ $creator->name }}</h1>
                <div class="lab-reputation-stars hub-text-blue">
                    ⭐ {{ number_format($creator->reputation_score, 1) }} <span>{{ __('messages.reputation') }}</span>
                </div>
            </div>
        </button>
        
        <div class="lab-controls-node">
            <div class="notif-wrapper" style="position: relative;">
                <button type="button" class="notif-icon-btn" title="{{ __('messages.notifications') }}" onclick="interceptarCampanaYLimpiarContador(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <!-- 🔴 Agregamos el id 'badge-notif-dinamico' para poder borrarlo con JavaScript -->
                    @if(($unread_count ?? 0) > 0)
                        <span class="notif-badge-v2" id="badge-notif-dinamico">{{ $unread_count }}</span>
                    @endif
                </button>
                
                <div class="notif-dropdown">
                    @if(!isset($notificaciones) || $notificaciones->isEmpty())
                        <div class="notif-item notif-empty-state">{{ __('messages.no_notifications') }}</div>
                    @else
                        @foreach($notificaciones as $n)
                            <div class="notif-item {{ !$n->is_read ? 'unread' : '' }} notif-item-blue" 
                                 style="cursor: pointer; transition: background 0.2s;" 
                                 onclick="enrutarNotificacionInteligenteCreator('{{ strtolower($n->message) }}')"
                                 onmouseover="this.style.background='rgba(52, 152, 219, 0.05)'" 
                                 onmouseout="this.style.background='transparent'">
                                {{ $n->message }}
                                <div class="notif-timestamp">⏱️ {{ date('d M - H:i', strtotime($n->created_at)) }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            
            <form method="POST" action="{{ route('logout') }}" class="m-0"> 
                @csrf 
                <button type="submit" class="btn-logout-v2">{{ __('messages.btn_logout') }}</button> 
            </form>
        </div>
    </header>

    <div id="main-home-hub-view" class="home-hubs-wrapper">
        <div class="action-hubs-grid">

            <div class="hub-card card-misiones-neon" onclick="abrirHubPersistente('hub-misiones')">
                <div>
                    <div class="hub-image-container"><img src="{{ asset('images/hubs/icon_ofertar.webp') }}" alt=""></div>
                    <h2>{{ __('messages.hub_missions_btn') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.hub_missions_sub') }}</div>
                </div>
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            $circunferencia = 213.6;
                            // Matemática de Misiones: Todo el pipeline del Creador
                            $postPendientes = $misPostulaciones->whereIn('status', ['pending', 'invited'])->count();
                            
                            // 🎯 REPARACIÓN DE CUPOS: Filtramos basándonos en si el Lab ya calificó e indexó el trabajo de ESTE creador
                            $postAceptadas  = $misPostulaciones->where('status', 'accepted')->where('is_reviewed', 0)->count();
                            $postCompletas  = $misPostulaciones->where('is_reviewed', 1)->count();
                            
                            $totalMisiones = max(1, $postPendientes + $postAceptadas + $postCompletas);
                            
                            $pPen = ($postPendientes / $totalMisiones) * $circunferencia;
                            $pAce = ($postAceptadas / $totalMisiones) * $circunferencia;
                            $pCom = ($postCompletas / $totalMisiones) * $circunferencia;
                        @endphp
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#f1c40f" stroke-width="12" stroke-dasharray="{{ $pPen }} 214"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#3498db" stroke-width="12" stroke-dasharray="{{ $pAce }} 214" stroke-dashoffset="-{{ $pPen }}"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2ecc71" stroke-width="12" stroke-dasharray="{{ $pCom }} 214" stroke-dashoffset="-{{ $pPen + $pAce }}"></circle>
                    </svg>
                </div>
                <div>
                    <div class="main-hub-value text-pink-neon">{{ $postPendientes + $postAceptadas + $postCompletas }} {{ __('messages.lbl_missions_unit') }}</div>
                    <div class="bullet-metrics-compact">
                        <div class="metric-compact-row"><span class="color-dot-indicator dot-warning"></span> <strong>{{ $postPendientes }}</strong> {{ __('messages.status_waiting') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator dot-info"></span> <strong>{{ $postAceptadas }}</strong> {{ __('messages.lbl_working_bullet') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator dot-success"></span> <strong>{{ $postCompletas }}</strong> {{ __('messages.lbl_closed_bullet') }}</div>
                    </div>
                </div>
            </div>

            <div class="hub-card card-mercado-neon" onclick="abrirHubPersistente('hub-mercado')">
                <div>
                    <div class="hub-image-container"><img src="{{ asset('images/hubs/icon_activar.webp') }}" style="filter: hue-rotate(180deg);" alt=""></div>
                    <h2>{{ __('messages.hub_market_btn') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.map_explorer_desc') }}</div>
                </div>
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            // ⏳ EN ESPERA: Órdenes en estado pendiente o reprogramadas por el Lab
                            $resCustodia = $misReservas->whereIn('status', ['pending', 'rescheduled'])->count();

                            // 🔵 POR USAR: Órdenes aprobadas (completed) que el Creador aún no ha calificado
                            $resConfirmadas = $misReservas->where('status', 'completed')->where('is_reviewed', 0)->count();

                            // 🟢 COMPLETADOS: Órdenes aprobadas que ya cumplieron todo su ciclo y fueron calificadas
                            $resConsumidas = $misReservas->where('status', 'completed')->where('is_reviewed', 1)->count();
                            
                            $totalRes = max(1, $resCustodia + $resConfirmadas + $resConsumidas);
                            
                            $pCus = ($resCustodia / $totalRes) * $circunferencia;
                            $pCon = ($resConfirmadas / $totalRes) * $circunferencia;
                            $pUse = ($resConsumidas / $totalRes) * $circunferencia;
                        @endphp
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#f1c40f" stroke-width="12" stroke-dasharray="{{ $pCus }} 214"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#3498db" stroke-width="12" stroke-dasharray="{{ $pCon }} 214" stroke-dashoffset="-{{ $pCus }}"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2ecc71" stroke-width="12" stroke-dasharray="{{ $pUse }} 214" stroke-dashoffset="-{{ $pCus + $pCon }}"></circle>
                    </svg>
                </div>
                <div>
                    <div class="main-hub-value hub-text-blue">{{ $resCustodia + $resConfirmadas + $resConsumidas }} {{ __('Reservas') }}</div>
                    <div class="bullet-metrics-compact">
                        <div class="metric-compact-row"><span class="color-dot-indicator dot-warning"></span> <strong>{{ $resCustodia }}</strong> {{ __('messages.status_pending') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator dot-info"></span> <strong>{{ $resConfirmadas }}</strong> {{ __('Por Usar') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator dot-success"></span> <strong>{{ $resConsumidas }}</strong> {{ __('messages.status_completed') }}</div>
                    </div>
                </div>
            </div>

            <div class="hub-card card-billetera-neon" onclick="abrirHubPersistente('hub-billetera')">
                <div>
                    <div class="hub-image-container"><img src="{{ asset('images/hubs/icon_tokenizar.webp') }}" style="filter: hue-rotate(280deg);" alt=""></div>
                    <h2>{{ __('messages.hub_wallet_btn') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.hub_wallet_sub') }}</div>
                </div>
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            // ⚖️ SALDO LÍQUIDO: El dinero real que el creador tiene libre en su cuenta
                            $fcLíquidos  = max(0, $saldoTotal);

                            // ⏳ EN ESPERA BRUTO: Suma total de las órdenes pendientes
                            $fcCustodiaRaw = floatval(DB::table('orders')
                                ->where('creator_id', $creator->id)
                                ->whereIn('status', ['pending', 'rescheduled'])
                                ->sum('total_fc'));

                            // 🤝 CRÉDITOS NO APROBADOS: Buscamos promesas de financiamiento que aún están en "pending"
                            $creditosPendientes = floatval(DB::table('financing_agreements')
                                ->where('creator_id', $creator->id)
                                ->where('status', 'pending')
                                ->sum('amount_initial'));

                            // ⏱️ CUSTODIA NETO: Restamos el crédito pendiente para que solo cuente el pago parcial real (los 75 FC)
                            $fcCustodia = max(0, $fcCustodiaRaw - $creditosPendientes);

                            // 💸 CONSUMIDOS REALES: Gastos totales ejecutados en transacciones menos la custodia neta retenida
                            $totalGastosRaw = floatval(DB::table('transactions')
                                ->where('user_id', $creator->id)
                                ->where('type', 'expense')
                                ->sum('amount'));
                            
                            $fcGastados  = max(0, $totalGastosRaw - $fcCustodia);
                            
                            // Capital total para la estabilidad y proporciones del gráfico circular
                            $totalCapital = max(1, $fcLíquidos + $fcCustodia + $fcGastados);
                            
                            $pLiq = ($fcLíquidos / $totalCapital) * $circunferencia;
                            $pEsc = ($fcCustodia / $totalCapital) * $circunferencia;
                            $pGas = ($fcGastados / $totalCapital) * $circunferencia;
                            
                            $deudaRestante = $creditoActual ? $creditoActual->amount_remaining : 0;
                        @endphp
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2ecc71" stroke-width="12" stroke-dasharray="{{ $pLiq }} 214"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#3498db" stroke-width="12" stroke-dasharray="{{ $pEsc }} 214" stroke-dashoffset="-{{ $pLiq }}"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#e84393" stroke-width="12" stroke-dasharray="{{ $pGas }} 214" stroke-dashoffset="-{{ $pLiq + $pEsc }}"></circle>
                    </svg>
                </div>
                <div>
                    <div class="main-hub-value text-green-neon">{{ number_format($fcLíquidos + $fcCustodia + $fcGastados, 0, '.', ' ') }} FC</div>
                    <div class="bullet-metrics-compact">
                        <div class="metric-compact-row"><span class="color-dot-indicator dot-success"></span> <strong>{{ number_format($fcLíquidos, 0, '.', ' ') }}</strong> {{ __('messages.kpi_wallet') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator dot-info"></span> <strong>{{ number_format($fcCustodia, 0, '.', ' ') }}</strong> {{ __('messages.status_pending') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator dot-danger"></span> <strong>{{ number_format($fcGastados, 0, '.', ' ') }}</strong> {{ __('messages.lbl_consumed') }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div id="hub-misiones" class="hub-section">
        <div class="hub-active-bar-v2 hub-bar-pink">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('hub-misiones')">← {{ __('messages.btn_back') }}</button>
            <div class="hub-title-node hub-text-pink">
                <img src="{{ asset('images/hubs/icon_ofertar.webp') }}" alt="">
                {{ __('messages.hub_missions_btn') }}
            </div>
        </div>
        @include('creator.tabs.1_misiones')
    </div>

    <div id="hub-mercado" class="hub-section">
        <div class="hub-active-bar-v2 hub-bar-yellow">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('hub-mercado')">← {{ __('messages.btn_back') }}</button>
            <div class="hub-title-node hub-text-yellow">
                <img src="{{ asset('images/hubs/icon_activar.webp') }}" style="filter: hue-rotate(180deg);" alt="">
                {{ __('messages.hub_market_btn') }}
            </div>
        </div>
        @include('creator.tabs.2_mercado')
    </div>

    <div id="hub-billetera" class="hub-section">
        <div class="hub-active-bar-v2 hub-bar-blue">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('hub-billetera')">← {{ __('messages.btn_back') }}</button>
            <div class="hub-title-node hub-text-blue">
                <img src="{{ asset('images/hubs/icon_tokenizar.webp') }}" style="filter: hue-rotate(280deg);" alt="">
                {{ __('messages.hub_wallet_btn') }}
            </div>
        </div>
        @include('creator.tabs.3_billetera')
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
/**
 * 🎛️ MANEJADOR DE TRANSICIÓN DE ESPACIOS INMERSIVOS (VINCULACIÓN ESPEJO CON EL LAB)
 */
function abrirHubPersistente(hubId) {
    sessionStorage.setItem('active_creator_hub', hubId);
    
    document.querySelectorAll('.hub-section').forEach(sec => {
        sec.style.display = 'none';
        sec.classList.remove('active');
    });
    
    const homeHub = document.getElementById('main-home-hub-view');
    if (homeHub) homeHub.style.display = 'none';
    
    const target = document.getElementById(hubId);
    if (target) {
        target.style.display = 'block';
        setTimeout(() => target.classList.add('active'), 50);
    }
}

// 🔥 MOTOR UX: Abre el espacio inmersivo y viaja milimétricamente a la sección indicada
function abrirHubYEnfocarTarjeta(hubId, tarjetaId) {
    // 1. Despertamos el contenedor macro (Hub) usando tu lógica existente
    abrirHubPersistente(hubId);

    // 2. ⏱️ HOLGURA DE RENDER: Esperamos 150ms a que el navegador termine de pintar el display:block
    setTimeout(() => {
        const tarjetaTarget = document.getElementById(tarjetaId);
        if (tarjetaTarget) {
            // Viaje suave y centrado en la pantalla
            tarjetaTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });

            // 🎨 EFECTO PREMIUM: Le metemos un destello de enfoque dorado efímero
            tarjetaTarget.style.transition = 'box-shadow 0.3s ease, border-color 0.3s ease';
            tarjetaTarget.style.boxShadow = '0 0 25px rgba(241, 196, 15, 0.25)';
            tarjetaTarget.style.borderColor = '#f1c40f';

            // Apagamos el destello en 2 segundos para regresar a la normalidad del Dark Mode
            setTimeout(() => {
                tarjetaTarget.style.boxShadow = 'none';
                tarjetaTarget.style.borderColor = 'rgba(255,255,255,0.05)'; // Tu borde original
            }, 2000);
        }
    }, 150);
}

function regresarAlHubCentralPersistente(hubId) {
    sessionStorage.removeItem('active_creator_hub');
    
    const target = document.getElementById(hubId);
    if (target) {
        target.style.display = 'none';
        target.classList.remove('active');
    }
    
    const homeHub = document.getElementById('main-home-hub-view');
    if (homeHub) homeHub.style.display = 'grid';
}

/**
 * 🚀 FUNCIÓN DEDICADA UNIFICADA: Enrutador de accesos desde el perfil público.
 * Se ejecuta de forma aislada y segura una sola vez.
 */
function procesarEnrutamientoEcosistemaPublico() {
    try {
        // 🚀 LECTURA DE ALMACÉN GLOBAL (INMUNE A REGLAS DE PESTAÑAS)
        const tieneLab = localStorage.getItem('auto_filtrar_lab');
        const tieneMision = localStorage.getItem('auto_filtrar_mision');

        console.log(" Harrison-Log -> Leyendo Memoria Temporal:", tieneLab, " | Misión =", tieneMision);

        if (tieneLab) {
            console.log(" Harrison-Log -> ¡Memoria detectada! Abriendo MERCADO...");
            localStorage.removeItem('auto_filtrar_lab'); // 🧼 Limpieza para evitar bucles
            abrirHubPersistente('hub-mercado');

            // 🎯 SIMULACIÓN DEL MAPA: Ejecuta el filtro contable del laboratorio
            setTimeout(() => {
                if (typeof filtrarCatalogoMercadoVivo === 'function') {
                    // Si tu función global del mapa se llama así, la ejecuta directamente
                    filtrarCatalogoMercadoVivo(tieneLab);
                } else {
                    // Alternativa defensiva: busca el selector/input de la sub-view y fuerza su cambio
                    const selectorCatalogo = document.getElementById('filter-cat') || document.getElementById('lab-selector');
                    if (selectorCatalogo) {
                        selectorCatalogo.value = tieneLab;
                        selectorCatalogo.dispatchEvent(new Event('change'));
                    }
                }
            }, 150); // ⏱️ Holgura de 150ms para que el HTML interno de la pestaña termine de despertar
        } else if (tieneMision) {
            console.log(" Harrison-Log -> ¡Memoria detectada! Abriendo MISIONES...");
            localStorage.removeItem('auto_filtrar_mision'); // 🧼 Limpieza inmediata
            abrirHubPersistente('hub-misiones');

            // 🎯 DISPARADOR: Filtra la bolsa de misiones de forma asíncrona
            setTimeout(() => {
                // Buscamos el selector o buscador que uses en tu pestaña de misiones
                const selectorMisiones = document.getElementById('filter-mission-lab') || document.getElementById('mission-lab-selector');
                if (selectorMisiones) {
                    selectorMisiones.value = tieneMision;
                    selectorMisiones.dispatchEvent(new Event('change'));
                }
            }, 150); // ⏱️ Holgura milimétrica para el renderizado del DOM
        } else {
            const hubGuardado = sessionStorage.getItem('active_creator_hub');
            if (hubGuardado) abrirHubPersistente(hubGuardado);
        }
    } catch (error) {
        console.warn("Aviso en enrutador unificado:", error);
    }
}

document.addEventListener("DOMContentLoaded", function() {
    
    // Variable principal para la lectura de alertas y SweetAlerts
    const urlParams = new URLSearchParams(window.location.search);

    // ⏱️ IMITACIÓN DE CAMPANITA: Espera 700ms a que la suite esté 100% dibujada para abrir el espacio inmersivo
    setTimeout(procesarEnrutamientoEcosistemaPublico, 700);

    // Procesamiento nativo de alertas informativas y errores de la plataforma
    const msg = urlParams.get('msg') || "{{ session('msg') }}";
    const error = "{{ session('error') ?? '' }}";
    
    const swalConfig = {
        background: '#1a252f',
        color: '#fff',
        confirmButtonColor: '#3498db',
        timer: 6000,
        timerProgressBar: true,
        customClass: { popup: 'premium-popup' }
    };

    const alertas = {
        'mission_applied_ok': { icon: 'success', title: '🚀 ' + "{{ __('messages.swal_success') ?? '¡Éxito!' }}", text: "{{ __('messages.swal_mission_applied_ok') }}" },
        'applied_ok': { icon: 'success', title: '🎯 ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_applied_ok_desc') }}" },
        'p2p_ok': { icon: 'success', title: '💰 ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_p2p_ok_desc') }}" },
        'profile_updated': { icon: 'success', title: '👤 ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_profile_completed_desc') }}" },
        'pass_ok': { icon: 'success', title: '🔒 ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_pass_ok_desc') }}" },
        'rental_pending': { icon: 'warning', title: '⏳ ' + "{{ __('messages.swal_rental_pending_title') }}", text: "{{ __('messages.swal_rental_pending_desc') }}" },
        'credit_pending': { icon: 'warning', title: '⏳ ' + "{{ __('messages.swal_credit_pending_title') }}", text: "{{ __('messages.swal_credit_pending_desc') }}" },
        'credit_accepted': { icon: 'success', title: '🎓 ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_credit_accepted_desc') }}" },
        'date_accepted': { icon: 'success', title: '✅ ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_date_accepted_desc') }}" },
        'date_rejected': { icon: 'error', title: '❌ ' + "{{ __('messages.swal_refunded_title') }}", text: "{{ __('messages.swal_date_rejected_desc') }}" },
        'review_ok': { icon: 'success', title: '⭐ ' + "{{ __('messages.swal_reviewed_title') }}", text: "{{ __('messages.swal_review_ok_desc') }}" },
        'reservation_approved_ok': { icon: 'success', title: '✅ ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.msg_reservation_approved_ok') }}" },
        'reservation_rejected_ok': { icon: 'error', title: '❌ ' + "{{ __('messages.swal_attention') }}", text: "{{ __('messages.msg_reservation_rejected_ok') }}" },
        'reservation_rescheduled_ok': { icon: 'info', title: '🔄 ' + "{{ __('messages.swal_attention') }}", text: "{{ __('messages.msg_reservation_rescheduled_ok') }}" },
    };

    if (msg && alertas[msg]) {
        Swal.fire({ ...swalConfig, icon: alertas[msg].icon, title: alertas[msg].title, text: alertas[msg].text });
    }
    if (error) {
        Swal.fire({ ...swalConfig, icon: 'error', title: '⚠️ ' + "{{ __('messages.swal_attention') }}", text: error, timer: null });
    }
});

function confirmarAccion(event, mensaje, icono = 'warning', colorBoton = '#3498db') {
    event.preventDefault(); 
    const boton = event.target.closest('button');
    const form = boton.closest('form');

    Swal.fire({
        title: "{{ __('messages.swal_are_you_sure') }}",
        text: mensaje,
        icon: icono,
        background: '#1a252f',
        color: '#fff',
        showCancelButton: true,
        confirmButtonColor: colorBoton,
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: "{{ __('messages.swal_confirm') }}",
        cancelButtonText: "{{ __('messages.swal_cancel') }}"
    }).then((result) => {
        if (result.isConfirmed) {
            form.submit();
        }
    });
}

function enrutarNotificacionInteligenteCreator(mensaje) {
    // 🎯 REGLA DE REPUTACIÓN: Si la alerta es sobre su calificación, lo redirigimos a su perfil público
    if (mensaje.includes('calificación') || mensaje.includes('estrellas') || mensaje.includes('reseña') || mensaje.includes('rating')) {
        window.location.href = "{{ route('public.profile', auth()->user()->slug ?? auth()->id()) }}";
        return;
    }

    // 🎯 CASO A: Propuestas de reprogramación o avisos de reservas aprobadas -> Va a Mis Alquileres
    if (mensaje.includes('propuesto') || mensaje.includes('fecha') || mensaje.includes('reprogramar') || mensaje.includes('reserva ha sido aprobada')) {
        abrirHubYEnfocarTarjeta('hub-mercado', 'tarjeta-mis-reservas');
    } 
    // 🎯 CASO B: Resoluciones de Créditos Educativos / ISA (Aprobados o Cancelados) -> Va a Contrato ISA
    else if (mensaje.includes('crédito') || mensaje.includes('financiamiento')) {
        abrirHubYEnfocarTarjeta('hub-billetera', 'tarjeta-mis-financiamientos');
    } 
    // 🎯 CASO C: Asignaciones de misiones, invitaciones o cierres de labor -> Va a Mis Postulaciones
    else if (mensaje.includes('misión') || mensaje.includes('postulación') || mensaje.includes('trabajo') || mensaje.includes('asignada') || mensaje.includes('completada')) {
        abrirHubYEnfocarTarjeta('hub-misiones', 'tarjeta-mis-misiones');
    } 
    // 🎯 CASO D: Alertas genéricas financieras residuales (Reembolsos, P2P) -> Va al Hub Billetera normal
    else if (mensaje.includes('reserva') || mensaje.includes('billetera') || mensaje.includes('reembolso') || mensaje.includes('saldo')) {
        abrirHubPersistente('hub-billetera');
    } 
    else {
        abrirHubPersistente('hub-mercado');
    }
    
    const dropdown = document.querySelector('.notif-dropdown');
    if(dropdown) dropdown.style.display = 'none';
}

// 🔍 AUTO-COMPLETAR NOMBRE PARA P2P EN BILLETERA
const inputEmailP2P = document.getElementById('p2p-email-input');
const feedbackP2P = document.getElementById('p2p-name-feedback');
let timeoutId = null;

if (inputEmailP2P && feedbackP2P) {
    inputEmailP2P.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const email = this.value.trim();
        
        if (email.length < 5 || !email.includes('@')) {
            feedbackP2P.textContent = '';
            return;
        }

        feedbackP2P.innerHTML = '{{ __("messages.lbl_searching") }}'; // Antes decía "Buscando..."
        feedbackP2P.style.color = '#bdc3c7';

        timeoutId = setTimeout(() => {
            fetch(`{{ route('creator.check_email_p2p') }}?email=${encodeURIComponent(email)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.name === 'NOT_FOUND') {
                        feedbackP2P.innerHTML = '❌ {{ __("messages.lbl_receiver_not_found") }}';
                        feedbackP2P.style.color = '#e74c3c';
                    } else {
                        feedbackP2P.innerHTML = '✅ {{ __("messages.lbl_send_to") }} ' + data.name; // Antes decía "Enviar a:"
                        feedbackP2P.style.color = '#2ecc71';
                    }
                }).catch(() => feedbackP2P.textContent = '');
        }, 500); // Espera 500ms después de dejar de teclear para no saturar el servidor
    });
}

function interceptarCampanaYLimpiarContador(btn) {
    // 1. MANTENER TU LOGICA ORIGINAL: Abre y cierra el menú exactamente igual que antes
    const dd = btn.nextElementSibling;
    dd.style.display = dd.style.display === 'flex' ? 'none' : 'flex';
    
    // 2. DISPARADOR ASÍNCRONO: Si el menú se está abriendo, limpiamos los datos
    if (dd.style.display === 'flex') {
        const badge = document.getElementById('badge-notif-dinamico');
        if (badge) {
            badge.remove(); // 🧼 Desaparece el número rojo de la pantalla al instante
            
            // 🎯 REPARACIÓN DE RUTA: Apuntamos al endpoint correcto del Creator
            fetch('{{ route("creator.read_notifs") }}')
                .catch(error => console.error('Aviso de lectura no procesado:', error));
        }
    }
}


</script>
@endpush
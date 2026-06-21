@extends('layouts.app')

@section('title', __('messages.creator_portal') . ' | FabCoins')

@section('content')
<div class="container">
    
    <header class="lab-header-v2">
       <button type="button" class="lab-profile-trigger" 
        onclick="abrirHubPersistente('hub-billetera'); setTimeout(() => { document.getElementById('seccion-perfil-habilidades').scrollIntoView({ behavior: 'smooth' }); }, 250);" 
        title="{{ __('messages.edit_profile') }}">
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
                <button type="button" class="notif-icon-btn" title="{{ __('messages.notifications') }}" onclick="const dd = this.nextElementSibling; dd.style.display = dd.style.display === 'flex' ? 'none' : 'flex';">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    @if(($unreadCount ?? 0) > 0)
                        <span class="notif-badge-v2 hub-bg-blue">{{ $unreadCount }}</span>
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
                            $postAceptadas  = $misPostulaciones->where('status', 'accepted')->where('mission_status', 'open')->count();
                            $postCompletas  = $misPostulaciones->where('mission_status', 'completed')->count();
                            
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
                            // Matemática de Reservas: Custodia, Confirmadas y Consumidas
                            $resCustodia = $misReservas->where('status', 'pending')->count();
                            $resConfirmadas = $misReservas->where('status', 'approved')->count();
                            $resConsumidas = $misReservas->where('status', 'completed')->count();
                            
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
                            // Matemática de Billetera: Distribución de la riqueza histórica
                            $fcLíquidos  = max(0, $saldoTotal);
                            $fcCustodia  = DB::table('transactions')->where('user_id', $creator->id)->where('type', 'escrow')->sum('amount');
                            $fcGastados  = DB::table('transactions')->where('user_id', $creator->id)->where('type', 'expense')->sum('amount');
                            
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

document.addEventListener("DOMContentLoaded", function() {
    // Restauración de estado dinámico
    const hubGuardado = sessionStorage.getItem('active_creator_hub');
    if (hubGuardado) {
        abrirHubPersistente(hubGuardado);
    }

    const urlParams = new URLSearchParams(window.location.search);
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

    // 🌐 SCRIPTS CON SOPORTE MULTIDIOMA INTEGRADO
    const alertas = {
        'mission_applied_ok': { icon: 'success', title: '🚀 ' + "{{ __('messages.swal_success') ?? '¡Éxito!' }}", text: "{{ __('messages.swal_mission_applied_ok') }}" },
        'applied_ok': { icon: 'success', title: '🎯 ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_applied_ok_desc') }}" },
        'credit_accepted': { icon: 'success', title: '🎓 ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_credit_accepted_desc') }}" },
        'p2p_ok': { icon: 'success', title: '💰 ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_p2p_ok_desc') }}" },
        'profile_updated': { icon: 'success', title: '👤 ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_profile_completed_desc') }}" },
        'pass_ok': { icon: 'success', title: '🔒 ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_pass_ok_desc') }}" },
        'rental_pending': { icon: 'warning', title: '⏳ ' + "{{ __('messages.status_enlisted') }}", text: "{{ __('messages.swal_rental_pending_desc') }}" },
        'date_accepted': { icon: 'success', title: '✅ ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_date_accepted_desc') }}" },
        'date_rejected': { icon: 'error', title: '❌ ' + "{{ __('messages.swal_refunded_title') }}", text: "{{ __('messages.swal_date_rejected_desc') }}" },
        'review_ok': { icon: 'success', title: '⭐ ' + "{{ __('messages.swal_reviewed_title') }}", text: "{{ __('messages.swal_review_ok_desc') }}" },
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
    // 1. Excepción específica: Si la reserva YA fue aprobada, debe ir a Mercado (Monitor de alquileres)
    if (mensaje.includes('reserva ha sido aprobada')) {
        abrirHubPersistente('hub-mercado');
    } 
    // 2. Todo lo demás que sea financiero, a Billetera
    else if (mensaje.includes('crédito') || mensaje.includes('financiamiento') || mensaje.includes('reserva') || mensaje.includes('saldo')) {
        abrirHubPersistente('hub-billetera');
    } 
    // 3. Misiones y postulaciones, a Misiones
    else if (mensaje.includes('misión') || mensaje.includes('postulación') || mensaje.includes('trabajo')) {
        abrirHubPersistente('hub-misiones');
    } 
    // 4. Por defecto
    else {
        abrirHubPersistente('hub-mercado');
    }
    
    // Cerramos el dropdown visualmente tras hacer clic
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
</script>
@endpush
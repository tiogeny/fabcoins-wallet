@extends('layouts.app')

@section('title', __('messages.lab_portal'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lab.css') }}?v=4.0">
@endpush

@section('content')
<div class="container">
    
    <!-- HEADER INDUSTRIAL DE ALTA GAMA -->
    <header class="lab-header-v2">
       <button type="button" class="lab-profile-trigger" 
        onclick="abrirHubPersistente('hub-activar'); setTimeout(() => { document.getElementById('seccion-perfil-mapa').scrollIntoView({ behavior: 'smooth' }); }, 250);" 
        title="{{ __('messages.edit_profile') }}">
            <div class="lab-avatar-wrapper {{ ($isFrozen ?? false) ? 'status-frozen' : 'status-active' }}">
                <img src="{{ $lab->avatar_url ?: 'https://ui-avatars.com/api/?name='.urlencode($lab->name).'&background=1abc9c&color=fff' }}" alt="{{ $lab->name }}">
            </div>
            <div class="lab-identity-meta">
                <h1>{{ $lab->name }}</h1>
                <div class="lab-reputation-stars">
                    ⭐ {{ number_format($lab->reputation_score, 1) }} <span>{{ __('messages.reputation') }}</span>
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
                    @if(($unread_count ?? 0) > 0)
                        <span class="notif-badge-v2">{{ $unread_count }}</span>
                    @endif
                </button>
                
                <div class="notif-dropdown">
                    @if(!isset($notificaciones) || $notificaciones->isEmpty())
                        <div class="notif-item" style="text-align: center; color: #7f8c8d; border-left: none;">{{ __('messages.no_notifications') }}</div>
                    @else
                        @foreach($notificaciones as $n)
                            <div class="notif-item {{ !$n->is_read ? 'unread' : '' }} notif-item-azul" 
                                 style="cursor: pointer; transition: background 0.2s;" 
                                 onclick="enrutarNotificacionInteligenteLab('{{ strtolower($n->message) }}')"
                                 onmouseover="this.style.background='rgba(52, 152, 219, 0.05)'" 
                                 onmouseout="this.style.background='transparent'">
                                {{ $n->message }}
                                <div class="notif-timestamp">⏱️ {{ date('d M - H:i', strtotime($n->created_at)) }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>
            </div>
            
            <form method="POST" action="{{ route('logout') }}" style="margin:0;"> 
                @csrf 
                <button type="submit" class="btn-logout-v2">{{ __('messages.btn_logout') }}</button> 
            </form>
        </div>
    </header>

    <!-- 🏠 VISTA 1: CENTRAL HUBS -->
    <div id="main-home-hub-view" class="home-hubs-wrapper">
        <div class="action-hubs-grid">
            <div class="hub-card card-activar-neon" onclick="abrirHubPersistente('hub-activar')">
                <div>
                    <div class="hub-image-container"><img src="{{ asset('images/hubs/icon_activar.webp') }}" alt=""></div>
                    <h2>{{ __('messages.hub_activate_title') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.hub_activate_desc') }}</div>
                </div>
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            $circunferencia = 213.6;
                            $perimetroM = ($totalMaquinasCount / max(1, $totalActivosCount)) * $circunferencia;
                            $perimetroS = ($totalServiciosCount / max(1, $totalActivosCount)) * $circunferencia;
                            $perimetroL = ($totalLabsConectados / max(1, $totalActivosCount)) * $circunferencia;
                        @endphp
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#1abc9c" stroke-width="12" stroke-dasharray="{{ $perimetroM }} 214"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#3498db" stroke-width="12" stroke-dasharray="{{ $perimetroS }} 214" stroke-dashoffset="-{{ $perimetroM }}"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#9b59b6" stroke-width="12" stroke-dasharray="{{ $perimetroL }} 214" stroke-dashoffset="-{{ $perimetroM + $perimetroS }}"></circle>
                    </svg>
                </div>
                <div>
                    <div class="main-hub-value">{{ $totalActivosCount }} {{ __('messages.lbl_assets_unit') }}</div>
                    <div class="bullet-metrics-compact">
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#1abc9c;"></span> <strong>{{ $totalMaquinasCount }}</strong> {{ __('messages.lbl_machines_bullet') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#3498db;"></span> <strong>{{ $totalServiciosCount }}</strong> {{ __('messages.lbl_services_bullet') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#9b59b6;"></span> <strong>{{ $totalLabsConectados }}</strong> {{ __('messages.lbl_labs_bullet') }}</div>
                    </div>
                </div>
            </div>

            <div class="hub-card card-tokenizar-neon" onclick="abrirHubPersistente('hub-tokenizar')">
                <div>
                    <div class="hub-image-container"><img src="{{ asset('images/hubs/icon_tokenizar.webp') }}" alt=""></div>
                    <h2>{{ __('messages.hub_tokenise_title') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.hub_tokenise_desc') }}</div>
                </div>
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            $labId = auth()->id();
                            $circunferencia = 213.6;

                            // 1. Emisión Base (Masa Monetaria Inalterable)
                            $totalMinted = DB::table('transactions')->where('user_id', $labId)->where('type', 'mint')->sum('amount');
                            $totalMinted = max(5000, $totalMinted); // Salvaguarda base

                            // 2. Fondos en Custodia (Misiones Abiertas o En Ejecución actualmente)
                            $congeladosReales = DB::table('missions')
                                ->where('lab_id', $labId)
                                ->whereIn('status', ['open', 'assigned'])
                                ->sum(DB::raw('reward_fc * spots_total'));

                            // 3. En Circulación (Inyectado a la comunidad por misiones terminadas)
                            $enCirculacion = DB::table('missions')
                                ->where('lab_id', $labId)
                                ->where('status', 'completed')
                                ->sum(DB::raw('reward_fc * spots_filled'));

                            // 4. Tesorería Disponible (Lo que estrictamente le queda al Lab en caja)
                            $realLiquid = max(0, $totalMinted - $congeladosReales - $enCirculacion);

                            // 5. Contador Independiente: Servicios Liquidados / Deudas Quemadas
                            $realConsumed = DB::table('transactions')->where('user_id', $labId)->where('type', 'consumed')->sum('amount');

                            // Perímetros del gráfico circular perfectos sobre base 5000
                            $pLiq = ($realLiquid / $totalMinted) * $circunferencia;
                            $pFrz = ($congeladosReales / $totalMinted) * $circunferencia;
                            $pCir = ($enCirculacion / $totalMinted) * $circunferencia;
                        @endphp
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#3498db" stroke-width="12" stroke-dasharray="{{ $pLiq }} 214"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#f1c40f" stroke-width="12" stroke-dasharray="{{ $pFrz }} 214" stroke-dashoffset="-{{ $pLiq }}"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2ecc71" stroke-width="12" stroke-dasharray="{{ $pCir }} 214" stroke-dashoffset="-{{ $pLiq + $pFrz }}"></circle>
                    </svg>
                </div>
                <div style="display: flex; flex-direction: column; justify-content: space-between;">
                    
                    <div style="display: flex; align-items: baseline; justify-content: center; gap: 8px; margin-bottom: 15px;">
                        <div class="main-hub-value text-success-neon" style="margin: 0;" title="Emisión Histórica Respaldada">{{ number_format($totalMinted, 0, '.', ' ') }} FC</div>
                        <div style="font-size: 14px; color: #e67e22; font-weight: 700; font-family: 'Rajdhani', sans-serif;">
                            / {{ number_format($realConsumed, 0, '.', ' ') }} <span style="font-size: 9px; font-family: 'Inter', sans-serif; text-transform: uppercase; color: #bdc3c7;">{{ __('messages.badge_consumed') }}</span>
                        </div>
                    </div>

                    <div class="bullet-metrics-compact">
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#3498db;"></span> <strong>{{ number_format($realLiquid, 0, '.', ' ') }}</strong> {{ __('messages.lbl_reserve') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#f1c40f;"></span> <strong>{{ number_format($congeladosReales, 0, '.', ' ') }}</strong> {{ __('messages.lbl_frozen') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#2ecc71;"></span> <strong>{{ number_format($enCirculacion, 0, '.', ' ') }}</strong> {{ __('messages.lbl_circulating') }}</div>
                    </div>
                </div>
            </div>

            <div class="hub-card card-publicar-neon" onclick="abrirHubPersistente('hub-publicar')">
                <div>
                    <div class="hub-image-container"><img src="{{ asset('images/hubs/icon_ofertar.webp') }}" alt=""></div>
                    <h2>{{ __('messages.hub_publish_title') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.hub_publish_desc') }}</div>
                </div>
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            $perimetroComp = ($statsMisiones['completadas'] / max(1, $totalMisionesCount)) * $circunferencia;
                            $perimetroExec = ($statsMisiones['en_ejecucion'] / max(1, $totalMisionesCount)) * $circunferencia;
                            $perimetroOpen = (($statsMisiones['abiertas'] + $statsMisiones['por_aceptar']) / max(1, $totalMisionesCount)) * $circunferencia;
                        @endphp
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2ecc71" stroke-width="12" stroke-dasharray="{{ $perimetroComp }} 214"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#e84393" stroke-width="12" stroke-dasharray="{{ $perimetroExec }} 214" stroke-dashoffset="-{{ $perimetroComp }}"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#3498db" stroke-width="12" stroke-dasharray="{{ $perimetroOpen }} 214" stroke-dashoffset="-{{ $perimetroComp + $perimetroExec }}"></circle>
                    </svg>
                </div>
                <div>
                    <div class="main-hub-value">{{ $totalMisionesCount }} {{ __('messages.lbl_missions_unit') }}</div>
                    <div class="bullet-metrics-compact">
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#2ecc71;"></span> <strong>{{ $statsMisiones['completadas'] }}</strong> {{ __('messages.lbl_closed_bullet') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#e84393;"></span> <strong>{{ $statsMisiones['en_ejecucion'] }}</strong> {{ __('messages.lbl_working_bullet') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#3498db;"></span> <strong>{{ $statsMisiones['abiertas'] + $statsMisiones['por_aceptar'] }}</strong> {{ __('messages.lbl_open_bullet') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 🖥️ VISTA 2: ESPACIOS DE TRABAJO INMERSIVOS -->
    
    <!-- ACTIVAR (ESMERALDA SOLIDO) -->
    <div id="hub-activar" class="hub-section">
        <div class="hub-active-bar-v2 hub-bar-verde">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('hub-activar')">← {{ __('messages.btn_back') }}</button>
            <div class="hub-title-node hub-text-verde">
                <img src="{{ asset('images/hubs/icon_activar.webp') }}" alt="">
                {{ __('messages.hub_activate_title') }}
            </div>
        </div>
        @include('lab.tabs.1_activar')
    </div>

    <div id="hub-tokenizar" class="hub-section">
        <div class="hub-active-bar-v2 hub-bar-amarillo">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('hub-tokenizar')">← {{ __('messages.btn_back') }}</button>
            <div class="hub-title-node hub-text-amarillo">
                <img src="{{ asset('images/hubs/icon_tokenizar.webp') }}" alt="">
                {{ __('messages.hub_tokenise_title') }}
            </div>
        </div>
        @include('lab.tabs.2_tokenizar')
    </div>

    <div id="hub-publicar" class="hub-section">
        <div class="hub-active-bar-v2 hub-bar-rosado">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('hub-publicar')">← {{ __('messages.btn_back') }}</button>
            <div class="hub-title-node hub-text-rosado">
                <img src="{{ asset('images/hubs/icon_ofertar.webp') }}" alt="">
                {{ __('messages.hub_publish_title') }}
            </div>
        </div>
        @include('lab.tabs.3_publicar')
    </div>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/lab-hubs.js') }}?v=1.4"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    // 🔥 SOLUCIÓN ARREGLO DE HILOS: Limpieza total de clases ocultas para evitar congelamiento
    function abrirHubPersistente(hubId) {
        sessionStorage.setItem('active_lab_hub', hubId);
        
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
        sessionStorage.removeItem('active_lab_hub');
        
        const target = document.getElementById(hubId);
        if (target) {
            target.style.display = 'none';
            target.classList.remove('active');
        }
        
        const homeHub = document.getElementById('main-home-hub-view');
        if (homeHub) homeHub.style.display = 'grid';
    }

    document.addEventListener("DOMContentLoaded", function() {
        // 🧠 DETECTOR DE COLISIÓN FLOTANTE (SMART TOOLTIPS V1.0)
        document.body.addEventListener('mouseenter', function(e) {
            // Buscamos si el cursor entró a un icono de tooltip
            const tooltip = e.target.closest('.fx-tooltip');
            if (!tooltip) return;

            const rect = tooltip.getBoundingClientRect();
            
            // Si hay menos de 160px de espacio libre entre el icono y el techo del navegador
            if (rect.top < 160) {
                tooltip.classList.add('to-bottom'); // Lo obliga a abrirse hacia abajo
            } else {
                tooltip.classList.remove('to-bottom'); // Lo deja abrirse hacia arriba por defecto
            }
        }, true); // Usamos capture true para escuchar eventos dinámicos en caliente
        
        const hubGuardado = sessionStorage.getItem('active_lab_hub');
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

       const alertas = {
            'asset_enlisted_ok': { icon: 'success', title: '🏢 ' + "{{ __('messages.swal_inv_updated') }}", text: "{{ __('messages.asset_enlisted_ok') }}" },
            'asset_deleted_ok': { icon: 'success', title: '🗑️ ' + "{{ __('messages.swal_success') }}", text: "{{ __('messages.asset_deleted_ok') }}" },
            'mint_ok': { icon: 'success', title: "{{ __('messages.swal_mint_title') }}", text: "{{ __('messages.swal_mint_text') }}" },
            'retired_ok': { icon: 'warning', title: "{{ __('messages.swal_retired_title') }}", text: "{{ __('messages.swal_retired_text') }}" },
            'price_ok': { icon: 'success', title: "{{ __('messages.swal_price_title') }}", text: "{{ __('messages.swal_price_desc') }}" },
            'mission_published_ok': { icon: 'success', title: "{{ __('messages.swal_mission_published') }}", text: "{{ __('messages.swal_mission_published_desc') }}" },
            'mission_assigned_ok': { icon: 'success', title: "{{ __('messages.swal_mission_assigned') }}", text: "{{ __('messages.swal_mission_assigned_desc') }}" },
            'amortize_completed': { icon: 'success', title: "{{ __('messages.swal_amortize_completed') }}", text: "{{ __('messages.swal_amortize_completed_desc') }}" },
            'profile_updated': { icon: 'success', title: "{{ __('messages.swal_success') }}", text: "{{ __('messages.swal_profile_completed_desc') }}" },
        };

        if (msg && alertas[msg]) {
            Swal.fire({ ...swalConfig, icon: alertas[msg].icon, title: alertas[msg].title, text: alertas[msg].text });
        }
        if (error) {
            Swal.fire({ ...swalConfig, icon: 'error', title: '⚠️ {{ __('messages.warning') }}', text: error, timer: null });
        }
    });

    function confirmarAccion(event, mensaje, icono = 'warning', colorBoton = '#e74c3c') {
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

    function enrutarNotificacionInteligenteLab(mensaje) {
        // Si habla de reservas, maquinaria, financiamiento o créditos -> Hub Tokenizar
        if (mensaje.includes('reserva') || mensaje.includes('crédito') || mensaje.includes('financiamiento') || mensaje.includes('máquina')) {
            abrirHubPersistente('hub-tokenizar'); 
        } 
        // Si habla de misiones, trabajos, postulantes -> Hub Misiones
        else if (mensaje.includes('misión') || mensaje.includes('postul')) {
            abrirHubPersistente('hub-publicar'); 
        } 
        // Por defecto -> Hub Catálogo
        else {
            abrirHubPersistente('hub-activar'); 
        }
        
        // Cierra el dropdown
        const dropdown = document.querySelector('.notif-dropdown');
        if(dropdown) dropdown.style.display = 'none';
    }
    </script>
@endpush
@extends('layouts.app')

@section('title', __('messages.lab_portal'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lab.css') }}?v=1.9">
@endpush

@section('content')
<div class="container">
    
    <!-- HEADER INDUSTRIAL DE ALTA GAMA -->
    <header class="lab-header-v2">
        <button type="button" class="lab-profile-trigger" onclick="abrirWorkspaceHubPersistente('workspace-perfil')" title="{{ __('messages.edit_profile') }}">
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
                <button type="button" class="notif-icon-btn" title="Notificaciones" onclick="const dd = this.nextElementSibling; dd.style.display = dd.style.display === 'flex' ? 'none' : 'flex';">
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
                            <div class="notif-item {{ !$n->is_read ? 'unread' : '' }}">
                                {{ $n->message }}
                                <div style="font-size: 10px; color: #7f8c8d; margin-top: 5px;">⏱️ {{ date('d M - H:i', strtotime($n->created_at)) }}</div>
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
            <div class="hub-card card-activar-neon" onclick="abrirWorkspaceHubPersistente('workspace-activar')">
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

            <div class="hub-card card-tokenizar-neon" onclick="abrirWorkspaceHubPersistente('workspace-tokenizar')">
                <div>
                    <div class="hub-image-container"><img src="{{ asset('images/hubs/icon_tokenizar.webp') }}" alt=""></div>
                    <h2>{{ __('messages.hub_tokenise_title') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.hub_tokenise_desc') }}</div>
                </div>
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            $perimetroReserva = ($enReserva / max(1, $totalMinted)) * $circunferencia;
                            $perimetroOfertados = ($ofertadosTotal / max(1, $totalMinted)) * $circunferencia;
                            $perimetroBajas = ($dadosDeBajaValor / max(1, $totalMinted)) * $circunferencia;
                        @endphp
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#3498db" stroke-width="12" stroke-dasharray="{{ $perimetroReserva }} 214"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#f1c40f" stroke-width="12" stroke-dasharray="{{ $perimetroOfertados }} 214" stroke-dashoffset="-{{ $perimetroReserva }}"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#e74c3c" stroke-width="12" stroke-dasharray="{{ $perimetroBajas }} 214" stroke-dashoffset="-{{ $perimetroReserva + $perimetroOfertados }}"></circle>
                    </svg>
                </div>
                <div>
                    <div class="main-hub-value" style="color: var(--c-green);">{{ number_format($totalMinted, 0, '.', ' ') }} FC</div>
                    <div class="bullet-metrics-compact">
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#3498db;"></span> <strong>{{ number_format($enReserva, 0, '.', ' ') }}</strong> {{ __('messages.lbl_reserve') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#f1c40f;"></span> <strong>{{ number_format($ofertadosTotal, 0, '.', ' ') }}</strong> {{ __('messages.lbl_frozen') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#e74c3c;"></span> <strong>{{ number_format($dadosDeBajaValor, 0, '.', ' ') }}</strong> {{ __('messages.status_retired') }}</div>
                    </div>
                </div>
            </div>

            <div class="hub-card card-publicar-neon" onclick="abrirWorkspaceHubPersistente('workspace-publicar')">
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
    <div id="workspace-activar" class="workspace-section">
        <div class="workspace-active-bar-v2" style="border-bottom: 2px solid #1abc9c;">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('workspace-activar')">← {{ __('messages.btn_back') }}</button>
            <div class="workspace-title-node" style="color: #1abc9c;">
                <img src="{{ asset('images/hubs/icon_activar.webp') }}" alt="">
                {{ __('messages.hub_activate_title') }}
            </div>
        </div>
        @include('lab.tabs.activar')
    </div>

    <div id="workspace-tokenizar" class="workspace-section">
        <div class="workspace-active-bar-v2" style="border-bottom: 2px solid #f1c40f;">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('workspace-tokenizar')">← {{ __('messages.btn_back') }}</button>
            <div class="workspace-title-node" style="color: #f1c40f;">
                <img src="{{ asset('images/hubs/icon_tokenizar.webp') }}" alt="">
                {{ __('messages.hub_tokenise_title') }}
            </div>
        </div>
        @include('lab.tabs.boveda')
    </div>

    <div id="workspace-publicar" class="workspace-section">
        <div class="workspace-active-bar-v2" style="border-bottom: 2px solid #e84393;">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('workspace-publicar')">← {{ __('messages.btn_back') }}</button>
            <div class="workspace-title-node" style="color: #e84393;">
                <img src="{{ asset('images/hubs/icon_ofertar.webp') }}" alt="">
                {{ __('messages.hub_publish_title') }}
            </div>
        </div>
        @include('lab.tabs.misiones')
    </div>

    <div id="workspace-perfil" class="workspace-section">
        <div class="workspace-active-bar-v2" style="border-bottom: 2px solid #3498db;">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('workspace-perfil')">← {{ __('messages.btn_back') }}</button>
            <div class="workspace-title-node" style="color: #3498db;">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px;"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                {{ __('messages.edit_profile') }}
            </div>
        </div>
        @include('lab.tabs.perfil')
    </div>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/lab-hubs.js') }}?v=1.4"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
    // 🔥 SOLUCIÓN ARREGLO DE HILOS: Limpieza total de clases ocultas para evitar congelamiento
    function abrirWorkspaceHubPersistente(workspaceId) {
        sessionStorage.setItem('active_lab_workspace', workspaceId);
        
        document.querySelectorAll('.workspace-section').forEach(sec => {
            sec.style.display = 'none';
            sec.classList.remove('active');
        });
        
        const homeHub = document.getElementById('main-home-hub-view');
        if (homeHub) homeHub.style.display = 'none';
        
        const target = document.getElementById(workspaceId);
        if (target) {
            target.style.display = 'block';
            setTimeout(() => target.classList.add('active'), 50);
        }
    }

    function regresarAlHubCentralPersistente(workspaceId) {
        sessionStorage.removeItem('active_lab_workspace');
        
        const target = document.getElementById(workspaceId);
        if (target) {
            target.style.display = 'none';
            target.classList.remove('active');
        }
        
        const homeHub = document.getElementById('main-home-hub-view');
        if (homeHub) homeHub.style.display = 'grid';
    }

    document.addEventListener("DOMContentLoaded", function() {
        const workspaceGuardado = sessionStorage.getItem('active_lab_workspace');
        if (workspaceGuardado) {
            abrirWorkspaceHubPersistente(workspaceGuardado);
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
            'asset_deleted_ok': { icon: 'success', title: '🗑️ Éxito', text: "{{ __('messages.asset_deleted_ok') }}" },
            'mint_ok': { icon: 'success', title: '🚀 Tokenización exitosa', text: 'Tus activos están listos en la bóveda.' },
            'retired_ok': { icon: 'warning', title: '⚠️ Activo retirado', text: 'Se aplicó la penalización en tu saldo.' }
        };

        if (msg && alertas[msg]) {
            Swal.fire({ ...swalConfig, icon: alertas[msg].icon, title: alertas[msg].title, text: alertas[msg].text });
        }
        if (error) {
            Swal.fire({ ...swalConfig, icon: 'error', title: '⚠️ Atención', text: error, timer: null });
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
    </script>
@endpush
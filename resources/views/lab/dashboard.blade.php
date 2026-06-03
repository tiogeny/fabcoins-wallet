@extends('layouts.app')

@section('title', __('messages.lab_portal'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lab.css') }}?v=1.3">
@endpush

@section('content')
<div class="container">
    
    <!-- HEADER GENERAL -->
    <header class="header" style="background: var(--bg-card); border-left: 6px solid var(--c-green); padding: 20px 30px; border-radius: 12px; margin-bottom: 25px;">
        <div class="profile-info" style="display: flex; gap: 15px; align-items: center;">
            <img src="{{ $lab->avatar_url ?: 'https://via.placeholder.com/60' }}" style="width: 50px; height: 50px; border-radius: 50%; border: 2px solid var(--c-green);">
            <div>
                <a href="{{ route('lab.profile.edit') }}" class="font-bold text-white" style="text-decoration:none; font-size: 18px;">
                    🏢 {{ $lab->name }} <small style="color: var(--text-muted); font-weight: normal; margin-left: 5px; font-size: 12px;">⚙️ {{ __('messages.edit_profile') }}</small>
                </a>
                <div style="color: var(--c-yellow); font-size: 12px; margin-top: 3px;">⭐ {{ number_format($lab->reputation_score, 1) }} {{ __('messages.reputation') }}</div>
            </div>
        </div>
        
        <div style="display: flex; align-items: center; gap: 25px;">
            <a href="#" title="{{ __('messages.global_community') }}" style="text-decoration: none; font-size: 22px;">🌍</a>
            <div class="notif-wrapper" style="font-size: 22px; cursor: pointer;">🔔</div>
            <form method="POST" action="{{ route('logout') }}" style="margin:0;"> 
                @csrf 
                <button type="submit" class="btn-logout" style="padding: 6px 15px; font-size: 13px;">{{ __('messages.btn_logout') }}</button> 
            </form>
        </div>
    </header>

    <!-- =======================================================================
         🏠 VISTA 1: CENTRAL HUBS (ALINEACIÓN HORIZONTAL SANADA)
         ======================================================================= -->
    <div id="main-home-hub-view" class="home-hubs-wrapper">
        <div class="action-hubs-grid">
            
            <!-- HUB A: ACTIVAR -->
            <div class="hub-card card-activar-neon" onclick="abrirWorkspaceHub('workspace-activar')">
                <div>
                    <div class="hub-image-container">
                        <img src="{{ asset('images/hubs/icon_activar.webp') }}" alt="{{ __('messages.hub_activate_title') }}">
                    </div>
                    <h2>{{ __('messages.hub_activate_title') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.hub_activate_desc') }}</div>
                </div>
                
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            $circunferencia = 213.6;
                            $perimetroM = ($totalMaquinasCount / $totalActivosCount) * $circunferencia;
                            $perimetroS = ($totalServiciosCount / $totalActivosCount) * $circunferencia;
                            $perimetroL = ($totalLabsConectados / $totalActivosCount) * $circunferencia;
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

            <!-- HUB B: TOKENIZAR (ALINEACIÓN VERTICAL PERFECCIONADA) -->
            <div class="hub-card card-tokenizar-neon" onclick="abrirWorkspaceHub('workspace-tokenizar')">
                <div>
                    <div class="hub-image-container">
                        <img src="{{ asset('images/hubs/icon_tokenizar.webp') }}" alt="{{ __('messages.hub_tokenise_title') }}">
                    </div>
                    <h2>{{ __('messages.hub_tokenise_title') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.hub_tokenise_desc') }}</div>
                </div>
                
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            $perimetroReserva = ($enReserva / $totalMinted) * $circunferencia;
                            $perimetroOfertados = ($ofertadosTotal / $totalMinted) * $circunferencia;
                            $perimetroBajas = ($dadosDeBajaValor / $totalMinted) * $circunferencia;
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

            <!-- HUB C: OFERTAR (SINTAXIS POR_ACEPTAR CORREGIDA) -->
            <div class="hub-card card-ofertar-neon" onclick="abrirWorkspaceHub('workspace-ofertar')">
                <div>
                    <div class="hub-image-container">
                        <img src="{{ asset('images/hubs/icon_ofertar.webp') }}" alt="{{ __('messages.hub_offer_title') }}">
                    </div>
                    <h2>{{ __('messages.hub_offer_title') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.hub_offer_desc') }}</div>
                </div>
                
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            $perimetroComp = ($statsMisiones['completadas'] / $totalMisionesCount) * $circunferencia;
                            $perimetroExec = ($statsMisiones['en_ejecucion'] / $totalMisionesCount) * $circunferencia;
                            $perimetroOpen = (($statsMisiones['abiertas'] + $statsMisiones['por_aceptar']) / $totalMisionesCount) * $circunferencia;
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

    <!-- =======================================================================
         🖥️ VISTA 2: ESPACIOS DE TRABAJO (TÍTULO IZQUIERDA / BOTÓN DERECHA COMPACTO)
         ======================================================================= -->
    
    <!-- WORKSPACE A -->
    <div id="workspace-activar" class="workspace-section">
        <div class="workspace-active-bar-v2" style="border-bottom: 2px solid #1abc9c;">
            <div class="workspace-title-node" style="color: #1abc9c;">
                <img src="{{ asset('images/hubs/icon_activar.webp') }}" alt="Activar">
                {{ __('messages.hub_activate_title') }}
            </div>
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentral('workspace-activar')">← {{ __('messages.btn_back') }}</button>
        </div>
        @include('lab.tabs.boveda')
    </div>

    <!-- WORKSPACE B -->
    <div id="workspace-tokenizar" class="workspace-section">
        <div class="workspace-active-bar-v2" style="border-bottom: 2px solid #f1c40f;">
            <div class="workspace-title-node" style="color: #f1c40f;">
                <img src="{{ asset('images/hubs/icon_tokenizar.webp') }}" alt="Tokenizar">
                {{ __('messages.hub_tokenise_title') }}
            </div>
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentral('workspace-tokenizar')">← {{ __('messages.btn_back') }}</button>
        </div>
        <div class="card">
            <p class="text-muted" style="text-align: center; padding: 40px;">[Módulo B: Libro de Registro de Emisiones y Valorización Contable MINT - Listo para programar modularmente]</p>
        </div>
    </div>

    <!-- WORKSPACE C -->
    <div id="workspace-ofertar" class="workspace-section">
        <div class="workspace-active-bar-v2" style="border-bottom: 2px solid #e84393;">
            <div class="workspace-title-node" style="color: #e84393;">
                <img src="{{ asset('images/hubs/icon_ofertar.webp') }}" alt="Ofertar">
                {{ __('messages.hub_offer_title') }}
            </div>
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentral('workspace-ofertar')">← {{ __('messages.btn_back') }}</button>
        </div>
        @include('lab.tabs.misiones')
    </div>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/lab-hubs.js') }}?v=1.3"></script>
@endpush
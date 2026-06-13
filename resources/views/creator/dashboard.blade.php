@extends('layouts.app')

@section('title', __('messages.creator_portal') . ' | FabCoins')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lab.css') }}?v=2.0">
@endpush

@section('content')
<div class="container">
    
    <header class="lab-header-v2">
       <button type="button" class="lab-profile-trigger" 
        onclick="abrirWorkspaceHubPersistente('workspace-mercado'); setTimeout(() => { document.getElementById('seccion-perfil-habilidades').scrollIntoView({ behavior: 'smooth' }); }, 250);" 
        title="{{ __('messages.edit_profile') }}">
            <div class="lab-avatar-wrapper status-active" style="border-color: #3498db;">
                <img src="{{ $creator->avatar_url ?: 'https://ui-avatars.com/api/?name='.urlencode($creator->name).'&background=3498db&color=fff' }}" alt="{{ $creator->name }}">
            </div>
            <div class="lab-identity-meta">
                <h1>{{ $creator->name }}</h1>
                <div class="lab-reputation-stars" style="color: #3498db;">
                    ⭐ {{ number_format($creator->reputation_score, 1) }} <span>{{ __('messages.reputation') }}</span>
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
                    @if(($unreadCount ?? 0) > 0)
                        <span class="notif-badge-v2" style="background: #3498db;">{{ $unreadCount }}</span>
                    @endif
                </button>
                
                <div class="notif-dropdown">
                    @if(!isset($notificaciones) || $notificaciones->isEmpty())
                        <div class="notif-item" style="text-align: center; color: #7f8c8d; border-left: none;">{{ __('messages.no_notifications') }}</div>
                    @else
                        @foreach($notificaciones as $n)
                            <div class="notif-item {{ !$n->is_read ? 'unread' : '' }}" style="border-left-color: #3498db;">
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

    @if($creditoActual)
        @if($creditoActual->status == 'pending')
            <div class="card" style="border: 1px dashed #f39c12; background: rgba(243, 156, 18, 0.02); margin-top: 20px; padding: 20px; border-radius: 12px;">
                <h3 style="color: #f39c12; margin-top: 0; font-family: 'Rajdhani', sans-serif; font-weight: 700; text-transform: uppercase; font-size: 16px; letter-spacing: 0.5px;">🎓 {{ __('messages.isa_proposal_title') }}</h3>
                <p style="font-size: 13px; color: #cbd5e0; line-height: 1.5; margin-bottom: 15px;">{{ __('messages.isa_proposal_desc1') }} <strong>{{ $creditoActual->lab_name }}</strong> {{ __('messages.isa_proposal_desc2') }} <strong style="color:#f1c40f;">{{ number_format($creditoActual->amount_initial, 0) }} FC</strong>. {{ __('messages.isa_proposal_desc3') }} <em style="color:#a0aec0;">"{{ $creditoActual->description }}"</em>.</p>
                <form action="{{ route('creator.sign_credit') }}" method="POST" style="margin:0;">
                    @csrf 
                    <input type="hidden" name="contract_id" value="{{ $creditoActual->id }}">
                    <button type="submit" class="btn-logout-v2" style="background: #2ecc71; border-color: #2ecc71; color: white; padding: 0 24px; height: 36px; font-size: 11.5px; font-weight: bold; border-radius: 6px; cursor: pointer;">{{ __('messages.btn_sign_isa') }}</button>
                </form>
            </div>
        @elseif($creditoActual->status == 'active')
            @php $porcentaje = round((($creditoActual->amount_initial - $creditoActual->amount_remaining) / $creditoActual->amount_initial) * 100); @endphp
            <div class="card" style="border: 1px solid rgba(241, 196, 15, 0.15); background: #1c2230; margin-top: 20px; padding: 20px; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                <h3 style="color: #f1c40f; margin-top: 0; font-size: 15px; font-family: 'Rajdhani', sans-serif; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">🎓 {{ __('messages.isa_active_title') }} | {{ $creditoActual->lab_name }}</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: center; margin-top: 12px;">
                    <div>
                        <div style="display: flex; justify-content: space-between; font-size: 12px; color: #cbd5e0; margin-bottom: 5px;">
                            <span>{{ __('messages.lbl_progress') }} (<strong>{{ $porcentaje }}%</strong>)</span>
                            <span style="font-family:'Rajdhani', sans-serif; font-weight:700; color:#f1c40f;">{{ __('messages.lbl_debt') }}: {{ number_format($creditoActual->amount_remaining, 0) }} / {{ number_format($creditoActual->amount_initial, 0) }} FC</span>
                        </div>
                        <div style="width: 100%; height: 4px; background: rgba(255,255,255,0.06); border-radius: 2px; overflow: hidden;">
                            <div style="width: {{ $porcentaje }}%; height: 100%; background: #f1c40f; border-radius: 2px;"></div>
                        </div>
                    </div>
                    
                    @if(count($historialAbonos) > 0)
                        <div style="background: #131722; padding: 10px; border-radius: 6px; max-height: 70px; overflow-y: auto; border: 1px solid rgba(255,255,255,0.01);">
                            <div style="font-size: 9px; color: #7f8c8d; text-transform: uppercase; font-weight: 700; margin-bottom: 4px; letter-spacing: 0.3px;">📉 {{ __('messages.isa_payment_history') }}</div>
                            @foreach($historialAbonos as $abono)
                                <div style="display: flex; justify-content: space-between; font-size: 11px; color: #bdc3c7; margin-bottom: 3px;">
                                    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;">✓ {{ $abono->title }}</span>
                                    <strong style="color: #2ecc71; font-family: 'Rajdhani', sans-serif;">-{{ number_format($abono->amount, 0) }} FC</strong>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif
    @endif

    <div id="main-home-hub-view" class="home-hubs-wrapper">
        <div class="action-hubs-grid">
            
            <div class="hub-card card-activar-neon" onclick="abrirWorkspaceHubPersistente('workspace-mercado')" style="border-bottom-color: #3498db;">
                <div>
                    <div class="hub-image-container"><img src="{{ asset('images/hubs/icon_activar.webp') }}" style="filter: hue-rotate(180deg);" alt=""></div>
                    <h2>{{ __('messages.hub_market_btn') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.map_explorer_desc') }}</div>
                </div>
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            $circunferencia = 213.6;
                            $totalRes = max(1, $misReservas->count());
                            $pPen = ($misReservas->where('status', 'pending')->count() / $totalRes) * $circunferencia;
                            $pCom = ($misReservas->where('status', 'completed')->count() / $totalRes) * $circunferencia;
                            $pOth = ($misReservas->whereNotIn('status', ['pending', 'completed'])->count() / $totalRes) * $circunferencia;
                        @endphp
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#f1c40f" stroke-width="12" stroke-dasharray="{{ $pPen }} 214"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2ecc71" stroke-width="12" stroke-dasharray="{{ $pCom }} 214" stroke-dashoffset="-{{ $pPen }}"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#3498db" stroke-width="12" stroke-dasharray="{{ $pOth }} 214" stroke-dashoffset="-{{ $pPen + $pCom }}"></circle>
                    </svg>
                </div>
                <div>
                    <div class="main-hub-value" style="color: #3498db;">{{ $misReservas->count() }} {{ __('messages.lbl_assets_unit') }}</div>
                    <div class="bullet-metrics-compact">
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#f1c40f;"></span> <strong>{{ $misReservas->where('status', 'pending')->count() }}</strong> {{ __('messages.status_enlisted') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#2ecc71;"></span> <strong>{{ $misReservas->where('status', 'completed')->count() }}</strong> {{ __('messages.status_approved_consumed') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#3498db;"></span> <strong>{{ $misReservas->whereNotIn('status', ['pending', 'completed'])->count() }}</strong> {{ __('messages.lbl_reserva_bullet') }}</div>
                    </div>
                </div>
            </div>

            <div class="hub-card card-publicar-neon" onclick="abrirWorkspaceHubPersistente('workspace-misiones')">
                <div>
                    <div class="hub-image-container"><img src="{{ asset('images/hubs/icon_ofertar.webp') }}" alt=""></div>
                    <h2>{{ __('messages.hub_missions_btn') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.market_capacity_desc') }}</div>
                </div>
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            $totalMisionesYPost = max(1, $misionesAbiertas->count() + $misPostulaciones->count());
                            $pAbiertas = ($misionesAbiertas->count() / $totalMisionesYPost) * $circunferencia;
                            $pPostuladas = ($misPostulaciones->where('status', 'pending')->count() / $totalMisionesYPost) * $circunferencia;
                            $pAceptadas = ($misPostulaciones->where('status', 'accepted')->count() / $totalMisionesYPost) * $circunferencia;
                        @endphp
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#3498db" stroke-width="12" stroke-dasharray="{{ $pAbiertas }} 214"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#e84393" stroke-width="12" stroke-dasharray="{{ $pPostuladas }} 214" stroke-dashoffset="-{{ $pAbiertas }}"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2ecc71" stroke-width="12" stroke-dasharray="{{ $pAceptadas }} 214" stroke-dashoffset="-{{ $pAbiertas + $pPostuladas }}"></circle>
                    </svg>
                </div>
                <div>
                    <div class="main-hub-value" style="color: #e84393;">{{ $misionesAbiertas->count() }} {{ __('messages.lbl_missions_unit') }}</div>
                    <div class="bullet-metrics-compact">
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#3498db;"></span> <strong>{{ $misionesAbiertas->count() }}</strong> {{ __('messages.lbl_open_bullet') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#e84393;"></span> <strong>{{ $misPostulaciones->where('status', 'pending')->count() }}</strong> {{ __('messages.lbl_working_bullet') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#2ecc71;"></span> <strong>{{ $misPostulaciones->where('status', 'accepted')->count() }}</strong> {{ __('messages.lbl_closed_bullet') }}</div>
                    </div>
                </div>
            </div>

            <div class="hub-card card-tokenizar-neon" onclick="abrirWorkspaceHubPersistente('workspace-billetera')">
                <div>
                    <div class="hub-image-container"><img src="{{ asset('images/hubs/icon_tokenizar.webp') }}" style="filter: hue-rotate(280deg);" alt=""></div>
                    <h2>{{ __('messages.hub_wallet_btn') }}</h2>
                    <div class="hub-subtitle">{{ __('messages.rates_reg_desc') }}</div>
                </div>
                <div class="donut-chart-box">
                    <svg class="donut-svg-canvas" width="95" height="95" viewBox="0 0 90 90">
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2c3e50" stroke-width="12"></circle>
                        @php 
                            $deudaRestante = $creditoActual ? $creditoActual->amount_remaining : 0;
                            $totalPatrimonio = max(1, $saldoTotal + $deudaRestante);
                            $pLiquido = ($saldoTotal / $totalPatrimonio) * $circunferencia;
                            $pDeuda = ($deudaRestante / $totalPatrimonio) * $circunferencia;
                        @endphp
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#2ecc71" stroke-width="12" stroke-dasharray="{{ $pLiquido }} 214"></circle>
                        <circle cx="45" cy="45" r="34" fill="transparent" stroke="#f1c40f" stroke-width="12" stroke-dasharray="{{ $pDeuda }} 214" stroke-dashoffset="-{{ $pLiquido }}"></circle>
                    </svg>
                </div>
                <div>
                    <div class="main-hub-value" style="color: var(--c-green);">{{ number_format($saldoTotal, 0, '.', ' ') }} FC</div>
                    <div class="bullet-metrics-compact">
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#2ecc71;"></span> {{ __('messages.kpi_wallet') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#f1c40f;"></span> <strong>{{ number_format($deudaRestante, 0, '.', ' ') }}</strong> {{ __('messages.lbl_debt') }}</div>
                        <div class="metric-compact-row"><span class="color-dot-indicator" style="background:#3498db;"></span> <strong>{{ count($misSkillsIds) }}</strong> {{ __('messages.lbl_assets_unit') }}</div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div id="workspace-mercado" class="workspace-section">
        <div class="workspace-active-bar-v2" style="border-bottom: 2px solid #3498db;">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('workspace-mercado')">← {{ __('messages.btn_back') }}</button>
            <div class="workspace-title-node" style="color: #3498db;">
                <img src="{{ asset('images/hubs/icon_activar.webp') }}" style="filter: hue-rotate(180deg);" alt="">
                {{ __('messages.hub_market_btn') }}
            </div>
        </div>
        @include('creator.tabs.1_mercado')
    </div>

    <div id="workspace-misiones" class="workspace-section">
        <div class="workspace-active-bar-v2" style="border-bottom: 2px solid #e84393;">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('workspace-misiones')">← {{ __('messages.btn_back') }}</button>
            <div class="workspace-title-node" style="color: #e84393;">
                <img src="{{ asset('images/hubs/icon_ofertar.webp') }}" alt="">
                {{ __('messages.hub_missions_btn') }}
            </div>
        </div>
        @include('creator.tabs.2_misiones')
    </div>

    <div id="workspace-billetera" class="workspace-section">
        <div class="workspace-active-bar-v2" style="border-bottom: 2px solid #f1c40f;">
            <button type="button" class="btn-back-minimal" onclick="regresarAlHubCentralPersistente('workspace-billetera')">← {{ __('messages.btn_back') }}</button>
            <div class="workspace-title-node" style="color: #f1c40f;">
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
    function abrirWorkspaceHubPersistente(workspaceId) {
        sessionStorage.setItem('active_creator_workspace', workspaceId);
        
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
        sessionStorage.removeItem('active_creator_workspace');
        
        const target = document.getElementById(workspaceId);
        if (target) {
            target.style.display = 'none';
            target.classList.remove('active');
        }
        
        const homeHub = document.getElementById('main-home-hub-view');
        if (homeHub) homeHub.style.display = 'grid';
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Restauración de estado dinámico
        const workspaceGuardado = sessionStorage.getItem('active_creator_workspace');
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

        // 🌐 SCRIPTS CON SOPORTE MULTIDIOMA INTEGRADO
        const alertas = {
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
    </script>
@endpush
@extends('layouts.app')

@section('title', $user->name . ' | ' . __('messages.app_subtitle'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lab.css') }}?v=1.0">
    <link rel="stylesheet" href="{{ asset('css/creator.css') }}?v=1.6">
@endpush

@section('content')
<div class="profile-container-v2 container px-4">
    
    {{-- 1. CABECERA PÚBLICA (NAVBAR GLOBAL) --}}
    <nav class="public-navbar">
        <a href="{{ route('home') }}" class="public-logo-wrapper">
            <img src="{{ asset('images/logo-icon.webp') }}" alt="FabCoins">
            <h2 class="public-logo-text">Fab<span>Coins</span></h2>
        </a>
        
        <div class="public-nav-actions">
            <div class="lang-pill">
                <a href="?{{ http_build_query(array_merge($_GET, ['lang' => 'es'])) }}" class="{{ app()->getLocale() == 'es' ? 'lang-active' : 'lang-muted' }}">🇲🇽</a>
                <a href="?{{ http_build_query(array_merge($_GET, ['lang' => 'en'])) }}" class="{{ app()->getLocale() == 'en' ? 'lang-active' : 'lang-muted' }}">🇺🇸</a>
            </div>

            @auth
                <a href="{{ route('dashboard') }}" class="btn-premium btn-blue-hub m-0 font-12">
                    ⚙️ {{ __('messages.btn_dashboard') }}
                </a>
            @else
                <a href="{{ route('login', ['tab' => 'register']) }}" class="btn-premium m-0 font-12 btn-success-nav">
                    🚀 {{ __('messages.btn_join_network') }}
                </a>
            @endauth
        </div>
    </nav>

    @php
        $totalResenas = isset($historialUnificado) ? count($historialUnificado) : 0;
        $totalMisiones = isset($historialUnificado) ? collect($historialUnificado)->where('context_type', 'mission')->count() : 0;
        $avatarFinal = !empty($user->avatar_url) ? $user->avatar_url : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=131722&color=3498db&size=200&font-size=0.4&bold=true';
    @endphp

    {{-- 2. BLOQUE SUPERIOR DE IDENTIDAD --}}
    <div class="grid-top-identity">
        
        {{-- Pasaporte Maker / Nodo (Columna Izquierda) --}}
        <div class="premium-glass-card m-0 text-center">
            <div class="avatar-showcase">
                <img src="{{ $avatarFinal }}" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=131722&color=3498db&size=200'" alt="Avatar">
            </div>
            
            <h1 class="font-24 font-bold text-white-pure m-0 mb-5px" style="font-family: 'Rajdhani', sans-serif;">{{ $user->name }}</h1>
            
            {{-- 🟢 REPARACIÓN DE BADGE: Selección de color y clave bilingüe según rol real --}}
            <div class="mb-10">
                @if($user->role === 'lab')
                    <span class="badge-role-fintech badge-semantic badge-lab font-11">
                        {{ __('messages.badge_official_lab') }}
                    </span>
                @else
                    <span class="badge-role-fintech badge-semantic badge-orange font-11">
                        {{ __('messages.badge_creator_spec') }}
                    </span>
                @endif
            </div>

            <div class="text-warning-neon font-12 font-bold mb-10 text-center-wrapper">
                {!! str_repeat('★', floor($user->reputation_score ?? 5)) !!}{!! str_repeat('☆', 5 - floor($user->reputation_score ?? 5)) !!}
                <span class="text-neutral-neon font-11 font-mono">({{ number_format($user->reputation_score ?? 5.0, 1) }})</span>
            </div>

            @if(!empty($user->city) || !empty($user->country))
                <div class="text-blue-neon font-12 font-bold mb-15">
                    📍 <span class="text-neutral-muted font-11">{{ implode(', ', array_filter([$user->city, $user->country])) }}</span>
                </div>
            @endif

            {{-- Nodo Reclutador (Solo para Creadores) --}}
            @if(auth()->check() && auth()->user()->role === 'lab' && auth()->user()->id !== $user->id && $user->role === 'creator')
                <div class="mt-10 pt-10" style="border-top: 1px dashed rgba(255,255,255,0.05);">
                    @if(isset($misMisionesAbiertas) && $misMisionesAbiertas->isEmpty())
                        <p class="text-danger-neon m-0 font-11 font-bold">{{ __('messages.err_no_open_missions') }}</p>
                    @elseif(isset($misMisionesAbiertas))
                        <form action="{{ route('public.profile.invite', ['slugOrId' => $user->slug ?: $user->id]) }}" method="POST" class="m-0 flex-col-gap-10">
                            @csrf
                            <input type="hidden" name="creator_id" value="{{ $user->id }}">
                            <select name="mission_id" class="premium-select font-11" style="height: 32px;" required>
                                <option value="">-- {{ __('messages.opt_select_mission') }} --</option>
                                @foreach($misMisionesAbiertas as $ma)
                                    <option value="{{ $ma->id }}">{{ $ma->title }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-premium btn-blue-hub m-0 w-100 font-11" style="height: 32px;">🎯 {{ __('messages.btn_invite_mission') }}</button>
                        </form>
                    @endif
                </div>
            @endif

            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-value">{{ $totalResenas }}</div>
                    <div class="stat-label font-11">{{ __('messages.lbl_reviews_count') }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $totalMisiones }}</div>
                    <div class="stat-label font-11">{{ __('messages.lbl_missions_count') }}</div>
                </div>
            </div>
        </div>

        {{-- Presentación y Redes Reales (Columna Derecha) --}}
        <div class="premium-glass-card m-0 flex-col-gap-15" style="justify-content: space-between;">
            <div>
                <label class="premium-label font-11">{{ __('messages.title_prof_bio') }}</label>
                <div class="text-neutral-muted font-12" style="line-height: 1.6;">
                    {!! $user->bio ?: __('messages.empty_bio') !!}
                </div>
            </div>

            @if(!empty($user->social_fabacademy) || !empty($user->social_instagram) || !empty($user->social_linkedin) || !empty($user->social_github) || !empty($user->social_portfolio))
                <div class="social-bar m-0 mt-20" style="flex-wrap: wrap; gap: 8px;">
                    @if(!empty($user->social_fabacademy))
                        <a href="{{ $user->social_fabacademy }}" target="_blank" class="social-tag social-tag-fab font-11">
                            🎓 Fab Academy
                        </a>
                    @endif
                    @if(!empty($user->social_linkedin))
                        <a href="{{ $user->social_linkedin }}" target="_blank" class="social-tag font-11">
                            💼 LinkedIn
                        </a>
                    @endif
                    @if(!empty($user->social_portfolio))
                        <a href="{{ $user->social_portfolio }}" target="_blank" class="social-tag font-11">
                            🌐 {{ __('messages.lbl_portfolio') }}
                        </a>
                    @endif
                    @if(!empty($user->social_github))
                        <a href="{{ $user->social_github }}" target="_blank" class="social-tag font-11">
                            🐙 GitHub
                        </a>
                    @endif
                    @if(!empty($user->social_instagram))
                        <a href="{{ $user->social_instagram }}" target="_blank" class="social-tag font-11">
                            📸 Instagram
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- 3. BLOQUE INFERIOR DE MATRICES CONTABLES POLIMÓRFICAS --}}
    @if($user->role === 'lab')
        {{-- 📐 EXCLUSIVO LAB: TRÍO DE TARJETAS PARALELAS INTEGRADAS CON TUS CLASES NATIVAS --}}
        <div class="grid-tablas mt-25">
            
            {{-- Columna A: Catálogo de Infraestructura Disponible --}}
            <div class="premium-glass-card m-0" style="display: flex; flex-direction: column; justify-content: space-between; gap: 20px;">
                <div>
                    <div class="premium-glass-card-header">
                        <h3 class="premium-glass-card-title m-0">🏪 {{ __('messages.inv_title') }}</h3>
                    </div>
                    <div class="flex-col-gap-10 mt-10">
                        @forelse($misActivos as $act)
                            @php 
                                $bgBadge = '#7f8c8d';
                                if($act->asset_type === 'machine') $bgBadge = '#1abc9c'; 
                                elseif($act->asset_type === 'service') $bgBadge = '#3498db'; 
                                elseif(in_array($act->asset_type, ['lab', 'space', 'workshop'])) $bgBadge = '#9b59b6'; 
                            @endphp
                            <div class="rate-item-row" style="padding: 10px 14px;">
                                <div class="flex-col-gap-2">
                                    <span class="rate-item-title">{{ $act->custom_name }}</span>
                                    <span class="badge-semantic font-11" style="background: {{ $bgBadge }}; font-size: 8px; padding: 1px 6px; border-radius: 12px; width: max-content;">{{ $act->display_name }}</span>
                                </div>
                                <span class="td-amount-gold font-bold font-rajdhani-15" style="color: #2ecc71;">{{ number_format($act->set_price_fc, 2) }} FC</span>
                            </div>
                        @empty
                            <p class="text-neutral-muted m-0 font-italic font-11">{{ __('messages.inv_empty') }}</p>
                        @endforelse
                    </div>
                </div>

                {{-- Enlace Directo Filtrado al Mercado --}}
                @if(auth()->user() && auth()->user()->role === 'creator')
                    <div>
                        <a href="#" onclick="event.preventDefault(); localStorage.setItem('auto_filtrar_lab', '{{ $user->id }}'); window.location.href='{{ route('dashboard') }}';" class="btn-premium font-11 btn-yellow-hub">
                            🏪 {{ __('messages.btn_filter_activos_lab') }}
                        </a>
                    </div>
                @endif
            </div>

            {{-- Columna B: Bolsa de Misiones del Nodo --}}
            <div class="premium-glass-card m-0" style="display: flex; flex-direction: column; justify-content: space-between; gap: 20px;">
                <div>
                    <div class="premium-glass-card-header">
                        <h3 class="premium-glass-card-title m-0">🎯 {{ __('messages.hub_missions_btn') }}</h3>
                    </div>
                    <div class="flex-col-gap-10 mt-10">
                        @forelse($misMisionesNodo as $mn)
                            <div class="rate-item-row" style="padding: 10px 14px;">
                                <div class="flex-col-gap-2" style="max-width: 70%;">
                                    <span class="rate-item-title" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">{{ $mn->title }}</span>
                                    <span class="td-date-dim font-11">📅 {{ \Carbon\Carbon::parse($mn->deadline)->format('d M Y') }}</span>
                                </div>
                                <span class="td-amount-gold font-bold font-rajdhani-15">{{ number_format($mn->reward_fc, 0) }} FC</span>
                            </div>
                        @empty
                            <p class="text-neutral-muted m-0 font-italic font-11">{{ __('messages.empty_open_missions') }}</p>
                        @endforelse
                    </div>
                </div>

                {{-- Enlace Directo Filtrado a la Bolsa de Trabajo --}}
                @if(auth()->user() && auth()->user()->role === 'creator')
                    <div>
                        <a href="#" onclick="event.preventDefault(); localStorage.setItem('auto_filtrar_mision', '{{ $user->id }}'); window.location.href='{{ route('dashboard') }}';" class="btn-premium font-11 btn-pink-hub">
                            🚀 {{ __('messages.btn_filter_missions_lab') }}
                        </a>
                    </div>
                @endif
            </div>

            {{-- Columna C: Historial Colectivo de Reseñas --}}
            <div class="premium-glass-card m-0">
                <div class="premium-glass-card-header">
                    <h3 class="premium-glass-card-title m-0">🚀 {{ __('messages.title_portfolio_reviews') }}</h3>
                </div>

                @if(empty($historialUnificado) || count($historialUnificado) == 0)
                    <div class="empty-state-warning text-center" style="padding: 20px;">
                        <p class="text-neutral-muted m-0 font-11 font-italic">{{ __('messages.empty_reviews') }}</p>
                    </div>
                @else
                    <div class="flex-col-gap-10" style="max-height: 380px; overflow-y: auto; padding-right: 4px;">
                        @foreach($historialUnificado as $r)
                            @php 
                                $esMision = ($r->context_type === 'mission');
                                $tipoClase = $esMision ? 'review-mission' : 'review-market';
                                $iconoContexto = $esMision ? '🎯' : '🏪';
                            @endphp
                            <div class="review-item-card {{ $tipoClase }}" style="padding: 12px; margin-bottom: 2px;">
                                <div class="flex-between" style="align-items: flex-start; gap: 8px;">
                                    <div style="max-width: 65%;">
                                        <div class="font-12 font-bold text-white-pure mb-5px" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $iconoContexto }} {{ $r->context_title }}
                                        </div>
                                        <div class="font-11 text-neutral-neon" style="font-size: 10px;">
                                            {{ __('messages.lbl_reviewed_by') }} <span class="text-blue-neon font-bold">{{ $r->reviewer_name }}</span>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="tx-amount-value text-green-neon font-12 font-bold">+{{ number_format($r->context_fc ?? 0, 0) }} <small style="font-size: 9px;">FC</small></div>
                                        <div class="text-warning-node font-11" style="font-size: 9px; margin-top: 2px;">{!! str_repeat('★', floor($r->rating)) !!}</div>
                                    </div>
                                </div>
                                @if(!empty($r->comment))
                                    <p class="m-0 review-comment-box font-12 text-neutral-muted" style="font-size: 11px; padding: 6px 10px; margin-top: 6px;">
                                        "{{ $r->comment }}"
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    @else
        {{-- 👤 EXCLUSIVO CREADOR: EL DISEÑO ORIGINAL DE 2 COLUMNAS TOTALMENTE INTACTO --}}
        <div class="grid-bottom-data mt-25">
            <div class="premium-glass-card m-0">
                <div class="premium-glass-card-header">
                    <h3 class="premium-glass-card-title m-0">🛠️ {{ __('messages.title_validated_skills') }}</h3>
                </div>
                @if(isset($misHabilidades) && $misHabilidades->isEmpty())
                    <p class="text-neutral-muted m-0 font-italic font-11">{{ __('messages.empty_skills') }}</p>
                @else
                    @php 
                        $locale = app()->getLocale(); 
                        $tecnicas = $misHabilidades->where('type', 'hard');
                        $blandas = $misHabilidades->where('type', 'soft');
                    @endphp
                    <label class="premium-label font-11 text-blue-neon font-bold mt-10 mb-10">{{ __('messages.lbl_hard_skills') }}:</label>
                    <div class="skills-chips-matrix mb-20">
                        @foreach($tecnicas as $sk)
                            <div class="endorsement-chip hard m-0 font-12">
                                <span>{{ $locale === 'en' ? $sk->name_en : $sk->name_es }}</span>
                                @if(($sk->endorsements_count ?? 0) > 0)
                                    <span class="endorsement-count validated font-11">✓ {{ $sk->endorsements_count }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                    <label class="premium-label font-11 text-warning-neon font-bold mt-10 mb-10">{{ __('messages.lbl_soft_skills') }}:</label>
                    <div class="skills-chips-matrix">
                        @foreach($blandas as $sk)
                            <div class="endorsement-chip soft m-0 font-12">
                                <span>{{ $locale === 'en' ? $sk->name_en : $sk->name_es }}</span>
                                @if(($sk->endorsements_count ?? 0) > 0)
                                    <span class="endorsement-count validated font-11">✓ {{ $sk->endorsements_count }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="premium-glass-card m-0">
                <div class="premium-glass-card-header">
                    <h3 class="premium-glass-card-title m-0">🚀 {{ __('messages.title_portfolio_reviews') }}</h3>
                </div>
                @if(empty($historialUnificado) || count($historialUnificado) == 0)
                    <div class="empty-state-warning text-center">
                        <p class="text-neutral-muted m-0 font-11 font-italic">{{ __('messages.empty_reviews') }}</p>
                    </div>
                @else
                    <div class="flex-col-gap-10">
                        @foreach($historialUnificado as $r)
                            @php 
                                $esMision = ($r->context_type === 'mission');
                                $tipoClase = $esMision ? 'review-mission' : 'review-market';
                                $iconoContexto = $esMision ? '🎯' : '🏪';
                            @endphp
                            <div class="review-item-card {{ $tipoClase }}">
                                <div class="flex-between" style="align-items: flex-start;">
                                    <div>
                                        <div class="font-13 font-bold text-white-pure mb-5px">{{ $iconoContexto }} {{ $r->context_title }}</div>
                                        <div class="font-11 text-neutral-neon">{{ __('messages.lbl_reviewed_by') }} <strong class="text-blue-neon font-bold">{{ $r->reviewer_name }}</strong> • {{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}</div>
                                    </div>
                                    <div class="text-right">
                                        <div class="tx-amount-value text-green-neon font-13 font-bold">+{{ number_format($r->context_fc ?? 0, 0) }} <small class="font-11 font-mono">FC</small></div>
                                        <div class="text-warning-node font-11 mt-5px">{!! str_repeat('★', floor($r->rating)) !!}</div>
                                    </div>
                                </div>
                                @if(!empty($r->endorsed_skills))
                                    <div class="skills-chips-matrix mt-10 mb-5px" style="gap: 6px !important;">
                                        @foreach(explode(',', $r->endorsed_skills) as $skill_chunk)
                                            @php $parts = explode('|', $skill_chunk); @endphp
                                            <span class="endorsement-chip {{ ($parts[1] ?? 'hard') === 'hard' ? 'hard' : 'soft' }} font-11" style="padding: 2px 8px !important; font-size: 9.5px !important; margin: 0 !important;">✓ {{ trim($parts[0] ?? 'Habilidad') }}</span>
                                        @endforeach
                                    </div>
                                @endif
                                @if(!empty($r->comment))
                                    <p class="m-0 review-comment-box font-12 text-neutral-muted">"{{ $r->comment }}"</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
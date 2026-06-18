@extends('layouts.app')

@section('title', $user->name . ' | ' . __('messages.app_subtitle'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lab.css') }}?v=1.0">
    <link rel="stylesheet" href="{{ asset('css/creator.css') }}?v=1.3">
    
    <style>
        
    </style>
@endpush

@section('content')
<div class="profile-container-v2 px-4">
    
    {{-- 1. CABECERA PÚBLICA (NAVBAR) --}}
    <nav class="public-navbar">
        <a href="{{ route('home') }}" class="public-logo-wrapper">
            <img src="{{ asset('images/logo-icon.webp') }}" alt="FabCoins">
            <h2 class="public-logo-text">Fab<span>Coins</span></h2>
        </a>
        
        <div class="public-nav-actions">
            <div class="lang-pill">
                <a href="?{{ http_build_query(array_merge($_GET, ['lang' => 'es'])) }}" class="{{ app()->getLocale() == 'es' ? 'lang-active' : 'lang-muted' }}">🇪🇸</a>
                <a href="?{{ http_build_query(array_merge($_GET, ['lang' => 'en'])) }}" class="{{ app()->getLocale() == 'en' ? 'lang-active' : 'lang-muted' }}">🇺🇸</a>
            </div>

            @auth
                <a href="{{ route('dashboard') }}" class="btn-premium btn-blue-hub m-0 btn-nav-action">
                    ⚙️ {{ __('messages.btn_dashboard') }}
                </a>
            @else
                <a href="{{ route('login', ['tab' => 'register']) }}" class="btn-premium m-0 btn-nav-action btn-success-nav">
                    🚀 {{ __('messages.btn_join_network') }}
                </a>
            @endauth
        </div>
    </nav>

    {{-- CÁLCULO DE MÉTRICAS RÁPIDAS --}}
    @php
        $totalResenas = isset($historialUnificado) ? count($historialUnificado) : 0;
        $totalMisiones = isset($historialUnificado) ? collect($historialUnificado)->where('context_type', 'mission')->count() : 0;
        
        // Fallback robusto del Avatar
        $avatarFinal = !empty($user->avatar_url) ? $user->avatar_url : 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=131722&color=3498db&size=200&font-size=0.4&bold=true';
    @endphp

    {{-- 2. BLOQUE SUPERIOR (IDENTIDAD Y BIOGRAFÍA EQUILIBRADOS) --}}
    <div class="grid-top-identity">
        
        {{-- Columna Izquierda: Pasaporte del Maker --}}
        <div class="premium-glass-card m-0" style="text-align: center; padding: 30px 20px; display: flex; flex-direction: column; justify-content: center;">
            <div class="avatar-showcase">
                <img src="{{ $avatarFinal }}" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=131722&color=3498db&size=200'" alt="Avatar">
            </div>
            
            <h1 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 26px; color: #fff; margin: 0 0 6px 0; line-height: 1.2;">{{ $user->name }}</h1>
            
            <div style="margin-bottom: 10px;">
                <span class="badge-role badge-creator" style="text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px;">⚙️ {{ $user->role }}</span>
            </div>

            @if(!empty($user->city) || !empty($user->country))
                <div style="color: #3498db; font-size: 13px; font-weight: 500; display: flex; align-items: center; justify-content: center; gap: 6px; margin-bottom: 5px;">
                    📍 <span style="font-size: 12px; color: #a0aec0;">{{ implode(', ', array_filter([$user->city, $user->country])) }}</span>
                </div>
            @endif

            {{-- Motor de Reclutamiento Industrial (Solo para Labs autenticados) --}}
            @if(auth()->check() && auth()->user()->role === 'lab' && auth()->user()->id !== $user->id)
                <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed rgba(255,255,255,0.05);">
                    @if(isset($misMisionesAbiertas) && $misMisionesAbiertas->isEmpty())
                        <p class="text-danger-neon m-0" style="font-size: 11px;">No tienes misiones con cupos libres.</p>
                    @elseif(isset($misMisionesAbiertas))
                        <form action="{{ route('public.profile.invite', ['slugOrId' => $user->slug ?: $user->id]) }}" method="POST" class="m-0 flex-col-gap-10">
                            @csrf
                            <input type="hidden" name="creator_id" value="{{ $user->id }}">
                            <select name="mission_id" class="premium-select" style="height: 34px; font-size: 11px; padding: 0 10px;" required>
                                <option value="">-- Selecciona misión --</option>
                                @foreach($misMisionesAbiertas as $ma)
                                    <option value="{{ $ma->id }}">{{ $ma->title }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-premium btn-blue-hub m-0 w-100" style="height: 34px; font-size: 11px;">🎯 {{ __('messages.btn_invite_mission') }}</button>
                        </form>
                    @endif
                </div>
            @endif

            <div class="stats-row">
                <div class="stat-box">
                    <div class="stat-value">{{ $totalMisiones }}</div>
                    <div class="stat-label">{{ __('messages.lbl_missions_count') }}</div>
                </div>
                <div class="stat-box">
                    <div class="stat-value">{{ $totalResenas }}</div>
                    <div class="stat-label">{{ __('messages.lbl_reviews_count') }}</div>
                </div>
            </div>
        </div>

        {{-- Columna Derecha: Biografía + Ajuste Magnético de Redes --}}
        <div class="premium-glass-card m-0" style="display: flex; flex-direction: column; justify-content: space-between; padding: 30px;">
            <div>
                <label class="premium-label" style="font-size: 11px; margin-bottom: 12px; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('messages.title_prof_bio') }}</label>
                <p class="text-neutral-muted m-0" style="font-size: 14px; line-height: 1.6; color: #cbd5e0;">
                    {{ $user->bio ?: __('messages.empty_bio') }}
                </p>
            </div>

            {{-- REDES ESTILIZADAS PREMIUM TIPO TAG --}}
            @if(!empty($user->fab_academy_url) || !empty($user->instagram_url) || !empty($user->social_linkedin) || !empty($user->social_github) || !empty($user->social_portfolio))
                <div class="social-grid-wrapper">
                    @if(!empty($user->fab_academy_url))
                        <a href="{{ $user->fab_academy_url }}" target="_blank" class="social-tag-link social-tag-fab">
                            🎓 Fab Academy
                        </a>
                    @endif

                    @if(!empty($user->social_linkedin))
                        <a href="{{ $user->social_linkedin }}" target="_blank" class="social-tag-link">
                            💼 LinkedIn
                        </a>
                    @endif

                    @if(!empty($user->social_portfolio))
                        <a href="{{ $user->social_portfolio }}" target="_blank" class="social-tag-link">
                            🌐 Portafolio
                        </a>
                    @endif

                    @if(!empty($user->social_github))
                        <a href="{{ $user->social_github }}" target="_blank" class="social-tag-link">
                            🐙 GitHub
                        </a>
                    @endif

                    @if(!empty($user->instagram_url))
                        <a href="{{ $user->instagram_url }}" target="_blank" class="social-tag-link">
                            📸 Instagram
                        </a>
                    @endif
                </div>
            @endif
        </div>

    </div>

    {{-- 3. BLOQUE INFERIOR (HABILIDADES E HISTORIAL) --}}
    <div class="grid-bottom-data">
        
        {{-- Columna Izquierda: Habilidades --}}
        <div class="premium-glass-card m-0">
            <div class="premium-glass-card-header">
                <h3 class="premium-glass-card-title m-0">🛠️ {{ __('messages.title_validated_skills') }}</h3>
            </div>
            
            @if(isset($misHabilidades) && $misHabilidades->isEmpty())
                <p class="text-neutral-muted m-0" style="font-size: 13px; font-style: italic;">{{ __('messages.empty_skills') }}</p>
            @elseif(isset($misHabilidades))
                <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                    @foreach($misHabilidades as $sk)
                        @php 
                            $esHard = ($sk->type === 'hard');
                            $color = $esHard ? '#3498db' : '#1abc9c';
                            $bg = $esHard ? 'rgba(52, 152, 219, 0.06)' : 'rgba(26, 188, 156, 0.06)';
                            $border = $esHard ? 'rgba(52, 152, 219, 0.2)' : 'rgba(26, 188, 156, 0.2)';
                        @endphp
                        <div style="background: {{ $bg }}; border: 1px solid {{ $border }}; padding: 6px 12px; border-radius: 6px; display: flex; align-items: center; gap: 8px;">
                            <span style="font-size: 12px; font-weight: 600; color: #fff;">{{ $sk->name }}</span>
                            <span style="background: {{ $color }}; color: #000; font-size: 10px; font-weight: 800; padding: 1px 5px; border-radius: 4px;">
                                {{ $sk->endorsements_count }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Columna Derecha: Historial Unificado --}}
        <div class="premium-glass-card m-0">
            <div class="premium-glass-card-header">
                <h3 class="premium-glass-card-title m-0">🚀 {{ __('messages.title_portfolio_reviews') }}</h3>
            </div>

            @if(empty($historialUnificado) || count($historialUnificado) == 0)
                <div style="background: #131722; border-radius: 8px; padding: 40px; text-align: center; border: 1px dashed rgba(255,255,255,0.05);">
                    <p class="text-neutral-muted m-0" style="font-size: 13px;">{{ __('messages.empty_reviews') }}</p>
                </div>
            @else
                <div style="display: flex; flex-direction: column; gap: 15px;">
                    @foreach($historialUnificado as $r)
                        @php 
                            $esMision = ($r->context_type === 'mission');
                            $tipoClase = $esMision ? 'review-mission' : 'review-market';
                            $tituloContexto = $esMision ? ($r->mission_title ?? 'Misión Industrial') : ($r->asset_name ?? 'Servicio de Catálogo');
                            $iconoContexto = $esMision ? '🎯' : '🏪';
                        @endphp
                        
                        <div class="review-item-card {{ $tipoClase }}">
                            <div style="display: flex; justify-content: space-between; align-items: start; gap: 10px;">
                                <div>
                                    <div style="font-weight: 700; font-size: 14px; color: #fff; margin-bottom: 3px;">
                                        {{ $iconoContexto }} {{ $tituloContexto }}
                                    </div>
                                    <div style="font-size: 11px; color: #7f8c8d;">
                                        {{ __('messages.lbl_reviewed_by') }} <strong style="color: #3498db;">{{ $r->reviewer_name }}</strong> • {{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}
                                    </div>
                                </div>
                                <div style="text-align: right; shrink-0: 0;">
                                    <div style="font-size: 14px; font-family: 'Rajdhani', sans-serif; font-weight: 800; color: #2ecc71; margin-bottom: 2px;">
                                        +{{ number_format($r->context_fc ?? 0, 0) }} FC
                                    </div>
                                    <div style="font-size: 11px; color: #f1c40f; letter-spacing: 1px;">
                                        {!! str_repeat('★', floor($r->rating)) !!}{!! str_repeat('☆', 5 - floor($r->rating)) !!}
                                    </div>
                                </div>
                            </div>

                            @if(!empty($r->endorsed_skills))
                                <div style="display: flex; flex-wrap: wrap; gap: 5px; margin: 10px 0 5px 0;">
                                    @foreach(explode(',', $r->endorsed_skills) as $skill_chunk)
                                        @php
                                            $parts = explode('|', $skill_chunk);
                                            $skill_name = $parts[0] ?? 'Habilidad';
                                            $skill_type = $parts[1] ?? 'hard';
                                            $border_color = ($skill_type === 'hard') ? 'rgba(52, 152, 219, 0.3)' : 'rgba(243, 156, 18, 0.3)';
                                            $text_color   = ($skill_type === 'hard') ? '#3498db' : '#f39c12';
                                        @endphp
                                        <span style="border: 1px solid {{ $border_color }}; color: {{ $text_color }}; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: 700; background: rgba(0,0,0,0.15);">
                                            ✓ {{ trim($skill_name) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($r->comment))
                                <p class="m-0 review-comment-box">
                                    "{{ $r->comment }}"
                                </p>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>
@endsection
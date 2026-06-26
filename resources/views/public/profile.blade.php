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

    {{-- 2. BLOQUE SUPERIOR DE IDENTIDAD (MATRIZ REUTILIZADA NATIVA) --}}
    <div class="grid-top-identity">
        
        {{-- Pasaporte Maker (Columna Izquierda) --}}
        <div class="premium-glass-card m-0 text-center">
            <div class="avatar-showcase">
                <img src="{{ $avatarFinal }}" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=131722&color=3498db&size=200'" alt="Avatar">
            </div>
            
            <h1 class="font-24 font-bold text-white-pure m-0 mb-5px" style="font-family: 'Rajdhani', sans-serif;">{{ $user->name }}</h1>
            
            <div class="mb-10">
                <span class="badge-role-fintech badge-semantic badge-orange font-11">
                    {{ __('messages.badge_creator_spec') }}
                </span>
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

            {{-- Nodo Reclutador (Solo visible para Laboratorios Externos) --}}
            @if(auth()->check() && auth()->user()->role === 'lab' && auth()->user()->id !== $user->id)
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

            {{-- Botones de Redes Sociales con Clases Nativas del Core --}}
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

    {{-- 3. BLOQUE INFERIOR (MATRIZ DE HABILIDADES & COMPENSACIONES GENERALES) --}}
    <div class="grid-bottom-data mt-25">
        
        {{-- Módulo de Habilidades (Chips con Borde e Idioma Inteligente) --}}
        <div class="premium-glass-card m-0">
            <div class="premium-glass-card-header">
                <h3 class="premium-glass-card-title m-0">🛠️ {{ __('messages.title_validated_skills') }}</h3>
            </div>
            
            @if(isset($misHabilidades) && $misHabilidades->isEmpty())
                <p class="text-neutral-muted m-0 font-italic font-11">{{ __('messages.empty_skills') }}</p>
            @elseif(isset($misHabilidades))
                @php 
                    $locale = app()->getLocale(); 
                    $tecnicas = $misHabilidades->where('type', 'hard');
                    $blandas = $misHabilidades->where('type', 'soft');
                @endphp

                {{-- Habilidades Técnicas (Gama Cian) --}}
                <label class="premium-label font-11 text-blue-neon font-bold mt-10 mb-10">{{ __('messages.lbl_hard_skills') }}:</label>
                <div class="skills-chips-matrix mb-20">
                    @foreach($tecnicas as $sk)
                        <div class="endorsement-chip hard m-0 font-12">
                            <span>{{ $locale === 'en' ? $sk->name_en : $sk->name_es }}</span>
                            {{-- 🎯 REGLA CONTABLE: Pinta el check verde solo si tiene validaciones hechas por laboratorios --}}
                            @if(($sk->endorsements_count ?? 0) > 0)
                                <span class="endorsement-count validated font-11">✓ {{ $sk->endorsements_count }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Habilidades Blandas (Gama Oro) --}}
                <label class="premium-label font-11 text-warning-neon font-bold mt-10 mb-10">{{ __('messages.lbl_soft_skills') }}:</label>
                <div class="skills-chips-matrix">
                    @foreach($blandas as $sk)
                        <div class="endorsement-chip soft m-0 font-12">
                            <span>{{ $locale === 'en' ? $sk->name_en : $sk->name_es }}</span>
                            {{-- 🎯 REGLA CONTABLE --}}
                            @if(($sk->endorsements_count ?? 0) > 0)
                                <span class="endorsement-count validated font-11">✓ {{ $sk->endorsements_count }}</span>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Libro Contable de Calificaciones y Portafolio --}}
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
                                    <div class="font-13 font-bold text-white-pure mb-5px">
                                        {{ $iconoContexto }} {{ $r->context_title }}
                                    </div>
                                    <div class="font-11 text-neutral-neon">
                                        {{ __('messages.lbl_reviewed_by') }} <strong class="text-blue-neon font-bold">{{ $r->reviewer_name }}</strong> • {{ \Carbon\Carbon::parse($r->created_at)->format('d M Y') }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="tx-amount-value text-green-neon font-13 font-bold">
                                        +{{ number_format($r->context_fc ?? 0, 0) }} <small class="font-11 font-mono">FC</small>
                                    </div>
                                    <div class="text-warning-node font-11 mt-5px">
                                        {!! str_repeat('★', floor($r->rating)) !!}{!! str_repeat('☆', 5 - floor($r->rating)) !!}
                                    </div>
                                </div>
                            </div>

                            @if(!empty($r->endorsed_skills))
                                <div class="skills-chips-matrix mt-10 mb-5px" style="gap: 6px !important;">
                                    @foreach(explode(',', $r->endorsed_skills) as $skill_chunk)
                                        @php
                                            $parts = explode('|', $skill_chunk);
                                            $skill_name = $parts[0] ?? 'Habilidad';
                                            $skill_type = $parts[1] ?? 'hard';
                                            $badge_type = ($skill_type === 'hard') ? 'hard' : 'soft';
                                        @endphp
                                        <span class="endorsement-chip {{ $badge_type }} font-11" style="padding: 2px 8px !important; font-size: 9.5px !important; margin: 0 !important;">
                                            ✓ {{ trim($skill_name) }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif

                            @if(!empty($r->comment))
                                <p class="m-0 review-comment-box font-12 text-neutral-muted">
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
@extends('layouts.app')

@section('title', $user->name . ' | ' . __('messages.app_subtitle'))

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/lab.css') }}?v=1.0">
    <link rel="stylesheet" href="{{ asset('css/creator.css') }}?v=1.1">
    
    <style>
        .profile-header-grid { display: grid; grid-template-columns: 200px 1fr; gap: 30px; align-items: center; }
        .profile-body-grid { display: grid; grid-template-columns: 1fr 1.8fr; gap: 25px; align-items: start; }
        .review-item-card { background: #131722; padding: 18px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.02); border-left-width: 3px; }
        .review-mission { border-left-color: #2ecc71; }
        .review-market { border-left-color: #3498db; }
        .review-comment-box { font-size: 13px; font-style: italic; color: #bdc3c7; background: rgba(0,0,0,0.2); padding: 10px 15px; border-radius: 4px; margin-top: 10px; }
        
        /* Estilos Premium para Redes */
        .btn-social-highlight {
            background: #3498db; color: #ffffff !important; padding: 12px; border-radius: 6px; 
            text-decoration: none; display: flex; align-items: center; justify-content: center;
            gap: 8px; font-weight: 700; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.2); transition: all 0.2s ease;
        }
        .btn-social-highlight:hover { background: #2980b9; transform: translateY(-2px); }
        
        .btn-social-regular {
            background: rgba(255,255,255,0.03); color: #ecf0f1 !important; padding: 10px 12px; 
            border-radius: 6px; text-decoration: none; display: flex; align-items: center; gap: 10px; 
            font-size: 13px; border: 1px solid rgba(255,255,255,0.05); transition: all 0.2s ease;
        }
        .btn-social-regular:hover { background: rgba(255,255,255,0.08); border-color: rgba(255,255,255,0.15); }
    </style>
@endpush

@section('content')
<div class="container" style="max-width: 1100px;">
    
    {{-- BARRA DE ACCIÓN SUPERIOR --}}
    <div class="header mb-20" style="display: flex; justify-content: space-between; align-items: center;">
        {{-- Botón de Volver --}}
        <a href="{{ route('dashboard') }}" class="btn-back">⬅ {{ __('messages.btn_back') ?? 'Volver' }}</a>
        
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="display: flex; gap: 10px; background: rgba(0,0,0,0.3); padding: 5px 15px; border-radius: 20px;">
                <a href="?{{ http_build_query(array_merge($_GET, ['lang' => 'es'])) }}" style="text-decoration: none; font-size: 20px; opacity: {{ app()->getLocale() == 'es' ? '1' : '0.4' }}">🇪🇸</a>
                <a href="?{{ http_build_query(array_merge($_GET, ['lang' => 'en'])) }}" style="text-decoration: none; font-size: 20px; opacity: {{ app()->getLocale() == 'en' ? '1' : '0.4' }}">🇺🇸</a>
            </div>
        </div>
    </div>

    {{-- CABECERA PANORÁMICA DEL PERFIL --}}
    <div class="premium-glass-card mb-25">
        <div class="profile-header-grid">
            <div style="text-align: center;">
                <img src="{{ $user->avatar_url ?: asset('images/default-avatar.png') }}" alt="Avatar" style="width: 130px; height: 130px; border-radius: 50%; border: 3px solid #3498db; object-fit: cover; box-shadow: 0 0 20px rgba(52,152,219,0.3);">
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 15px;">
                    <h1 class="m-0" style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 32px; color: #fff;">{{ $user->name }}</h1>
                    <span class="badge-role badge-creator" style="text-transform: uppercase; font-size: 11px;">⚙️ {{ $user->role }}</span>
                </div>
                
                {{-- UBICACIÓN DINÁMICA (Solo si tiene datos) --}}
                @if(!empty($user->city) || !empty($user->country))
                    <div style="margin-top: 5px; color: #3498db; font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 6px;">
                        🌐 <span>{{ implode(', ', array_filter([$user->city, $user->country])) }}</span>
                    </div>
                @endif

                <p class="text-neutral-muted mt-10 mb-0" style="font-size: 14px; max-width: 700px; line-height: 1.5; color: #bdc3c7;">
                    {{ $user->bio ?: 'Sin biografía profesional registrada.' }}
                </p>
            </div>
        </div>
    </div>

    {{-- CUERPO PRINCIPAL --}}
    <div class="profile-body-grid">
        
        {{-- COLUMNA IZQUIERDA: REPUTACIÓN Y ENLACES --}}
        <div class="flex-col-gap-25" style="display: flex; flex-direction: column; gap: 25px;">
            
            {{-- Bloque de Reputación Colectiva --}}
            <div class="premium-glass-card m-0">
                <div class="premium-glass-card-header">
                    <h3 class="premium-glass-card-title m-0">🏆 {{ __('messages.reputation') ?? 'Reputación de Red' }}</h3>
                </div>
                <div style="text-align: center; padding: 10px 0;">
                    <div style="font-size: 48px; font-weight: 800; color: #2ecc71; font-family: 'Rajdhani', sans-serif; line-height: 1;">
                        {{ number_format($user->reputation_score, 1) }}
                    </div>
                    <div style="font-size: 11px; text-transform: uppercase; color: #7f8c8d; margin-top: 5px; font-weight: 600; letter-spacing: 0.5px;">
                        Puntaje de Confianza Global
                    </div>
                </div>
            </div>

            {{-- BLOQUE DINÁMICO DE REDES SOCIALES --}}
            @if(!empty($user->fab_academy_url) || !empty($user->instagram_url) || !empty($user->social_linkedin) || !empty($user->social_github) || !empty($user->social_portfolio))
                <div class="premium-glass-card m-0">
                    <div class="premium-glass-card-header">
                        <h3 class="premium-glass-card-title m-0">🔗 {{ __('Enlaces & Portafolios') }}</h3>
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 10px;">
                        
                        @if(!empty($user->fab_academy_url))
                            <a href="{{ $user->fab_academy_url }}" target="_blank" class="btn-social-highlight">
                                🎓 Fab Academy Profile
                            </a>
                        @endif

                        @if(!empty($user->instagram_url))
                            <a href="{{ $user->instagram_url }}" target="_blank" class="btn-social-regular">
                                📸 Instagram
                            </a>
                        @endif

                        @if(!empty($user->social_linkedin))
                            <a href="{{ $user->social_linkedin }}" target="_blank" class="btn-social-regular">
                                💼 LinkedIn Profesional
                            </a>
                        @endif

                        @if(!empty($user->social_github))
                            <a href="{{ $user->social_github }}" target="_blank" class="btn-social-regular">
                                🐙 GitHub Repository
                            </a>
                        @endif

                        @if(!empty($user->social_portfolio))
                            <a href="{{ $user->social_portfolio }}" target="_blank" class="btn-social-regular">
                                🌐 Portafolio / Web Personal
                            </a>
                        @endif

                    </div>
                </div>
            @endif

            {{-- Motor Quirúrgico de Reclutamiento Industrial --}}
            @if(auth()->check() && auth()->user()->role === 'lab' && auth()->user()->id !== $user->id)
                <div class="premium-glass-card m-0" style="border: 1px solid rgba(52, 152, 219, 0.2); background: rgba(52, 152, 219, 0.02);">
                    <div class="premium-glass-card-header">
                        <h3 class="premium-glass-card-title m-0" style="color: #3498db;">⚡ Reclutar Talento</h3>
                    </div>
                    
                    @if(isset($misMisionesAbiertas) && $misMisionesAbiertas->isEmpty())
                        <p class="text-danger-neon text-center mb-20" style="font-size: 13px;">No tienes misiones abiertas con cupos disponibles.</p>
                        <button type="button" class="btn-premium m-0 w-100" style="background: #34495e;" onclick="window.location.href='{{ route('lab.dashboard') }}'">Ir a crear una misión</button>
                    @elseif(isset($misMisionesAbiertas))
                        <p class="text-neutral-muted mb-15" style="font-size: 12px;">Selecciona a qué misión deseas invitar a <strong>{{ $user->name }}</strong>:</p>
                        
                        <form action="{{ route('public.profile.invite', ['slugOrId' => $user->slug ?: $user->id]) }}" method="POST" class="m-0">
                            @csrf
                            <input type="hidden" name="creator_id" value="{{ $user->id }}">
                            <select name="mission_id" class="premium-select mb-20" required>
                                <option value="">-- Selecciona una misión --</option>
                                @foreach($misMisionesAbiertas as $ma)
                                    <option value="{{ $ma->id }}">{{ $ma->title }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-premium btn-blue-hub m-0 w-100">📨 Enviar Invitación</button>
                        </form>
                    @endif
                </div>
            @endif

        </div>

        {{-- COLUMNA DERECHA: HISTORIAL DE EVALUACIONES Y SKILLS --}}
        <div class="flex-col-gap-25" style="display: flex; flex-direction: column; gap: 25px;">
            
            {{-- Catálogo Consolidado de Habilidades Endosadas --}}
            <div class="premium-glass-card m-0">
                <div class="premium-glass-card-header">
                    <h3 class="premium-glass-card-title m-0">🛠️ Especializaciones Certificadas</h3>
                </div>
                
                @if(isset($misHabilidades) && $misHabilidades->isEmpty())
                    <p class="text-neutral-muted m-0" style="font-size: 13px; font-style: italic;">Este creador aún no ha registrado habilidades certificadas en sus proyectos.</p>
                @elseif(isset($misHabilidades))
                    <div style="display: flex; flex-wrap: wrap; gap: 10px;">
                        @foreach($misHabilidades as $sk)
                            @php 
                                $esHard = ($sk->type === 'hard');
                                $color = $esHard ? '#3498db' : '#f39c12';
                                $bg = $esHard ? 'rgba(52, 152, 219, 0.1)' : 'rgba(243, 156, 18, 0.1)';
                            @endphp
                            <div style="background: {{ $bg }}; border: 1px solid rgba(255,255,255,0.04); padding: 8px 14px; border-radius: 6px; display: flex; align-items: center; gap: 10px;">
                                <span style="font-size: 13px; font-weight: 600; color: #fff;">{{ $sk->name }}</span>
                                <span style="background: {{ $color }}; color: #000; font-size: 10px; font-weight: 700; padding: 1px 5px; border-radius: 4px;">
                                    👍 {{ $sk->endorsements_count }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Bitácora Unificada de Reputación (Reseñas) --}}
            <div class="premium-glass-card m-0">
                <div class="premium-glass-card-header">
                    <h3 class="premium-glass-card-title m-0">📝 Historial Operativo</h3>
                </div>

                @if(empty($historialUnificado))
                    <p class="text-neutral-muted m-0" style="font-size: 13px; font-style: italic;">Este expediente no registra operaciones o calificaciones históricas en la red.</p>
                @else
                    <div style="display: flex; flex-direction: column; gap: 15px;">
                        @foreach($historialUnificado as $r)
                            @php 
                                $esMision = ($r->context_type === 'mission');
                                $tipoClase = $esMision ? 'review-mission' : 'review-market';
                                $tituloContexto = $esMision ? ($r->mission_title ?? 'Misión Industrial') : ($r->asset_name ?? 'Servicio de Catálogo');
                                $iconoContexto = $esMision ? '🚀' : '🏪';
                            @endphp
                            
                            <div class="review-item-card {{ $tipoClase }}">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 8px;">
                                    <div>
                                        <div style="font-weight: 700; font-size: 14px; color: #fff;">
                                            {{ $iconoContexto }} {{ $tituloContexto }}
                                        </div>
                                        <div style="font-size: 11px; color: #7f8c8d; margin-top: 2px;">
                                            Evaluado por: <strong>{{ $r->reviewer_name }}</strong> • {{ \Carbon\Carbon::parse($r->created_at)->format('d M, Y') }}
                                        </div>
                                    </div>
                                    <div style="font-size: 14px; font-weight: 700; color: #2ecc71; background: rgba(46,204,113,0.1); padding: 2px 8px; border-radius: 4px;">
                                        ⭐ {{ number_format($r->rating, 1) }}
                                    </div>
                                </div>

                                @if(!empty($r->endorsed_skills))
                                    <div style="display: flex; flex-wrap: wrap; gap: 5px; margin-bottom: 10px;">
                                        @foreach(explode(',', $r->endorsed_skills) as $skill_chunk)
                                            @php
                                                $parts = explode('|', $skill_chunk);
                                                $skill_name = $parts[0] ?? 'Habilidad';
                                                $skill_type = $parts[1] ?? 'hard';
                                                
                                                $bg_color     = ($skill_type === 'hard') ? 'rgba(52, 152, 219, 0.12)' : 'rgba(243, 156, 18, 0.12)';
                                                $text_color   = ($skill_type === 'hard') ? '#3498db' : '#f39c12';
                                                $border_color = ($skill_type === 'hard') ? 'rgba(52, 152, 219, 0.3)' : 'rgba(243, 156, 18, 0.3)';
                                            @endphp
                                            <span style="background: {{ $bg_color }}; color: {{ $text_color }}; border: 1px solid {{ $border_color }}; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold;">
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
</div>
@endsection
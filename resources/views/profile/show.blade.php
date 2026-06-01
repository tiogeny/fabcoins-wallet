@extends('layouts.app')

@section('title', htmlspecialchars($user->name) . ' | Perfil FabCoins')

@section('content')
<div class="container" style="max-width: 1100px; margin-top: 20px; margin-bottom: 30px;">
    
    <div class="header mb-20" style="display: flex; justify-content: space-between; align-items: center;">
        @if(auth()->check())
            <a href="{{ route(auth()->user()->role . '.dashboard') }}" class="btn-back">⬅ {{ __('messages.btn_dashboard') ?? 'Dashboard' }}</a>
        @else
            <a href="{{ route('login') }}" class="btn-back">⬅ {{ __('messages.btn_login') ?? 'Ingresar' }}</a>
        @endif
        
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="display: flex; gap: 10px; background: rgba(0,0,0,0.3); padding: 5px 15px; border-radius: 20px;">
                <a href="?lang=es" style="text-decoration: none; font-size: 20px; opacity: {{ app()->getLocale() == 'es' ? '1' : '0.4' }};">🇪🇸</a>
                <a href="?lang=en" style="text-decoration: none; font-size: 20px; opacity: {{ app()->getLocale() == 'en' ? '1' : '0.4' }};">🇺🇸</a>
            </div>
            <span class="font-12 text-muted">{{ __('messages.lbl_member_since') ?? 'Miembro desde' }} {{ date('Y', strtotime($user->created_at)) }}</span>
        </div>
    </div>

    @if(session('msg') == 'invite_ok')
        <div class="alert alert-success mb-20" style="background: rgba(46, 204, 113, 0.2); color: #2ecc71; border: 1px solid #2ecc71; padding: 15px; border-radius: 8px;">
            ✅ ¡Invitación enviada exitosamente al correo del Maker!
        </div>
    @endif

    <div class="card mb-20" style="padding: 40px;">
        <div class="profile-header-flex">
            
            <div class="profile-identity-col">
                <img src="{{ $user->avatar_url ?: 'https://via.placeholder.com/100' }}" alt="Avatar" class="avatar" style="border: 3px solid var(--theme-color);">
                <h1 class="m-0 font-24">{{ $user->name }}</h1>
                
                <span class="badge mt-10" style="background: var(--theme-color); color: #1a1a1a; font-weight:bold; text-transform: uppercase; font-size: 10px; padding: 3px 8px;">
                    {{ $user->role == 'lab' ? (__('messages.badge_official_lab') ?? 'LAB OFICIAL') : (__('messages.badge_maker_spec') ?? 'MAKER ESPECIALISTA') }}
                </span>
                <span class="font-13 text-muted mt-10">📍 {{ $user->address ?: (__('messages.lbl_loc_undefined') ?? 'Ubicación no definida') }}</span>

                @if($miRol === 'lab' && $miId !== $user->id && $user->role === 'maker')
                    <div style="margin-top: 20px;">
                        <button onclick="document.getElementById('modal-invitar-publico').style.display='flex'" class="btn-apply" style="background: var(--c-blue); color: white; border: none; padding: 10px 25px; border-radius: 20px; font-weight: bold; cursor: pointer; display: inline-block;">
                            🎯 {{ __('messages.btn_publish_miss') ?? 'Invitar a una Misión' }}
                        </button>
                    </div>

                    <div id="modal-invitar-publico" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; text-align: left;">
                        <div class="card" style="width: 100%; max-width: 420px; position: relative;">
                            <button onclick="document.getElementById('modal-invitar-publico').style.display='none'" style="position: absolute; top: 10px; right: 15px; background: none; border: none; color: white; font-size: 20px; cursor: pointer;">✕</button>
                            
                            <h3 style="margin-top: 0; color: var(--c-blue); border-bottom: 1px solid #34495e; padding-bottom: 10px;">🎯 Invitar Talento</h3>
                            
                            @if($misMisionesAbiertas->isEmpty())
                                <p style="color: #e74c3c; font-size: 13px;">No tienes misiones abiertas con cupos disponibles.</p>
                            @else
                                <p style="font-size: 13px; color: #bdc3c7;">Selecciona a qué misión deseas invitar a <strong>{{ $user->name }}</strong>:</p>
                                <form action="{{ route('public.profile.invite', $user->slug ?: $user->id) }}" method="POST">
                                    @csrf
                                    <select name="mision_id" required style="width: 100%; padding: 12px; margin-bottom: 20px; background: #1a1a1a; border: 1px solid #34495e; color: white; border-radius: 6px; font-size: 13px;">
                                        <option value="">-- Selecciona una misión --</option>
                                        @foreach($misMisionesAbiertas as $ma)
                                            <option value="{{ $ma->id }}">{{ $ma->title }}</option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn-apply" style="background: var(--c-blue); width: 100%; font-size: 14px;">✉️ Enviar Invitación Oficial</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif
                
                <div class="profile-stats-mini">
                    <div class="stat-item">
                        <div class="stat-value text-blue">{{ count($historialUnificado) }}</div>
                        <div class="stat-label">{{ __('messages.lbl_reviews') ?? 'Reseñas' }}</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-value text-green">
                            {{ $user->role == 'maker' ? count(array_filter($historialUnificado, fn($h) => $h->context_type == 'mission')) : count($activosLab) }}
                        </div>
                        <div class="stat-label">
                            {{ $user->role == 'maker' ? (__('messages.lbl_missions') ?? 'Misiones') : (__('messages.lbl_assets') ?? 'Activos') }}
                        </div>
                    </div>
                </div>
            </div>

            <div class="profile-content-col">
                <div style="flex-grow: 1;">
                    <h3 class="text-muted font-11 font-bold text-uppercase mb-10" style="letter-spacing: 1px;">{{ __('messages.title_prof_bio') ?? 'Biografía Profesional' }}</h3>
                    <div class="font-16" style="line-height: 1.7; color: #ecf0f1; margin: 0;">
                        {!! $user->bio ?: (__('messages.empty_bio') ?? 'Este usuario aún no ha redactado su presentación profesional.') !!}
                    </div>
                </div>

                <div class="social-bar mt-20">
                    @if(!empty($user->social_fabacademy))
                        <a href="{{ $user->social_fabacademy }}" target="_blank" class="social-tag" style="background: var(--c-yellow); color: #1a1a1a; border-color: var(--c-yellow);">🎓 Fab Academy</a>
                    @endif
                    @if(!empty($user->social_linkedin))
                        <a href="{{ $user->social_linkedin }}" target="_blank" class="social-tag">🔗 LinkedIn</a>
                    @endif
                    @if(!empty($user->social_github))
                        <a href="{{ $user->social_github }}" target="_blank" class="social-tag">🐙 GitHub</a>
                    @endif
                    @if(!empty($user->social_portfolio))
                        <a href="{{ $user->social_portfolio }}" target="_blank" class="social-tag">🌐 Portafolio</a>
                    @endif
                    @if(!empty($user->social_instagram))
                        <a href="{{ $user->social_instagram }}" target="_blank" class="social-tag">📸 Instagram</a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 20px; align-items: start;">

        <div style="position: sticky; top: 20px; height: fit-content;">
            @if($user->role == 'maker')
                <div class="card mb-20">
                    <h2 class="font-16 mb-15">🛠️ {{ __('messages.title_validated_skills') ?? 'Habilidades Validadas' }}</h2>
                    
                    @php 
                        $hardSkills = array_filter($misHabilidades, fn($s) => $s->type == 'hard');
                        $softSkills = array_filter($misHabilidades, fn($s) => $s->type == 'soft');
                    @endphp

                    @if(empty($misHabilidades))
                        <p class="font-12 text-muted">{{ __('messages.empty_skills') ?? 'Sin habilidades registradas.' }}</p>
                    @else
                        @if(count($hardSkills) > 0)
                            <div class="font-11 text-blue font-bold mb-10 text-uppercase" style="letter-spacing: 0.5px;">{{ __('messages.lbl_hard_skills') ?? 'Habilidades Técnicas:' }}</div>
                            <div class="mb-15">
                                @foreach($hardSkills as $sk)
                                    <div class="endorsement-chip hard">
                                        {{ $sk->name }} 
                                        <span class="endorsement-count {{ $sk->endorsements_count > 0 ? 'validated' : '' }}">✓ {{ $sk->endorsements_count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if(count($softSkills) > 0)
                            <div class="font-11 text-orange font-bold mb-10 text-uppercase" style="letter-spacing: 0.5px;">{{ __('messages.lbl_soft_skills') ?? 'Habilidades Blandas:' }}</div>
                            <div>
                                @foreach($softSkills as $sk)
                                    <div class="endorsement-chip soft">
                                        {{ $sk->name }} 
                                        <span class="endorsement-count {{ $sk->endorsements_count > 0 ? 'validated' : '' }}">✓ {{ $sk->endorsements_count }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    @endif
                </div>
            @else
                <div class="card">
                    <h2 class="font-16 mb-15">⚙️ {{ __('messages.title_equipment_catalog') ?? 'Catálogo de Equipos' }}</h2>
                    @forelse($activosLab as $a)
                        <div class="flex-between mb-10 p-10" style="background: rgba(255,255,255,0.03); border-radius: 4px;">
                            <span class="font-13">{{ $a->custom_name }}</span>
                            <span class="text-blue font-12">{{ number_format($a->set_price_fc, 2) }} FC/h</span>
                        </div>
                    @empty
                        <p class="font-12 text-muted">{{ __('messages.empty_assets') ?? 'No hay equipos disponibles.' }}</p>
                    @endforelse
                </div>
            @endif
        </div>

        <div>
            <div class="card">
                <h2 class="font-16 mb-15">🚀 {{ __('messages.title_portfolio_reviews') ?? 'Portafolio y Reseñas' }}</h2>
                
                @forelse($historialUnificado as $r)
                    <div class="review-card" style="border-left: 3px solid {{ $r->context_type == 'mission' ? 'var(--c-green)' : 'var(--c-blue)' }}; background: rgba(255,255,255,0.02); padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                        
                        <div class="flex-between mb-5">
                            <span class="font-14 font-bold" style="color: #fff;">
                                {!! $r->context_type == 'mission' ? '🎯 ' : '🏭 ' !!}
                                {{ $r->context_title }}
                            </span>
                            @if($r->context_fc > 0)
                                <span class="font-13 font-bold text-green">+{{ number_format($r->context_fc, 0) }} FC</span>
                            @endif
                        </div>

                        @if(!empty($r->context_desc))
                            <div style="font-size: 12px; color: #bdc3c7; margin-bottom: 12px; line-height: 1.4; padding-left: 22px;">
                                {{ mb_strimwidth($r->context_desc, 0, 140, "...") }}
                            </div>
                        @endif

                        <div class="flex-between mb-10" style="border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 10px;">
                            <span class="font-11 text-muted">
                                {{ __('messages.lbl_reviewed_by') ?? 'Por:' }} 
                                <a href="{{ route('public.profile', $r->reviewer_slug ?: $r->reviewer_id) }}" style="color: var(--c-blue); text-decoration: none; font-weight: bold;">
                                    👤 {{ $r->reviewer_name }} ↗️
                                </a> 
                                • {{ date('d M Y', strtotime($r->created_at)) }}
                            </span>
                            <div style="color: #f1c40f; font-size: 12px;">
                                {!! str_repeat('⭐', $r->rating) !!}{!! str_repeat('☆', 5 - $r->rating) !!}
                            </div>
                        </div>

                        @if(!empty($r->specific_skills))
                            <div style="margin-bottom: 10px; display: flex; flex-wrap: wrap; gap: 5px; padding-left: 5px;">
                                @foreach(explode('|', $r->specific_skills) as $skillCombined)
                                    @php 
                                        $parts = explode(':', $skillCombined);
                                        $skillName = $parts[0] ?? '';
                                        $skillType = $parts[1] ?? 'hard';
                                        
                                        $bgColor     = ($skillType === 'hard') ? 'rgba(52, 152, 219, 0.12)' : 'rgba(243, 156, 18, 0.12)';
                                        $textColor   = ($skillType === 'hard') ? '#3498db' : '#f39c12';
                                        $borderColor = ($skillType === 'hard') ? 'rgba(52, 152, 219, 0.3)' : 'rgba(243, 156, 18, 0.3)';
                                    @endphp
                                    <span style="background: {{ $bgColor }}; color: {{ $textColor }}; border: 1px solid {{ $borderColor }}; padding: 2px 6px; border-radius: 4px; font-size: 9px; font-weight: bold;">
                                        ✓ {{ trim($skillName) }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        @if(!empty($r->comment))
                            <p style="font-size: 13px; font-style: italic; color: #e2e8f0; margin: 0; background: rgba(0,0,0,0.15); padding: 8px 12px; border-radius: 4px; border-left: 2px solid rgba(255,255,255,0.1);">
                                "{{ $r->comment }}"
                            </p>
                        @endif
                    </div>
                @empty
                    <p class="font-12 text-muted text-center">{{ __('messages.empty_reviews') ?? 'Este usuario aún no tiene historial de trabajos o reseñas.' }}</p>
                @endforelse
            </div>
        </div>

    </div>
</div>
@endsection
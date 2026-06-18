<div class="focus-glow-blue">

    {{-- 1. EXPLORADOR DE MISIONES (GRILLA) --}}
    <div class="premium-glass-card">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title m-0">🌍 {{ __('messages.title_explore_missions') }}</h3>
        </div>
        <p class="premium-glass-card-subtitle">{{ __('messages.desc_explore_missions') }}</p>

        <div class="creator-asset-grid mt-20">
            @forelse($misionesAbiertas as $m)
                <div class="creator-asset-card" style="border-top: 4px solid #e84393;">
                    
                    <div>
                        <div class="creator-asset-header">
                            <div class="flex-col-gap-4">
                                <a href="{{ route('public.profile', $m->lab_slug ?? $m->lab_id) }}" target="_blank" class="asset-lab-badge text-rosado-neon font-bold text-decoration-none">
                                    🏭 {{ $m->lab_name }} ↗️
                                </a>
                            </div>
                            <span class="price-tag td-amount-gold text-warning-neon">{{ number_format($m->reward_fc, 0) }} FC</span>
                        </div>
                        
                        @if($m->target_creator_id == auth()->id())
                            <span class="badge-ghost-warning mb-10 display-inline-block">🎯 {{ __('messages.badge_directed_mission') }}</span>
                        @endif
                        
                        <h3 class="asset-display-title asset-title-large mb-10">{{ $m->title }}</h3>
                        
                        <div class="font-rajdhani-15 text-blue-neon font-bold mb-8 font-size-11">👥 {{ $m->spots_filled }} / {{ $m->spots_total }} {{ __('messages.lbl_spots_status') }}</div>
                        <p class="text-neutral-muted mb-15 font-size-12 line-height-15">{{ $m->description }}</p>
                        <div class="text-rosado-neon font-bold mb-15 font-size-11">📅 {{ __('messages.th_deadline') }}: {{ date('d M Y', strtotime($m->deadline)) }}</div>
                    </div>
                    
                    <form action="{{ route('creator.apply_mission') }}" method="POST" class="form-reserve-integrated mt-10">
                        @csrf 
                        <input type="hidden" name="mission_id" value="{{ $m->id }}">
                        
                        <textarea name="message" rows="2" placeholder="{{ __('messages.ph_why_ideal_creator') }}" class="premium-textarea m-0 h-60" required></textarea>
                        
                        <button type="button" class="btn-premium btn-rosado-hub m-0" onclick="confirmarAccion(event, '¿Confirmas tu postulación a esta misión?', 'info', '#e84393')">
                            🚀 {{ __('messages.btn_send_application') }}
                        </button>
                    </form>
                </div>
            @empty
                <div class="empty-state-warning grid-col-span-full">
                    <p class="m-0 text-neutral-muted">{{ __('messages.empty_open_missions') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- 2. TABLA: MIS POSTULACIONES Y TRABAJOS --}}
    <div class="premium-glass-card">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title m-0">📋 {{ __('messages.title_my_apps_status') }}</h3>
        </div>
        <div class="table-container">
            <table class="premium-data-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.th_date') }}</th>
                        <th>{{ __('messages.th_lab') }}</th>
                        <th>{{ __('messages.th_mission') }}</th>
                        <th>{{ __('messages.th_reward') }}</th>
                        <th>{{ __('messages.th_deadline') }}</th>
                        <th>{{ __('messages.th_app_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($misPostulaciones->isEmpty())
                        <tr><td colspan="6" class="empty-state">{{ __('messages.empty_my_apps') }}</td></tr>
                    @else
                        @foreach($misPostulaciones as $p)
                            <tr>
                                <td class="td-date-dim">{{ date('d M Y', strtotime($p->created_at)) }}</td>
                                <td class="td-creator-name">
                                    <a href="{{ route('public.profile', $p->lab_slug ?? $p->lab_id) }}" target="_blank" class="text-blue-neon font-bold text-decoration-none">
                                        {{ $p->lab_name }} ↗️
                                    </a>
                                </td>
                                <td>
                                    <div class="text-white-pure font-bold">
                                        {{ $p->title }}
                                        
                                        {{-- Indicador de Invitación Directa / Misión Dirigida --}}
                                        @if(isset($p->target_creator_id) && $p->target_creator_id == auth()->id())
                                            <span class="badge-ghost-warning mt-5 display-inline-block">
                                                🎯 {{ __('messages.badge_directed_mission') ?? 'Invitación Directa' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="td-amount-gold">{{ number_format($p->reward_fc, 0) }} FC</td>
                                <td class="td-date-dim">
                                    @if($p->mission_status === 'completed')
                                        <span class="text-success-neon">✓ {{ __('messages.status_mission_finished') }}</span>
                                    @else
                                        📅 {{ date('d M Y', strtotime($p->deadline)) }}
                                    @endif
                                </td>
                                <td>
                                    @if($p->status === 'pending')
                                        <span class="badge-ghost-warning">⏳ {{ __('messages.status_waiting') }}</span>
                                    @elseif($p->status === 'invited')
                                        {{-- NUEVO: Botones para que el Creador acepte o rechace la invitación --}}
                                        <div style="display: flex; gap: 8px;">
                                            <form action="{{ route('creator.mission.accept_invite') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="mission_id" value="{{ $p->mission_id ?? $p->id }}">
                                                <button type="submit" class="badge-ghost-success" style="border: none; cursor: pointer;">
                                                    ✅ Aceptar
                                                </button>
                                            </form>
                                            <form action="{{ route('creator.mission.reject_invite') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="mission_id" value="{{ $p->mission_id ?? $p->id }}">
                                                <button type="submit" class="badge-ghost-danger" style="border: none; cursor: pointer;">
                                                    ❌ Rechazar
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($p->status === 'accepted')
                                        @if($p->mission_status === 'completed')
                                            <span class="badge-ghost-success">🎉 {{ __('messages.status_approved_consumed') }}</span>
                                        @else
                                            <span class="badge-ghost-success">✅ {{ __('messages.status_accepted_working') }}</span>
                                        @endif
                                    @elseif($p->status === 'rejected')
                                        <span class="badge-ghost-danger">❌ {{ __('messages.status_not_selected') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

</div>
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
                                <a href="{{ route('public.profile', $m->lab_slug ?? $m->lab_id) }}" target="_blank" class="asset-lab-badge text-pink-neon font-bold text-decoration-none">
                                    🏭 {{ $m->lab_name }} ↗️
                                </a>
                            </div>
                            <span class="price-tag td-amount-gold text-warning-neon">{{ number_format($m->reward_fc, 0) }} FC</span>
                        </div>
                        
                        @if($m->target_creator_id == auth()->id())
                            <span class="badge-ghost-warning mb-10 display-inline-block">🎯 {{ __('messages.badge_directed_mission') }}</span>
                        @endif
                        
                        <h4 class="font-14 font-bold text-white-pure mb-10">{{ $m->title }}</h4>
                        
                        <div class="font-rajdhani-15 text-blue-neon font-bold mb-8 font-11">👥 {{ $m->spots_filled }} / {{ $m->spots_total }} {{ __('messages.lbl_spots_status') }}</div>
                        <p class="text-neutral-muted mb-15 font-12 line-height-15">{{ $m->description }}</p>
                        <div class="text-pink-neon font-bold mb-15 font-11">📅 {{ __('messages.th_deadline') }}: {{ date('d M Y', strtotime($m->deadline)) }}</div>
                    </div>
                    
                    <form action="{{ route('creator.apply_mission') }}" method="POST" class="form-reserve-integrated mt-10">
                        @csrf 
                        <input type="hidden" name="mission_id" value="{{ $m->id }}">
                        
                        <textarea name="message" rows="2" placeholder="{{ __('messages.ph_why_ideal_creator') }}" class="premium-textarea m-0 h-60" required></textarea>
                        
                        <button type="button" class="btn-premium btn-pink-hub m-0" style="width: max-content !important; padding: 0 20px; align-self: flex-end;" onclick="confirmarAccion(event, '{{ __('messages.confirm_application') }}', 'info', '#e84393')">
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
                                    <div class="text-white-pure font-bold font-13">
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
                                        {{-- NUEVO: Botones para que el Creador acepte o rechace la invitación bilingüe --}}
                                        <div style="display: flex; gap: 8px;">
                                            <form action="{{ route('creator.mission.accept_invite') }}" method="POST" class="m-0">
                                                @csrf
                                                <input type="hidden" name="mission_id" value="{{ $p->mission_id ?? $p->id }}">
                                                <button type="submit" class="badge-ghost-success font-bold" style="border: none; cursor: pointer; padding: 4px 12px; border-radius: 4px;">
                                                    ✅ {{ __('messages.btn_accept') }}
                                                </button>
                                            </form>
                                            <form action="{{ route('creator.mission.reject_invite') }}" method="POST" class="m-0">
                                                @csrf
                                                <input type="hidden" name="mission_id" value="{{ $p->mission_id ?? $p->id }}">
                                                <button type="submit" class="badge-ghost-danger font-bold" style="border: none; cursor: pointer; padding: 4px 12px; border-radius: 4px;">
                                                    ❌ {{ __('messages.btn_reject') }}
                                                </button>
                                            </form>
                                        </div>
                                    @elseif($p->status === 'accepted')
                                        {{-- 🎯 CORRECCIÓN: Si ya fue revisado individualmente por el Lab o la misión cerró por completo --}}
                                        @if($p->is_reviewed || $p->mission_status === 'completed')
                                            <div class="action-cell-flex-6">
                                                <span class="badge-ghost-success">🎉 {{ __('messages.status_mission_finished') }}</span>
                                                @if($p->ya_calificado == 0)
                                                    {{-- 🚀 Si el creador no ha dejado su reseña de vuelta hacia el Lab, abrimos el modal --}}
                                                    <button type="button" class="btn-back-minimal btn-min-eval-gold font-11" style="width: auto !important; height: auto;" onclick="abrirModalReputacionMision({{ $p->mission_id ?? $p->id }}, {{ $p->lab_id }}, '{{ $p->lab_name }}', '{{ $p->title }}')">
                                                        ⭐ {{ __('messages.btn_rate_service') }}
                                                    </button>
                                                @else
                                                    <span class="status-text-approved">✓ {{ __('messages.lbl_rating_sent') }}</span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="badge-ghost-info">✓ {{ __('messages.status_accepted_working') }}</span>
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

    {{-- 📦 PLANTILLA MAESTRA DE EVALUACIÓN DE LAB POR MISIÓN (Protegida en Blade) --}}
    <template id="review-mission-modal-template">
        <form id="form-evaluar-mision-lab" action="{{ route('creator.rate_lab') }}" method="POST">
            @csrf
            <input type="hidden" name="mission_id" id="swal-mission-id">
            <input type="hidden" name="lab_id" id="swal-lab-id">

            <div class="modal-info-box grid-mission-inputs">
                <div>
                    <div class="modal-rating-label">{{ __('messages.th_lab') }}</div>
                    <div id="swal-mission-nombre-lab" class="modal-creator-name text-white-pure">-</div>
                </div>
                <div>
                    <div class="modal-rating-label-container">
                        {{-- 🔵 REUTILIZACIÓN: Pinta la insignia de misión con tu color blue de servicios --}}
                        <span class="badge-semantic badge-service">{{ __('messages.th_mission') }}</span>
                    </div>
                    <div id="swal-mission-nombre-activo" class="modal-creator-name text-white-pure">-</div>
                </div>
            </div>

            <div class="rating-stars-row">
                <label class="modal-rating-label">{{ __('messages.lbl_general_rating') }}</label>
                <div class="star-rating-cyber">
                    <input type="radio" id="mission-star5" name="rating" value="5" checked><label for="mission-star5">★</label>
                    <input type="radio" id="mission-star4" name="rating" value="4"><label for="mission-star4">★</label>
                    <input type="radio" id="mission-star3" name="rating" value="3"><label for="mission-star3">★</label>
                    <input type="radio" id="mission-star2" name="rating" value="2"><label for="mission-star2">★</label>
                    <input type="radio" id="mission-star1" name="rating" value="1"><label for="mission-star1">★</label>
                </div>
            </div>

            <div class="mb-22">
                <textarea name="comment" placeholder="{{ __('messages.ph_review_lab_mision') }}" class="premium-textarea m-0 h-90" required></textarea>
            </div>
        </form>
    </template>

</div>

@push('scripts')
<script>
function abrirModalReputacionMision(missionId, labId, labName, missionTitle) {
    Swal.fire({
        title: '⭐ {{ __('messages.modal_rate_title') }}',
        html: document.getElementById('review-mission-modal-template').innerHTML,
        background: '#1c2230',
        color: '#fff',
        showCancelButton: true,
        confirmButtonColor: '#f1c40f',
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: '💾 {{ __('messages.btn_submit_review') }}',
        cancelButtonText: '{{ __('messages.swal_cancel') }}',
        customClass: { popup: 'premium-popup' },
        
        // 🚀 CIRCUITO BLINDADO: Escribimos directo en el DOM activo del modal
        didOpen: () => {
            const modalVivo = Swal.getHtmlContainer();
            
            modalVivo.querySelector('#swal-mission-id').value = missionId;
            modalVivo.querySelector('#swal-lab-id').value = labId;
            modalVivo.querySelector('#swal-mission-nombre-lab').textContent = labName;
            modalVivo.querySelector('#swal-mission-nombre-activo').textContent = missionTitle;
            
            modalVivo.querySelector('#mission-star5').checked = true;
        },
        
        preConfirm: () => {
            const form = Swal.getPopup().querySelector('#form-evaluar-mision-lab');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.getPopup().querySelector('#form-evaluar-mision-lab').submit();
        }
    });
}
</script>
@endpush
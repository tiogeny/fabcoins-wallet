@php
    $misionesDelLab = DB::table('missions')->where('lab_id', auth()->id())->orderBy('id', 'desc')->get();
    $alumnosFinanciados = DB::table('users')->where('deuda_lab_id', auth()->id())->where('deuda_fc', '>', 0)->get();
    
    // 🔥 CONTROL DE INTRUSIÓN: Como no tienes tablas de habilidades mapeadas, inicializamos un array vacío seguro
    $catalogoHabilidades = collect();
@endphp

<div class="focus-glow-rosado">
    <div class="premium-glass-card">
        <h2 class="premium-glass-card-title">🚀 {{ __('messages.miss_create_title') }}</h2>
        <p class="premium-glass-card-subtitle">{{ __('messages.miss_create_desc') }}</p>

        <form action="{{ route('lab.mission.store') }}" method="POST">
            @csrf
            
            <div class="flex-col-gap-15">
                <div>
                    <input type="text" name="title" placeholder="{{ __('messages.ph_miss_title') }}" class="premium-input m-0" required>
                </div>

                <div>
                    <textarea name="description" placeholder="{{ __('messages.ph_miss_desc') }}" class="premium-textarea m-0 h-90" required></textarea>
                </div>

                <div class="grid-mission-inputs">
                    <div>
                        <input type="url" name="reference_link" placeholder="{{ __('messages.ph_ref_link') }}" class="premium-input m-0">
                    </div>
                    <div>
                        <select name="target_creator_id" id="selector-mision-dirigida" onchange="evaluarRulesCuposMision(this)" class="premium-select m-0">
                            <option value="">{{ __('messages.opt_open_mission') }}</option>
                            @foreach($alumnosFinanciados as $alumno)
                                <option value="{{ $alumno->id }}" data-deuda="{{ intval($alumno->deuda_fc) }}">🎓 {{ __('messages.lbl_only_for') }} {{ $alumno->name }} ({{ intval($alumno->deuda_fc) }} FC)</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="grid-mission-config">
                    <div>
                        <label class="premium-label">{{ __('messages.lbl_spots') }}</label>
                        <input type="number" name="spots_total" id="input-spots-mision" value="1" min="1" class="premium-input m-0 text-center-wrapper font-rajdhani-15">
                    </div>
                    <div>
                        <label class="premium-label">{{ __('messages.lbl_reward_per_creator') }}</label>
                        <input type="number" name="reward_fc" value="" placeholder="0" min="1" class="input-reward-gold" required>
                    </div>
                    <div>
                        <label class="premium-label">{{ __('messages.th_limit') }}</label>
                        <div class="relative-flex-w100">
                            <span class="calendar-icon-overlay">📅</span>
                            <input type="date" name="deadline" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="input-date-premium" required>
                        </div>
                    </div>
                    <div class="pt-24">
                        <button type="submit" class="btn-premium btn-rosado-hub m-0 w-100">{{ __('messages.btn_publish_miss') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="premium-glass-card mb-0">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title">📋 {{ __('messages.miss_list_title') }}</h3>
        </div>
        
        <div class="table-container">
            <table class="premium-data-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.th_miss_details') }}</th>
                        <th class="text-center" style="width: 110px;">{{ __('messages.th_limit') }}</th>
                        <th class="text-center" style="width: 120px;">{{ __('messages.th_reward') }}</th>
                        <th style="width: 320px;">{{ __('messages.th_applicants') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($misionesDelLab->isEmpty())
                        <tr><td colspan="4" class="empty-state">{{ __('messages.miss_list_empty') }}</td></tr>
                    @else
                        @foreach($misionesDelLab as $m)
                            <tr class="tr-align-top">
                                <td>
                                    <div class="miss-title-text">{{ $m->title }}</div>
                                    <div class="miss-desc-text">{{ $m->description }}</div>
                                    @if($m->reference_link)
                                        <a href="{{ $m->reference_link }}" target="_blank" class="miss-link-text">🔗 {{ __('messages.lbl_view_attachment') }}</a>
                                    @endif
                                </td>
                                
                                <td class="text-center td-date-rajdhani">
                                    {{ date('d M Y', strtotime($m->deadline)) }}
                                </td>
                                
                                <td class="text-center">
                                    <span class="td-amount-gold block-16">{{ intval($m->reward_fc) }} <small class="token-unit-small">FC</small></span>
                                    <span class="spots-filled-text">{{ $m->spots_filled }} / {{ $m->spots_total }}</span>
                                </td>

                                <td>
                                    @php
                                        $postulantes = DB::table('mission_applications')
                                            ->join('users', 'mission_applications.creator_id', '=', 'users.id')
                                            ->where('mission_applications.mission_id', $m->id)
                                            ->select('users.name', 'users.id as creator_id', 'users.deuda_fc', 'users.deuda_lab_id', 'mission_applications.status', 'mission_applications.is_reviewed')
                                            ->get();
                                    @endphp

                                    @if($postulantes->isEmpty())
                                        <span class="empty-italic-text">{{ __('messages.lbl_no_applicants') }}</span>
                                    @else
                                        <div class="flex-col-gap-8">
                                            @foreach($postulantes as $p)
                                                @php $esDeudorDirecto = ($p->deuda_lab_id == auth()->id() && $p->deuda_fc > 0); @endphp
                                                <div class="applicant-row-card">
                                                    <div class="flex-col-gap-2">
                                                        <span class="applicant-name-text">{{ $p->name }}</span>
                                                        @if($esDeudorDirecto)
                                                            <span class="badge-financiado">{{ __('messages.lbl_financiado') }}</span>
                                                        @endif
                                                    </div>

                                                    <div class="action-cell-flex-6">
                                                        @if($p->status === 'pending')
                                                            <form action="{{ route('lab.mission.reject') }}" method="POST" class="m-0">
                                                                @csrf
                                                                <input type="hidden" name="mission_id" value="{{ $m->id }}">
                                                                <input type="hidden" name="creator_id" value="{{ $p->creator_id }}">
                                                                <button type="submit" class="btn-back-minimal btn-min-reject">{{ __('messages.btn_discard') }}</button>
                                                            </form>
                                                            <form action="{{ route('lab.mission.assign') }}" method="POST" class="m-0">
                                                                @csrf
                                                                <input type="hidden" name="mission_id" value="{{ $m->id }}">
                                                                <input type="hidden" name="creator_id" value="{{ $p->creator_id }}">
                                                                <button type="submit" class="btn-back-minimal btn-min-assign">{{ __('messages.btn_assign_creator') }}</button>
                                                            </form>
                                                        @elseif($p->status === 'invited')
                                                            {{-- NUEVO: Mensaje para el Lab indicando que ya invitó --}}
                                                            <div class="mt-5">
                                                                <span class="badge-ghost-warning" style="font-size: 10px; opacity: 0.8;">
                                                                    ⏳ Esperando respuesta del Creador
                                                                </span>
                                                            </div>
                                                        @elseif($p->status === 'accepted' && !$p->is_reviewed)
                                                            @php 
                                                                $llaveBotonEval = $esDeudorDirecto ? 'messages.btn_eval_amortize' : 'messages.btn_eval_pay';
                                                                $claseBotonEval = $esDeudorDirecto ? 'btn-min-eval-gold' : 'btn-min-eval-green';
                                                            @endphp
                                                            <button type="button" onclick="ejecutarAperturaModalAuditoria({{ $m->id }}, {{ $p->creator_id }}, '{{ $p->name }}', {{ $esDeudorDirecto ? 'true' : 'false' }}, {{ intval($m->reward_fc) }})" class="btn-back-minimal {{ $claseBotonEval }}">
                                                                {{ __($llaveBotonEval) }}
                                                            </button>
                                                        @elseif($p->status === 'accepted' && $p->is_reviewed)
                                                            <span class="status-text-approved">{{ __('messages.lbl_rating_sent') }}</span>
                                                        @else
                                                            <span class="status-text-rejected">❌ {{ __('messages.status_rejected') }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-auditoria-talentos" class="modal-overlay-blur">
    <div class="modal-container-dark">
        <div class="modal-header-glass">
            <h3 id="modal-titulo-auditoria" class="modal-title-glass">{{ __('messages.lbl_evaluate_creator') }}</h3>
            <button type="button" onclick="document.getElementById('modal-auditoria-talentos').style.display='none'" class="modal-close-btn">&times;</button>
        </div>

        <form action="{{ route('lab.mission.complete') }}" method="POST" id="form-envio-auditoria">
            @csrf
            <input type="hidden" name="mission_id" id="modal-field-mission-id">
            <input type="hidden" name="creator_id" id="modal-field-creator-id">

            <div class="modal-info-box">
                <div class="premium-label m-0">{{ __('messages.lbl_assigned_creator') }}</div>
                <div id="modal-pizarra-nombre-creator" class="modal-creator-name">-</div>
            </div>

            <div class="mb-20">
                <label class="premium-label">{{ __('messages.lbl_what_skills') }}</label>
                <div class="skill-tags-wrapper">
                    @if($catalogoHabilidades->isEmpty())
                        <span class="empty-italic-text">{{ __('messages.lbl_no_skills_registered') }}</span>
                    @else
                        @foreach($catalogoHabilidades as $skill)
                            @php $esHard = ($skill->type === 'hard'); @endphp
                            <label class="skill-tag-label">
                               <input type="checkbox" name="endorsed_skills[]" value="{{ $skill->id }}" class="m-0" style="accent-color: {{ $esHard ? '#3498db' : '#f39c12' }};">
                                {{ $skill->name }} 
                                <small class="skill-tag-sub" style="color: {{ $esHard ? '#3498db' : '#f39c12' }};">({{ $skill->type }})</small>
                            </label>
                        @endforeach
                    @endif
                </div>
            </div>

                <div class="modal-rating-grid">
                    <label class="modal-rating-label">{{ __('messages.lbl_general_rating') }}</label>
                    <div>
                        <select name="rating" class="modal-rating-select" required>
                            <option value="5" selected>⭐ ⭐ ⭐ ⭐ ⭐ (5/5)</option>
                            <option value="4">⭐ ⭐ ⭐ ⭐ (4/5)</option>
                            <option value="3">⭐ ⭐ ⭐ (3/5)</option>
                            <option value="2">⭐ ⭐ (2/5)</option>
                            <option value="1">⭐ (1/5)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-22">
                    <textarea name="comment" placeholder="{{ __('messages.ph_comment_work') }}" class="premium-textarea m-0 h-75" required></textarea>
                </div>

                <button type="submit" onclick="dispararConfirmacionAuditoriaEfectiva(event)" id="modal-btn-execute-payout" class="btn-logout-v2 btn-modal-submit"></button>
            </form>
        </div>
    </div>
</div>

<script>
let mensajeConfirmacionModalDinamico = "";

function evaluarRulesCuposMision(selectNode) {
    const inputSpots = document.getElementById('input-spots-mision');
    if (!inputSpots) return;

    if (selectNode.value !== "") {
        inputSpots.value = 1;
        inputSpots.setAttribute('readonly', 'true');
        inputSpots.classList.add('input-readonly-state');
        inputSpots.classList.remove('input-active-state');
    } else {
        inputSpots.removeAttribute('readonly');
        inputSpots.classList.add('input-active-state');
        inputSpots.classList.remove('input-readonly-state');
    }
}

function ejecutarAperturaModalAuditoria(missionId, creatorId, creatorName, esDeudor, rewardFc) {
    document.getElementById('modal-field-mission-id').value = missionId;
    document.getElementById('modal-field-creator-id').value = creatorId;
    document.getElementById('modal-pizarra-nombre-creator').textContent = creatorName;

    const btnSubmit = document.getElementById('modal-btn-execute-payout');
    
    // Purgamos las clases de color antes de inyectar la nueva
    btnSubmit.classList.remove('btn-modal-gold', 'btn-modal-green');
    
    if (esDeudor) {
        btnSubmit.textContent = "{{ __('messages.btn_approve_amortize') }}";
        btnSubmit.classList.add('btn-modal-gold');
        mensajeConfirmacionModalDinamico = "{{ __('messages.btn_approve_amortize') }}"; 
    } else {
        btnSubmit.textContent = "{{ __('messages.btn_pay_rate') }}";
        btnSubmit.classList.add('btn-modal-green');
        mensajeConfirmacionModalDinamico = "{{ __('messages.btn_pay_rate') }}";
    }

    document.getElementById('modal-auditoria-talentos').style.display = 'flex';
}

function dispararConfirmacionAuditoriaEfectiva(event) {
    event.preventDefault();
    confirmarAccion(event, mensajeConfirmacionModalDinamico, 'info', '#3498db');
}
</script>
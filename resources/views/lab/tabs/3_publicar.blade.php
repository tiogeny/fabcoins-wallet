@php
    $misionesDelLab = DB::table('missions')->where('lab_id', auth()->id())->orderBy('id', 'desc')->get();
    $alumnosFinanciados = DB::table('users')->where('deuda_lab_id', auth()->id())->where('deuda_fc', '>', 0)->get();
    
    // 🔥 CONTROL DE INTRUSIÓN: Como no tienes tablas de habilidades mapeadas, inicializamos un array vacío seguro
    $catalogoHabilidades = collect();
@endphp

<div class="focus-glow-pink">
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

                <div class="grid-mission-config" style="align-items: flex-end;">
                    <div>
                        <label class="premium-label">{{ __('messages.lbl_spots') }}</label>
                        <input type="number" name="spots_total" id="input-spots-mision" value="1" min="1" class="premium-input m-0 text-center-wrapper font-rajdhani-15">
                    </div>
                    <div>
                        <label class="premium-label">{{ __('messages.lbl_reward_per_creator') }}</label>
                        <input type="number" name="reward_fc" value="" placeholder="0" min="1" class="premium-input m-0 text-center-wrapper font-rajdhani-15" required>
                    </div>
                    <div>
                        <label class="premium-label">{{ __('messages.th_limit') }}</label>
                        <div class="relative-flex-w100">
                            <span class="calendar-icon-overlay">📅</span>
                            <input type="date" name="deadline" value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="premium-input m-0 text-center-wrapper font-rajdhani-15" required>
                        </div>
                    </div>
                    <!-- 🎯 CORRECCIÓN: Quitamos el 'pt-24' que descuadraba el botón y forzamos su altura exacta -->
                    <div>
                        <button type="submit" class="btn-premium btn-pink-hub m-0 w-100" style="height: 38px; background-color: #e84393; border-color: #e84393;">
                            {{ __('messages.btn_publish_miss') }}
                        </button>
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

    {{-- 📦 PLANTILLA MAESTRA DE AUDITORÍA DE TALENTOS (Organizada y protegida en Blade) --}}
    <template id="audit-modal-template">
        <form id="form-envio-auditoria" action="{{ route('lab.mission.complete') }}" method="POST">
            @csrf
            <input type="hidden" name="mission_id" id="swal-field-mission-id">
            <input type="hidden" name="creator_id" id="swal-field-creator-id">

            <div class="modal-info-box">
                <div class="premium-label m-0">{{ __('messages.lbl_assigned_creator') }}</div>
                <div id="swal-pizarra-nombre-creator" class="modal-creator-name text-white-pure">-</div>
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
                               <input type="checkbox" name="endorsed_skills[]" value="{{ $skill->id }}" class="m-0">
                                {{ $skill->name }} 
                                <small class="skill-tag-sub">({{ $skill->type }})</small>
                            </label>
                        @endforeach
                    @endif
                </div>
            </div>

            <div class="rating-stars-row">
                <label class="modal-rating-label">{{ __('messages.lbl_general_rating') }}</label>
                <div class="star-rating-cyber">
                    <input type="radio" id="creator-star5" name="rating" value="5" checked><label for="creator-star5">★</label>
                    <input type="radio" id="creator-star4" name="rating" value="4"><label for="creator-star4">★</label>
                    <input type="radio" id="creator-star3" name="rating" value="3"><label for="creator-star3">★</label>
                    <input type="radio" id="creator-star2" name="rating" value="2"><label for="creator-star2">★</label>
                    <input type="radio" id="creator-star1" name="rating" value="1"><label for="creator-star1">★</label>
                </div>
            </div>

            <div class="mb-22">
                <textarea name="comment" placeholder="{{ __('messages.ph_comment_work') }}" class="premium-textarea m-0 h-75" required></textarea>
            </div>
        </form>
    </template>
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
    // 1. Clonamos el esqueleto HTML puro de nuestra plantilla template
    const template = document.getElementById('audit-modal-template');
    const clonDOM = template.content.cloneNode(true);

    // 2. Inyectamos los valores relacionales de la fila seleccionada
    clonDOM.querySelector('#swal-field-mission-id').value = missionId;
    clonDOM.querySelector('#swal-field-creator-id').value = creatorId;
    clonDOM.querySelector('#swal-pizarra-nombre-creator').textContent = creatorName;

    // 🧮 MOTOR ADAPTATIVO: Configura la naturaleza contable del botón SweetAlert
    let textoConfirmacionBoton = "";
    let colorConfirmacionBoton = "";

    if (esDeudor) {
        textoConfirmacionBoton = "{{ __('messages.btn_approve_amortize') }}";
        colorConfirmacionBoton = '#f1c40f'; // Oro Corporativo (Amortización de Deuda)
    } else {
        textoConfirmacionBoton = "{{ __('messages.btn_pay_rate') }}";
        colorConfirmacionBoton = '#2ecc71'; // Verde Esmeralda (Pago Líquido)
    }

    // Encapsulado preventivo para transferir la estructura procesada a Swal
    const wrapperTemporal = document.createElement('div');
    wrapperTemporal.appendChild(clonDOM);

    // 3. Lanzamiento del Tablero mediante SweetAlert en la raíz del DOM
    Swal.fire({
        title: '⭐ {{ __('messages.lbl_evaluate_creator') }}',
        html: wrapperTemporal.innerHTML,
        background: '#1c2230',
        color: '#fff',
        showCancelButton: true,
        confirmButtonColor: colorConfirmacionBoton,
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: '💾 ' + textoConfirmacionBoton,
        cancelButtonText: '{{ __('messages.swal_cancel') }}',
        customClass: { popup: 'premium-popup' },
        preConfirm: () => {
            // Validación HTML5 integrada antes del despacho
            const form = document.getElementById('form-envio-auditoria');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('form-envio-auditoria').submit();
        }
    });
}

function dispararConfirmacionAuditoriaEfectiva(event) {
    event.preventDefault();
    confirmarAccion(event, mensajeConfirmacionModalDinamico, 'info', '#3498db');
}
</script>
@php
    $misionesDelLab = DB::table('missions')->where('lab_id', auth()->id())->orderBy('id', 'desc')->get();
    $alumnosFinanciados = DB::table('users')->where('deuda_lab_id', auth()->id())->where('deuda_fc', '>', 0)->get();
    
    // 🔥 CONTROL DE INTRUSIÓN: Como no tienes tablas de habilidades mapeadas, inicializamos un array vacío seguro
    $catalogoHabilidades = collect();
@endphp

<div class="focus-glow-rosado">
    <div class="card" style="border: 1px solid rgba(255, 255, 255, 0.04); background: #1c2230; margin-bottom: 25px; padding: 24px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <h2 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 19px; color: #ffffff; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">🚀 {{ __('messages.miss_create_title') }}</h2>
        <p class="text-muted" style="font-size: 12.5px; margin-bottom: 25px; color: #a0aec0;">{{ __('messages.miss_create_desc') }}</p>

        <form action="{{ route('lab.mission.store') }}" method="POST">
            @csrf
            
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <div>
                    <input type="text" name="title" placeholder="{{ __('messages.ph_miss_title') }}" style="width: 100%; margin-bottom: 0; background: #131722; border: 1px solid rgba(255,255,255,0.06); color: #fff; height: 40px; border-radius: 6px; font-size: 13px; padding: 0 12px;" required>
                </div>

                <div>
                    <textarea name="description" placeholder="{{ __('messages.ph_miss_desc') }}" style="width: 100%; height: 90px; margin-bottom: 0; background: #131722; border: 1px solid rgba(255,255,255,0.06); color: #fff; border-radius: 6px; font-size: 13px; padding: 10px 12px; resize: none; font-family: 'Inter', sans-serif;" required></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 15px;">
                    <div>
                        <input type="url" name="reference_link" placeholder="{{ __('messages.ph_ref_link') }}" style="width: 100%; margin-bottom: 0; background: #131722; border: 1px solid rgba(255,255,255,0.06); color: #fff; height: 40px; border-radius: 6px; font-size: 13px; padding: 0 12px;">
                    </div>
                    <div>
                        <select name="target_creator_id" id="selector-mision-dirigida" onchange="evaluarRulesCuposMision(this)" style="width: 100%; font-weight: 600; margin-bottom: 0; background: #131722; border: 1px solid rgba(255,255,255,0.06); color: #fff; height: 40px; border-radius: 6px; font-size: 12.5px;">
                            <option value="">{{ __('messages.opt_open_mission') }}</option>
                            @foreach($alumnosFinanciados as $alumno)
                                <option value="{{ $alumno->id }}" data-deuda="{{ intval($alumno->deuda_fc) }}">🎓 {{ __('messages.lbl_only_for') }} {{ $alumno->name }} ({{ intval($alumno->deuda_fc) }} FC)</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 0.8fr 0.8fr 1fr 1.4fr; gap: 15px; align-items: center;">
                    <div>
                        <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; display: block; margin-bottom: 5px;">{{ __('messages.lbl_spots') }}</label>
                        <input type="number" name="spots_total" id="input-spots-mision" value="1" min="1" style="width: 100%; background: #131722; border: 1px solid rgba(255,255,255,0.06); color: #fff; height: 38px; border-radius: 6px; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 15px; text-align: center;">
                    </div>
                    <div>
                        <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; display: block; margin-bottom: 5px;">{{ __('messages.lbl_reward_per_creator') }}</label>
                        <input type="number" name="reward_fc" value="" placeholder="0" min="1" style="width: 100%; background: #131722; border: 1px solid rgba(255,255,255,0.06); color: #f1c40f; height: 38px; border-radius: 6px; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 15px; text-align: center;" required>
                    </div>
                    <div>
                        <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; display: block; margin-bottom: 5px; letter-spacing: 0.5px;">{{ __('messages.th_limit') }}</label>
                        <div style="position: relative; display: flex; align-items: center; width: 100%;">
                            <span style="position: absolute; left: 14px; color: #e84393; font-size: 13px; pointer-events: none; z-index: 5;">📅</span>
                            <input type="date" name="deadline" value="{{ date('Y-m-d', strtotime('+7 days')) }}" 
                                style="width: 100%; background: #131722; border: 1px solid rgba(255,255,255,0.08); color: #fff; height: 38px; border-radius: 6px; font-size: 12.5px; padding: 0 12px 0 38px; color-scheme: dark; font-family: 'Rajdhani', sans-serif; font-weight: 600; cursor: pointer; transition: all 0.2s ease-in-out;"
                                onmouseover="this.style.borderColor='rgba(232, 67, 147, 0.4)'; this.style.boxShadow='0 0 10px rgba(232, 67, 147, 0.08)';"
                                onmouseout="this.style.borderColor='rgba(255,255,255,0.08)'; this.style.boxShadow='none';"
                                required>
                        </div>
                    </div>
                    <div style="text-align: right; padding-top: 18px;">
                        <button type="submit" class="btn-logout-v2" style="background: #e84393; color: #ffffff; border: 1px solid #e84393; width: 100%; height: 40px; font-size: 12px; font-weight: 700; border-radius: 6px; margin: 0; box-shadow: 0 4px 15px rgba(232, 67, 147, 0.15);">{{ __('messages.btn_publish_miss') }}</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="card" style="background: #1c2230; border: 1px solid rgba(255, 255, 255, 0.04); padding: 24px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <h2 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 18px; color: #ffffff; margin-bottom: 18px; text-transform: uppercase; letter-spacing: 0.5px;">📋 {{ __('messages.miss_list_title') }}</h2>
        
        <div class="table-container">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.08); text-align: left;">
                        <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase;">{{ __('messages.th_miss_details') }}</th>
                        <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase; width: 110px; text-align: center;">{{ __('messages.th_limit') }}</th>
                        <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase; width: 120px; text-align: center;">{{ __('messages.th_reward') }}</th>
                        <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase; width: 320px;">{{ __('messages.th_applicants') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($misionesDelLab->isEmpty())
                        <tr><td colspan="4" style="text-align:center; padding:50px; color:#7f8c8d; font-size: 13px;">{{ __('messages.miss_list_empty') }}</td></tr>
                    @else
                        @foreach($misionesDelLab as $m)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.02); vertical-align: top;">
                                <td style="padding: 14px 12px;">
                                    <div style="font-weight: 600; color: #ffffff; font-size: 13.5px; margin-bottom: 4px;">{{ $m->title }}</div>
                                    <div style="font-size: 11.5px; color: #a0aec0; line-height: 1.4; max-width: 400px;">{{ $m->description }}</div>
                                    @if($m->reference_link)
                                        <a href="{{ $m->reference_link }}" target="_blank" style="color: #3498db; font-size: 11px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-top: 6px;">🔗 {{ __('messages.lbl_view_attachment') }}</a>
                                    @endif
                                </td>
                                
                                <td style="padding: 14px 12px; text-align: center; font-family: 'Rajdhani', sans-serif; font-weight: 700; color: #cbd5e0; font-size: 13px;">
                                    {{ date('d M Y', strtotime($m->deadline)) }}
                                </td>
                                
                                <td style="padding: 14px 12px; text-align: center;">
                                    <span style="font-family: 'Rajdhani', sans-serif; font-weight: 800; color: #f1c40f; font-size: 16px; display: block;">{{ intval($m->reward_fc) }} <small style="font-size: 10px; color: #7f8c8d;">FC</small></span>
                                    <span style="font-size: 9.5px; color: #7f8c8d; text-transform: uppercase; font-weight: 600;">{{ $m->spots_filled }} / {{ $m->spots_total }}</span>
                                </td>

                                <td style="padding: 14px 12px;">
                                    @php
                                        $postulantes = DB::table('mission_applications')
                                            ->join('users', 'mission_applications.creator_id', '=', 'users.id')
                                            ->where('mission_applications.mission_id', $m->id)
                                            ->select('users.name', 'users.id as creator_id', 'users.deuda_fc', 'users.deuda_lab_id', 'mission_applications.status', 'mission_applications.is_reviewed')
                                            ->get();
                                    @endphp

                                    @if($postulantes->isEmpty())
                                        <span style="font-size: 11.5px; color: #4a5568; font-style: italic;">{{ __('messages.lbl_no_applicants') }}</span>
                                    @else
                                        <div style="display: flex; flex-direction: column; gap: 8px;">
                                            @foreach($postulantes as $p)
                                                @php $esDeudorDirecto = ($p->deuda_lab_id == auth()->id() && $p->deuda_fc > 0); @endphp
                                                <div style="background: #131722; padding: 8px 12px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; border: 1px solid rgba(255,255,255,0.01)">
                                                    <div style="display: flex; flex-direction: column; gap: 2px;">
                                                        <span style="color: #cbd5e0; font-size: 12.5px; font-weight: 600;">{{ $p->name }}</span>
                                                        @if($esDeudorDirecto)
                                                            <span style="font-size: 9px; background: rgba(241, 196, 15, 0.08); color: #f1c40f; padding: 1px 4px; border-radius: 3px; font-weight: 700; width: fit-content; letter-spacing: 0.3px;">{{ __('messages.lbl_financiado') }}</span>
                                                        @endif
                                                    </div>

                                                    <div style="display: flex; gap: 6px;">
                                                        @if($p->status === 'pending')
                                                            <form action="{{ route('lab.mission.reject') }}" method="POST" style="margin:0;">
                                                                @csrf
                                                                <input type="hidden" name="mission_id" value="{{ $m->id }}">
                                                                <input type="hidden" name="creator_id" value="{{ $p->creator_id }}">
                                                                <button type="submit" class="btn-back-minimal" style="padding: 4px 8px; font-size: 9.5px; border-color: rgba(231,76,60,0.2); color: #e74c3c !important;">{{ __('messages.btn_discard') }}</button>
                                                            </form>
                                                            <form action="{{ route('lab.mission.assign') }}" method="POST" style="margin:0;">
                                                                @csrf
                                                                <input type="hidden" name="mission_id" value="{{ $m->id }}">
                                                                <input type="hidden" name="creator_id" value="{{ $p->creator_id }}">
                                                                <button type="submit" class="btn-back-minimal" style="padding: 4px 10px; font-size: 9.5px; background: rgba(52,152,219,0.05) !important; border-color: rgba(52,152,219,0.3) !important; color: #3498db !important;">{{ __('messages.btn_assign_creator') }}</button>
                                                            </form>
                                                        @elseif($p->status === 'accepted' && !$p->is_reviewed)
                                                            @php 
                                                                $llaveBotonEval = $esDeudorDirecto ? 'messages.btn_eval_amortize' : 'messages.btn_eval_pay';
                                                                $colorBotonEval = $esDeudorDirecto ? '#f1c40f' : '#2ecc71';
                                                            @endphp
                                                            <button type="button" onclick="ejecutarAperturaModalAuditoria({{ $m->id }}, {{ $p->creator_id }}, '{{ $p->name }}', {{ $esDeudorDirecto ? 'true' : 'false' }}, {{ intval($m->reward_fc) }})" class="btn-back-minimal" style="padding: 5px 12px; font-size: 10px; font-weight:700; background: rgba(255,255,255,0.02) !important; border-color: {{ $colorBotonEval }} !important; color: {{ $colorBotonEval }} !important;">
                                                                {{ __($llaveBotonEval) }}
                                                            </button>
                                                        @elseif($p->status === 'accepted' && $p->is_reviewed)
                                                            <span style="font-size: 10px; color: #2ecc71; font-weight: 700; letter-spacing: 0.5px;">{{ __('messages.lbl_rating_sent') }}</span>
                                                        @else
                                                            <span style="font-size: 11px; color: #7f8c8d;">❌ Rejected</span>
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

    <div id="modal-auditoria-talentos" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.75); z-index: 3000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div style="background: #1c2230; width: 100%; max-width: 520px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.06); box-shadow: 0 20px 50px rgba(0,0,0,0.6); overflow: hidden; padding: 24px;">
            
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid rgba(255,255,255,0.04); padding-bottom: 12px;">
                <h3 id="modal-titulo-auditoria" style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 17px; color: #ffffff; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">{{ __('messages.lbl_evaluate_creator') }}</h3>
                <button type="button" onclick="document.getElementById('modal-auditoria-talentos').style.display='none'" style="background: transparent; border: none; color: #7f8c8d; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <form action="{{ route('lab.mission.complete') }}" method="POST" id="form-envio-auditoria">
                @csrf
                <input type="hidden" name="mission_id" id="modal-field-mission-id">
                <input type="hidden" name="creator_id" id="modal-field-creator-id">

                <div style="background: #131722; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.01);">
                    <div style="font-size: 11px; color: #7f8c8d; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">{{ __('messages.lbl_assigned_creator') }}</div>
                    <div id="modal-pizarra-nombre-creator" style="font-size: 15px; color: #ffffff; font-weight: 600; margin-top: 2px;">-</div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="font-size: 11px; font-weight: 700; color: #a0aec0; text-transform: uppercase; display: block; margin-bottom: 6px; letter-spacing: 0.5px;">{{ __('messages.lbl_what_skills') }}</label>
                    <div style="background: #131722; border: 1px solid rgba(255,255,255,0.04); border-radius: 8px; padding: 12px; max-height: 130px; overflow-y: auto; display: flex; flex-wrap: wrap; gap: 6px;">
                        @if($catalogoHabilidades->isEmpty())
                            <span style="font-size: 11.5px; color: #4a5568; font-style: italic;">{{ __('messages.lbl_no_skills_registered') }}</span>
                        @else
                            @foreach($catalogoHabilidades as $skill)
                                @php $esHard = ($skill->type === 'hard'); @endphp
                                <label style="display: inline-flex; align-items: center; gap: 6px; background: #1c2230; padding: 4px 8px; border-radius: 4px; font-size: 11px; color: #cbd5e0; cursor: pointer; border: 1px solid rgba(255,255,255,0.02); transition: background 0.2s;">
                                    <input type="checkbox" name="endorsed_skills[]" value="{{ $skill->id }}" style="margin: 0; accent-color: {{ $esHard ? '#3498db' : '#f39c12' }};">
                                    {{ $skill->name }} 
                                    <small style="font-size: 8.5px; opacity: 0.5; color: {{ $esHard ? '#3498db' : '#f39c12' }}; font-weight: 700;">({{ $skill->type }})</small>
                                </label>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div style="margin-bottom: 20px; display: grid; grid-template-columns: 1fr 1.2fr; gap: 15px; align-items: center;">
                    <label style="font-size: 11px; font-weight: 700; color: #a0aec0; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0;">{{ __('messages.lbl_general_rating') }}</label>
                    <div>
                        <select name="rating" style="width: 100%; font-weight: 700; background: #131722; border: 1px solid rgba(255,255,255,0.06); color: #f1c40f; height: 36px; border-radius: 6px; font-size: 13px; text-align: center;" required>
                            <option value="5" selected>⭐ ⭐ ⭐ ⭐ ⭐ (5/5)</option>
                            <option value="4">⭐ ⭐ ⭐ ⭐ (4/5)</option>
                            <option value="3">⭐ ⭐ ⭐ (3/5)</option>
                            <option value="2">⭐ ⭐ (2/5)</option>
                            <option value="1">⭐ (1/5)</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom: 22px;">
                    <textarea name="comment" placeholder="{{ __('messages.ph_comment_work') }}" style="width: 100%; height: 75px; background: #131722; border: 1px solid rgba(255,255,255,0.06); color: #fff; border-radius: 6px; font-size: 12.5px; padding: 8px 12px; resize: none; font-family: 'Inter', sans-serif;" required></textarea>
                </div>

                <button type="submit" onclick="dispararConfirmacionAuditoriaEfectiva(event)" id="modal-btn-execute-payout" class="btn-logout-v2" style="width: 100%; height: 42px; font-size: 12px; font-weight: 700; border-radius: 6px; margin: 0; box-shadow: 0 4px 15px rgba(46, 204, 113, 0.15);"></button>
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
        inputSpots.style.background = '#1c2230';
        inputSpots.style.color = '#7f8c8d';
    } else {
        inputSpots.removeAttribute('readonly');
        inputSpots.style.background = '#131722';
        inputSpots.style.color = '#fff';
    }
}

function ejecutarAperturaModalAuditoria(missionId, creatorId, creatorName, esDeudor, rewardFc) {
    document.getElementById('modal-field-mission-id').value = missionId;
    document.getElementById('modal-field-creator-id').value = creatorId;
    document.getElementById('modal-pizarra-nombre-creator').textContent = creatorName;

    const btnSubmit = document.getElementById('modal-btn-execute-payout');
    
    if (esDeudor) {
        btnSubmit.textContent = "{{ __('messages.btn_approve_amortize') }}";
        btnSubmit.style.background = '#f1c40f';
        btnSubmit.style.borderColor = '#f1c40f';
        btnSubmit.style.color = '#111111';
        mensajeConfirmacionModalDinamico = "{{ __('messages.btn_approve_amortize') }}"; 
    } else {
        btnSubmit.textContent = "{{ __('messages.btn_pay_rate') }}";
        btnSubmit.style.background = '#2ecc71';
        btnSubmit.style.borderColor = '#2ecc71';
        btnSubmit.style.color = '#ffffff';
        mensajeConfirmacionModalDinamico = "{{ __('messages.btn_pay_rate') }}";
    }

    document.getElementById('modal-auditoria-talentos').style.display = 'flex';
}

function dispararConfirmacionAuditoriaEfectiva(event) {
    event.preventDefault();
    confirmarAccion(event, mensajeConfirmacionModalDinamico, 'info', '#3498db');
}
</script>
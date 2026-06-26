<div class="focus-glow-blue">

    {{-- 1. MOTOR DE TRANSFERENCIAS P2P --}}
    <div class="premium-glass-card">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title m-0">💸 {{ __('messages.title_send_fc') }}</h3>
        </div>
        <p class="premium-glass-card-subtitle">{{ __('messages.desc_send_fc') }}</p>

        <style>
            /* Elimina las flechas del input number para que no choquen con el texto FC */
            .no-spin-arrows::-webkit-inner-spin-button, .no-spin-arrows::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
            .no-spin-arrows { -moz-appearance: textfield; padding-right: 40px !important; }
        </style>

        <form action="{{ route('creator.transfer_p2p') }}" method="POST" class="form-reserve-integrated mt-20" style="border: none; padding: 0;">
            @csrf
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 15px; align-items: end;">
                <div>
                    <label class="premium-label">{{ __('messages.lbl_recipient_email') }}</label>
                    <input type="email" name="dest_email" id="p2p-email-input" class="premium-input m-0" placeholder="{{ __('messages.ph_receiver_email') }}" required>
                </div>
                <div>
                    <label class="premium-label">{{ __('messages.lbl_amount_to_send') }}</label>
                    <div class="input-with-badge-wrapper m-0">
                        <input type="number" name="monto_p2p" class="premium-input m-0 no-spin-arrows" placeholder="{{ __('messages.ph_amount_fc') }}" min="1" max="{{ $saldoTotal }}" required>
                        <span class="badge-unidad-dinamica" style="color: #f1c40f; right: 15px;">FC</span>
                    </div>
                </div>
                <div>
                    {{-- Validación Nativa Inyectada: Si el form está incompleto, lanza advertencia de HTML, si no, abre SweetAlert --}}
                    <button type="button" class="btn-premium btn-blue-hub m-0 w-100" onclick="if(!this.closest('form').checkValidity()) { this.closest('form').reportValidity(); return; } confirmarAccion(event, '{{ __('messages.swal_confirm_p2p') }}', 'warning', '#f1c40f')">
                        🚀 {{ __('messages.btn_transfer') }}
                    </button>
                </div>
            </div>
        </form>
    </div> 

    {{-- 2. TABLA DE CRÉDITO ISA ACTIVO O PENDIENTE --}}
    <div class="premium-glass-card">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title m-0">🎓 {{ __('messages.title_isa_portfolio_creator') ?? 'Estado de Financiamiento ISA' }}</h3>
        </div>
        <div class="table-container">
            <table class="premium-data-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.th_lab') }}</th>
                        <th>{{ __('messages.th_concept') }}</th>
                        <th>{{ __('messages.th_amount') }}</th>
                        <th style="width: 180px;">{{ __('messages.th_progress') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!$creditoActual)
                        <tr><td colspan="4" class="empty-state">{{ __('messages.isa_portfolio_empty') }}</td></tr>
                    @else
                        <tr>
                            <td>
                                <div class="td-creator-name">{{ $creditoActual->lab_name }}</div>
                            </td>
                            <td class="td-concept-desc">{{ $creditoActual->description }}</td>
                            <td class="td-amount-gold text-white-pure">{{ number_format($creditoActual->amount_initial, 0) }} <small class="token-unit-small">FC</small></td>
                            <td>
                                @if($creditoActual->status === 'pending')
                                    <span class="status-text-isa-pending">⏳ {{ __('messages.status_waiting_approval') }}</span>
                                @else
                                    @php $porcentaje = round((($creditoActual->amount_initial - $creditoActual->amount_remaining) / $creditoActual->amount_initial) * 100); @endphp
                                    <div class="isa-progress-text">
                                        {{ $porcentaje }}% <span class="isa-progress-sub">{{ __('messages.lbl_recovered') }}</span>
                                    </div>
                                    <div class="isa-progress-bar-wrap">
                                        <div class="asset-progress-fill bg-warning-neon" style="width: {{ $porcentaje }}%;"></div>
                                    </div>

                                    {{-- 🚀 NUEVA CIRUGÍA: Módulo de Pago Voluntario --}}
                                    @if($creditoActual->amount_remaining > 0 && $saldoTotal > 0)
                                        <form action="{{ route('creator.pay_debt') }}" method="POST" style="display: flex; gap: 8px; align-items: center; margin-top: 12px; background: rgba(0,0,0,0.2); padding: 8px; border-radius: 6px; border: 1px dashed rgba(241, 196, 15, 0.3);">
                                            @csrf
                                            <input type="hidden" name="contract_id" value="{{ $creditoActual->id }}">
                                            <input type="number" name="amount_to_pay" class="premium-input m-0 no-spin-arrows" 
                                                   style="height: 30px; font-size: 11px; width: 80px; padding: 0 10px;" 
                                                   placeholder="FC" min="1" max="{{ min($saldoTotal, $creditoActual->amount_remaining) }}" required>
                                            <button type="button" class="btn-premium btn-blue-hub m-0" 
                                                    style="height: 30px; font-size: 10px; padding: 0 12px; flex: 1;"
                                                    onclick="if(!this.closest('form').checkValidity()) { this.closest('form').reportValidity(); return; } confirmarAccion(event, '{{ __('messages.swal_confirm_payment') }}', 'info', '#f1c40f')">
                                                💰 {{ __('messages.btn_pay_debt') }}
                                            </button>
                                        </form>
                                    @elseif($creditoActual->amount_remaining > 0 && $saldoTotal <= 0)
                                        <div style="margin-top: 10px; font-size: 10px; color: #7f8c8d; font-style: italic;">
                                            {{ __('messages.lbl_no_balance_to_pay') }}
                                        </div>
                                    @endif
                                    {{-- FIN CIRUGÍA --}}

                                @endif
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- 3. HISTORIAL DE MOVIMIENTOS --}}
    <div class="premium-glass-card">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title m-0">💳 {{ __('messages.title_transactions') }}</h3>
        </div>
        <div class="table-container">
            <table class="premium-data-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.th_date') }}</th>
                        <th>{{ __('messages.th_description') }}</th>
                        <th style="text-align: right; width: 120px;">{{ __('messages.th_amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(empty($misTransacciones) || count($misTransacciones) == 0)
                        <tr><td colspan="3" class="empty-state">{{ __('messages.empty_transactions') }}</td></tr>
                    @else
                        @foreach($misTransacciones as $tx)
                            <tr>
                                <td class="td-date-dim">{{ date('d M Y - H:i', strtotime($tx->created_at)) }}</td>
                                <td class="td-description-text">
                                    {{ $tx->description }}
                                    
                                    @if($tx->type === 'mint')
                                        <span class="tx-badge tx-badge-mint">{{ __('messages.badge_mint') }}</span>
                                    @elseif($tx->type === 'escrow')
                                        <span class="tx-badge tx-badge-escrow">{{ __('messages.badge_escrow') }}</span>
                                    @elseif($tx->type === 'info')
                                        <span class="tx-badge tx-badge-info">{{ __('messages.badge_info') }}</span>
                                    @endif
                                </td>
                                <td class="text-right tx-amount-value {{ $tx->type == 'income' ? 'text-success-neon' : 'text-danger-neon' }}">
                                    {{ $tx->type == 'income' ? '+' : '-' }}{{ number_format($tx->amount, 0) }} FC
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- 4. SECCIÓN DE PERFIL Y HABILIDADES (Integrado al final de Mercado) --}}
    <div id="seccion-perfil-habilidades" class="premium-glass-card">
        <form action="{{ route('creator.update_profile') }}" method="POST" enctype="multipart/form-data" class="m-0">
            @csrf
            <div class="premium-glass-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="premium-glass-card-title m-0">👤 {{ __('messages.title_my_profile') }}</h2>
                <button type="submit" class="btn-premium btn-blue-hub m-0" style="width: auto !important; min-width: 160px; padding: 0 20px;">
                    💾 {{ __('messages.btn_save_profile') }}
                </button>
            </div>

            <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 25px; align-items: start; margin-top: 20px; padding-bottom: 25px; border-bottom: 1px solid rgba(255, 255, 255, 0.06);">
                
                {{-- 📝 BIOGRAFÍA PROFESIONAL (COLUMNA IZQUIERDA) --}}
                <div class="flex-col-gap-10" style="display: flex; flex-direction: column; gap: 10px;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label class="premium-label" style="margin: 0;">{{ __('messages.lbl_my_bio') }}</label>
                        {{-- BOTONES INTERACTIVOS DE REQUERIMIENTO VISUAL --}}
                        <div style="display: flex; gap: 4px; background: rgba(0,0,0,0.5); padding: 4px; border-radius: 6px; border: 1px solid rgba(255,255,255,0.05);">
                            <button type="button" class="btn-back-minimal m-0" style="padding: 2px 10px; font-size: 12px; font-weight: bold; color: #3498db;" onclick="ejecutarComandoEditor('creator-editor', 'bold')" title="Negrita">B</button>
                            <button type="button" class="btn-back-minimal m-0" style="padding: 2px 10px; font-size: 12px; font-style: italic; color: #3498db;" onclick="ejecutarComandoEditor('creator-editor', 'italic')" title="Cursiva">I</button>
                            <button type="button" class="btn-back-minimal m-0" style="padding: 2px 10px; font-size: 12px; text-decoration: underline; color: #3498db;" onclick="ejecutarComandoEditor('creator-editor', 'underline')" title="Subrayado">U</button>
                            <button type="button" class="btn-back-minimal m-0" style="padding: 2px 8px; font-size: 12px; color: #3498db;" onclick="ejecutarComandoEditor('creator-editor', 'insertUnorderedList')" title="Viñetas">• </button>
                        </div>
                    </div>
                    
                    <div id="creator-editor" 
                         contenteditable="true" 
                         class="premium-textarea m-0" 
                         style="min-height: 194px; height: auto; overflow-y: auto; color: #fff; background: #131722; padding: 14px; border: 1px solid rgba(255,255,255,0.06); border-radius: 6px; outline: none; line-height: 1.5;"
                         oninput="sincronizarEditorOculto('creator-editor', 'creator-bio-hidden')">{!! $creator->bio !!}</div>
                    
                    <input type="hidden" name="bio" id="creator-bio-hidden" value="{{ $creator->bio }}">
                </div>

                {{-- 🔗 REDES Y LOCALIZACIÓN (COLUMNA DERECHA) --}}
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                        <div>
                            <label class="premium-label">LinkedIn</label>
                            <input type="url" name="social_linkedin" value="{{ $creator->social_linkedin }}" class="premium-input m-0" placeholder="🔗 {{ __('messages.ph_linkedin') }}">
                        </div>
                        <div>
                            <label class="premium-label">GitHub</label>
                            <input type="url" name="social_github" value="{{ $creator->social_github }}" class="premium-input m-0" placeholder="🐙 {{ __('messages.ph_github') }}">
                        </div>
                        <div>
                            <label class="premium-label">Instagram</label>
                            <input type="url" name="social_instagram" value="{{ $creator->social_instagram }}" class="premium-input m-0" placeholder="📸 {{ __('messages.ph_instagram') }}">
                        </div>
                        <div>
                            <label class="premium-label">Fab Academy</label>
                            <input type="url" name="social_fabacademy" value="{{ $creator->social_fabacademy }}" class="premium-input m-0" placeholder="🎓 {{ __('messages.ph_fab_academy') }}">
                        </div>
                    </div>
                    <div>
                        <label class="premium-label">{{ __('messages.lbl_portfolio') ?? 'Portafolio / Web' }}</label>
                        <input type="url" name="social_portfolio" value="{{ $creator->social_portfolio }}" class="premium-input m-0" placeholder="🌐 {{ __('messages.ph_portfolio') }}">
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; background: rgba(0,0,0,0.1); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.02);">
                        <div>
                            <label class="premium-label">{{ __('messages.lbl_city') }}</label>
                            <input type="text" name="city" value="{{ $creator->city }}" class="premium-input m-0" placeholder="{{ __('messages.ph_city') }}">
                        </div>
                        <div>
                            <label class="premium-label">{{ __('messages.lbl_country') }}</label>
                            <input type="text" name="country" value="{{ $creator->country }}" class="premium-input m-0" placeholder="{{ __('messages.ph_country') }}">
                        </div>
                    </div>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px; align-items: start; margin-top: 25px;">
                
                {{-- ⚙️ HABILIDADES TÉCNICAS (IZQUIERDA) --}}
                <div class="flex-col-gap-10" style="display: flex; flex-direction: column; gap: 10px;">
                    <label class="premium-label" style="color: #3498db; font-weight: 800; letter-spacing: 0.8px;">
                        ⚙️ {{ __('messages.lbl_hard_skills') }}<span id="live-counter-hard" style="color: #7f8c8d; font-weight: 600; margin-left: 4px;">(0)</span>
                    </label>
                    <div style="background: #131722; padding: 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.04); min-height: 180px; box-sizing: border-box;">
                        @if($catalogoSkills->where('type', 'hard')->isEmpty())
                            <span class="empty-italic-text" style="font-size: 12px; color:#7f8c8d;">No hay competencias técnicas enlistadas.</span>
                        @else
                            @php $userLang = auth()->user()->preferred_lang ?? 'es'; @endphp
                            <div class="skills-chips-matrix">
                                @foreach($catalogoSkills->where('type', 'hard') as $skill)
                                    @php 
                                        $marcado = in_array($skill->id, $misSkillsIds ?? []) ? 'checked' : '';
                                        $nombreSkillHard = ($userLang === 'en') ? $skill->name_en : $skill->name_es;
                                    @endphp
                                    <label class="m-0" style="cursor: pointer; display: inline-block;">
                                        <input type="checkbox" name="skills[]" value="{{ $skill->id }}" class="chip-hard" {{ $marcado }}>
                                        <span class="skill-premium-chip-pill">{{ $nombreSkillHard }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                {{-- 🧠 HABILIDADES BLANDAS (DERECHA) --}}
                <div class="flex-col-gap-10" style="display: flex; flex-direction: column; gap: 10px;">
                    <label class="premium-label" style="color: #f39c12; font-weight: 800; letter-spacing: 0.8px;">🧠 {{ __('messages.lbl_soft_skills') }}<span id="live-counter-soft" style="color: #7f8c8d; font-weight: 600; margin-left: 4px;">(0)</span></label>
                    <div style="background: #131722; padding: 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.04); min-height: 220px; box-sizing: border-box;">
                        @if($catalogoSkills->isEmpty())
                            <span class="empty-italic-text">{{ __('messages.lbl_no_skills_registered') }}</span>
                        @else
                            @php $userLang = auth()->user()->preferred_lang ?? 'es'; $hasSoft = false; @endphp
                            <div class="skills-chips-matrix">
                                @foreach($catalogoSkills as $skill)
                                    @if($skill->type === 'soft')
                                        @php 
                                            $hasSoft = true;
                                            $marcado = in_array($skill->id, $misSkillsIds ?? []) ? 'checked' : '';
                                            $nombreSkillDinamico = ($userLang === 'en') ? $skill->name_en : $skill->name_es;
                                        @endphp
                                        <label class="m-0" style="cursor: pointer; display: inline-block;">
                                            <input type="checkbox" name="skills[]" value="{{ $skill->id }}" class="chip-soft" {{ $marcado }}>
                                            <span class="skill-premium-chip-pill">{{ $nombreSkillDinamico }}</span>
                                        </label>
                                    @endif
                                @endforeach
                                @if(!$hasSoft) <span class="empty-italic-text">{{ __('messages.lbl_no_skills_registered') }}</span> @endif
                            </div>
                        @endif
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<script>

// Hace que los cambios visuales ocurran de forma nativa en el texto seleccionado
function ejecutarComandoEditor(editorId, comando) {
    document.getElementById(editorId).focus();
    document.execCommand(comando, false, null);
    
    // Forzamos la actualización inmediata del input oculto tras formatear
    const inputId = editorId === 'lab-editor' ? 'lab-bio-hidden' : 'creator-bio-hidden';
    sincronizarEditorOculto(editorId, inputId);
}

// Sincroniza el HTML visual de la pantalla con el input de texto que viaja a Laravel
function sincronizarEditorOculto(editorId, inputId) {
    const htmlContenido = document.getElementById(editorId).innerHTML;
    document.getElementById(inputId).value = htmlContenido;
}

// 🪐 CIRCUITO DE INICIALIZACIÓN UNIFICADO (FUSIÓN PERFECTA)
document.addEventListener("DOMContentLoaded", function() {
    
    // 📝 1. TU LÓGICA DE BIOGRAFÍA EXISTENTE (Mantenida intacta)
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function() {
            if (document.getElementById('lab-editor')) {
                sincronizarEditorOculto('lab-editor', 'lab-bio-hidden');
            }
            if (document.getElementById('creator-editor')) {
                sincronizarEditorOculto('creator-editor', 'creator-bio-hidden');
            }
        });
    });

    // ⚙️ 2. NUEVA LÓGICA REACTIVA DE CHIPS DE HABILIDADES
    function recalcularContadoresSkillsDinamicos() {
        // Cuenta cuántas cajas técnicas (hard) y blandas (soft) están encendidas
        const totalHardChecked = document.querySelectorAll('.skills-chips-matrix input.chip-hard:checked').length;
        const totalSoftChecked = document.querySelectorAll('.skills-chips-matrix input.chip-soft:checked').length;
        
        // Localiza los indicadores de texto inyectados en los labels de tu Blade
        const elHard = document.getElementById('live-counter-hard');
        const elSoft = document.getElementById('live-counter-soft');
        
        // Pinta dinámicamente los totales
        if (elHard) elHard.textContent = `(${totalHardChecked})`;
        if (elSoft) elSoft.textContent = `(${totalSoftChecked})`;
    }

    // Ejecución analítica inicial para pintar los chips que ya estaban guardados en la BD
    recalcularContadoresSkillsDinamicos();

    // Vinculación dinámica: Escucha cada clic en los chips para actualizar los números en tiempo real
    document.querySelectorAll('.skills-chips-matrix input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', recalcularContadoresSkillsDinamicos);
    });

});

</script>
<div class="focus-glow-amarillo">

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
                    <small id="p2p-name-feedback" style="display: block; margin-top: 6px; font-size: 11px; color: #f1c40f; font-weight: 700; height: 14px;"></small>
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
                    <button type="button" class="btn-premium btn-amarillo-hub m-0 w-100" onclick="if(!this.closest('form').checkValidity()) { this.closest('form').reportValidity(); return; } confirmarAccion(event, '{{ __('messages.swal_confirm_p2p') }}', 'warning', '#f1c40f')">
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
                                            <button type="button" class="btn-premium btn-amarillo-hub m-0" 
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
                        <th class="text-right" style="width: 120px;">{{ __('messages.th_amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if(empty($misTransacciones) || count($misTransacciones) == 0)
                        <tr><td colspan="3" class="empty-state">{{ __('messages.empty_transactions') }}</td></tr>
                    @else
                        @foreach($misTransacciones as $tx)
                            <tr>
                                <td class="td-date-dim">{{ date('d M Y - H:i', strtotime($tx->created_at ?? $tx['created_at'])) }}</td>
                                <td class="td-description-text">
                                    {{ $tx->description ?? $tx['description'] }}
                                    @if(($tx->type ?? $tx['type']) === 'mint')
                                        <span class="tx-badge tx-badge-mint">{{ __('messages.badge_mint') }}</span>
                                    @elseif(($tx->type ?? $tx['type']) === 'escrow')
                                        <span class="tx-badge tx-badge-escrow">{{ __('messages.badge_escrow') }}</span>
                                    @elseif(($tx->type ?? $tx['type']) === 'info')
                                        <span class="tx-badge tx-badge-info">{{ __('messages.badge_info') }}</span>
                                    @endif
                                </td>
                                <td class="text-right tx-amount-value {{ ($tx->type ?? $tx['type']) == 'income' ? 'text-success-neon' : 'text-danger-neon' }}">
                                    {{ ($tx->type ?? $tx['type']) == 'income' ? '+' : '-' }}{{ number_format($tx->amount ?? $tx['amount'], 2) }} FC
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- SECCIÓN DE PERFIL Y HABILIDADES (Integrado al final de Mercado) --}}
    <div id="seccion-perfil-habilidades" class="premium-glass-card">
        <form action="{{ route('creator.update_profile') }}" method="POST" class="m-0">
            @csrf
            <div class="premium-glass-card-header">
                <h2 class="premium-glass-card-title m-0">👤 {{ __('messages.title_my_profile') }}</h2>
                <button type="submit" class="btn-premium btn-amarillo-hub m-0">💾 {{ __('messages.btn_save_profile') }}</button>
            </div>

            <div class="profile-panoramic-grid mt-20" style="display: grid; grid-template-columns: 1.5fr 1fr; gap: 25px;">
                {{-- Columna Izquierda: Bio, Redes y Ubicación --}}
                <div class="flex-col-gap-15" style="display: flex; flex-direction: column; gap: 15px;">
                    {{-- Biografía Profesional --}}
                    <div>
                        <label class="premium-label">{{ __('messages.lbl_my_bio') }}</label>
                        <textarea name="bio" rows="10" class="premium-textarea m-0" placeholder="{{ __('messages.ph_my_bio') }}" required>{{ $creator->bio }}</textarea>
                    </div>

                    {{-- Redes Sociales y Plataformas --}}
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
                            <input type="url" name="instagram_url" value="{{ auth()->user()->instagram_url }}" class="premium-input m-0" placeholder="📸 {{ __('messages.ph_instagram') }}">
                        </div>
                        <div>
                            <label class="premium-label">Fab Academy</label>
                            <input type="url" name="fab_academy_url" value="{{ auth()->user()->fab_academy_url }}" class="premium-input m-0" placeholder="🎓 {{ __('messages.ph_fab_academy') }}">
                        </div>
                        <div style="grid-column: span 2;">
                            <label class="premium-label">{{ __('messages.lbl_portfolio') ?? 'Portafolio / Web' }}</label>
                            <input type="url" name="social_portfolio" value="{{ $creator->social_portfolio }}" class="premium-input m-0" placeholder="🌐 {{ __('messages.ph_portfolio') }}">
                        </div>
                    </div>

                    {{-- Ubicación / Dirección --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; background: rgba(0,0,0,0.1); padding: 12px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.02);">
                        <div>
                            <label class="premium-label">{{ __('messages.lbl_city') }}</label>
                            <input type="text" name="city" value="{{ auth()->user()->city }}" class="premium-input m-0" placeholder="{{ __('messages.ph_city') }}">
                        </div>
                        <div>
                            <label class="premium-label">{{ __('messages.lbl_country') }}</label>
                            <input type="text" name="country" value="{{ auth()->user()->country }}" class="premium-input m-0" placeholder="{{ __('messages.ph_country') }}">
                        </div>
                    </div>
                </div>

                {{-- Columna Derecha: Selector de Habilidades --}}
                <div class="flex-col-gap-10">
                    <label class="premium-label">{{ __('messages.lbl_my_skills') }}</label>
                    <div class="skill-tags-wrapper" style="max-height: 340px; overflow-y: auto; background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.02);">
                        @if($catalogoSkills->isEmpty())
                            <span class="empty-italic-text">{{ __('messages.lbl_no_skills_registered') }}</span>
                        @else
                            @foreach($catalogoSkills as $skill)
                                @php 
                                    $esHard = ($skill->type === 'hard'); 
                                    $marcado = in_array($skill->id, $misSkillsIds) ? 'checked' : '';
                                @endphp
                                <label class="skill-tag-label" style="display: flex; align-items: center; gap: 8px; font-size: 13px; color: #fff; margin-bottom: 8px; cursor: pointer;">
                                   <input type="checkbox" name="skills[]" value="{{ $skill->id }}" {{ $marcado }} style="accent-color: {{ $esHard ? '#3498db' : '#f39c12' }}; width: 16px; height: 16px;">
                                    {{ $skill->name }} 
                                    <small style="color: {{ $esHard ? '#3498db' : '#f39c12' }}; font-weight: 600; font-size: 10px; text-transform: uppercase;">({{ $skill->type }})</small>
                                </label>
                            @endforeach
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
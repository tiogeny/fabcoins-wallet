<div class="focus-glow-amarillo">

    <div class="premium-glass-card">
        <h2 class="premium-glass-card-title">🪙 {{ __('messages.tokenise_title') }}</h2>
        <p class="premium-glass-card-subtitle">{{ __('messages.tokenise_desc') }}</p>

        @php $activosParaTokenizar = $misActivos->where('status', 'enlisted'); @endphp

        @if($activosParaTokenizar->isEmpty())
            <div class="empty-state-warning">
                <p class="m-0 text-neutral-muted">{{ __('messages.empty_tokenise') }}</p>
                <p class="mt-5px">{{ __('messages.empty_tokenise_sub') }}</p>
            </div>
        @else
            <form action="{{ route('lab.asset.tokenise') }}" method="POST">
                @csrf
                <div class="grid-tokenise-headers">
                    <div class="text-center"><input type="checkbox" id="select-all-boveda" onclick="conmutarTodosLosCheckboxes(this)" title="{{ __('messages.tooltip_select_all') ?? 'Seleccionar todos' }}"></div>
                    <label class="premium-label">{{ __('messages.th_asset_name') }}</label>
                    <label class="premium-label">{{ __('messages.th_capacity_year') }} <span class="fx-tooltip to-bottom tooltip-icon-warning">? <span class="fx-tooltip-card tooltip-card-warning">{{ __('messages.tooltip_real_capacity') }}</span></span></label>
                    <label class="premium-label">{{ __('messages.th_commitment_pct') }} <span class="fx-tooltip to-bottom tooltip-icon-warning">? <span class="fx-tooltip-card tooltip-card-warning">{{ __('messages.tooltip_commitment') }}</span></span></label>
                    <label class="premium-label">{{ __('messages.th_committed_qty') }} <span class="fx-tooltip to-bottom tooltip-icon-warning">? <span class="fx-tooltip-card tooltip-card-warning">{{ __('messages.tooltip_committed_qty_detail') }}</span></span></label>
                    <label class="premium-label">{{ __('messages.th_price_unit') }} <span class="fx-tooltip to-bottom tooltip-icon-warning">? <span class="fx-tooltip-card tooltip-card-warning">{{ __('messages.tooltip_unit_price') }}</span></span></label>
                    <label class="premium-label">{{ __('messages.th_estimated_fc') }} <span class="fx-tooltip to-bottom to-left tooltip-icon-warning">? <span class="fx-tooltip-card tooltip-card-warning">{{ __('messages.tooltip_val_comercial') }}</span></span></label>
                </div>

                <div id="contenedor-boveda-tokenizar">
                    @foreach($activosParaTokenizar as $asset)
                        @php
                            $precioSugerido = DB::table('global_catalog')->where('id', $asset->catalog_id)->value('suggested_price_fc') ?? 10;
                            $esTaller = ($asset->asset_type === 'service' && $asset->subcategory === 'workshop');
                            $unidadTexto = ($asset->asset_type === 'service' && $asset->subcategory === 'workshop') ? __('messages.unit_slots') : __('messages.unit_hours');
                        @endphp
                        <div class="fila-boveda-token row-tokenise-item" data-precio-base="{{ $precioSugerido }}" data-capacidad-base="{{ $asset->useful_life_hours }}" data-unidad="{{ $unidadTexto }}">
                            <div class="text-center"><input type="checkbox" name="transformar_activo[]" value="{{ $asset->id }}" class="check-activo-transformar" onchange="ejecutarCalculoEcuacion()"></div>
                            <div class="flex-align-gap-10">
                                @php $colorBadge = $asset->asset_type === 'machine' ? '#1abc9c' : ($asset->asset_type === 'service' ? '#3498db' : '#9b59b6'); @endphp
                                <span class="badge-semantic" style="background: {{ $colorBadge }};">
                                {{ $asset->custom_name }}</span>
                            </div>
                            <div><span class="token-numeric-value">{{ number_format($asset->useful_life_hours, 0) }} <small class="token-unit-small">{{ $unidadTexto }}</small></span></div>
                            <div>
                                @if($esTaller)
                                    <select name="percentage_committed[{{ $asset->id }}]" class="select-porcentaje-boveda premium-select m-0" onchange="ejecutarCalculoEcuacion()" readonly><option value="1.00" selected>{{ __('messages.pct_100') }}</option></select>
                                @else
                                    <select name="percentage_committed[{{ $asset->id }}]" class="select-porcentaje-boveda premium-select m-0" onchange="ejecutarCalculoEcuacion()"><option value="" selected>-- {{ __('messages.pct_select') }} --</option><option value="0.10">{{ __('messages.pct_10') }}</option><option value="0.20">{{ __('messages.pct_20') }}</option><option value="0.30">{{ __('messages.pct_30') }}</option><option value="0.40">{{ __('messages.pct_40') }}</option><option value="0.50">{{ __('messages.pct_50') }}</option></select>
                                @endif
                            </div>
                            <div><span class="pizarra-horas-netas token-numeric-value">0 <small class="token-unit-small">{{ $unidadTexto }}</small></span></div>
                            <div><input type="number" step="1" name="set_price_fc[{{ $asset->id }}]" value="{{ intval($precioSugerido) }}" class="input-precio-boveda premium-input text-center m-0" oninput="ejecutarCalculoEcuacion()"></div>
                            <div class="text-right pr-5"><span class="pizarra-subtotal-fc token-subtotal-value">0 FC</span></div>
                        </div>
                    @endforeach
                </div>

                <div class="tokenise-total-wrapper">
                    <div class="col-span-5"></div>
                    <div class="col-span-2 total-box-success">
                        <span class="total-box-label">{{ __('messages.lbl_total_to_emit') }}</span>
                        <span id="pizarra-total-boveda" class="total-box-value">0 FC</span>
                    </div>
                </div>

                <div class="flex-end-mt20">
                    <button type="submit" onclick="validarSeleccionBoveda(event)" class="btn-premium btn-amarillo-hub m-0">{{ __('messages.btn_execute_tokenise') }}</button>
                </div>
            </form>
        @endif
    </div>

    <div class="premium-glass-card">
        <h3 class="premium-glass-card-title">📈 {{ __('messages.rates_reg_title') }} <span class="fx-tooltip to-right tooltip-icon-warning">? <span class="fx-tooltip-card tooltip-card-warning">{{ __('messages.tooltip_rates_change') }}</span></span></h3>
        <p class="premium-glass-card-subtitle">{{ __('messages.rates_reg_desc') }}</p>

        @php $activosOperativos = $misActivos->where('status', 'active'); @endphp

        @if($activosOperativos->isEmpty())
            <p class="empty-italic-text">{{ __('messages.empty_active_rates') }}</p>
        @else
            <div class="flex-col-gap-10">
                @foreach($activosOperativos as $activoActivo)
                    @php 
                        $colorBadgeActivo = $activoActivo->asset_type === 'machine' ? '#1abc9c' : ($activoActivo->asset_type === 'service' ? '#3498db' : '#9b59b6'); 
                        $unidadRestanteLlave = ($activoActivo->asset_type === 'service' && $activoActivo->subcategory === 'workshop') ? 'messages.lbl_slots_remaining' : 'messages.lbl_hours_remaining'; 
                    @endphp
                    <div class="rate-item-row">
                        <div class="flex-align-gap-12">
                            <span class="badge-semantic" style="background: {{ $colorBadgeActivo }};">{{ __('messages.opt_'.$activoActivo->asset_type) }}</span>
                            <span class="rate-item-title">{{ $activoActivo->custom_name }}</span>
                            <span class="rate-item-subtitle">({{ number_format($activoActivo->useful_life_hours - $activoActivo->consumed_hours, 0) }} {{ __($unidadRestanteLlave) }})</span>
                        </div>
                        <form action="{{ route('lab.asset.updatePrice') }}" method="POST" class="form-inline-flex-10">
                            @csrf
                            <input type="hidden" name="asset_id" value="{{ $activoActivo->id }}">
                            <div class="input-with-badge-wrapper">
                                <input type="number" step="1" name="nuevo_precio" value="{{ intval($activoActivo->set_price_fc) }}" class="input-rate-update">
                            </div>
                            <button type="submit" onclick="confirmarAccion(event, '{{ __('messages.swal_confirm_price_change') }}', 'warning', '#f1c40f')" class="btn-back-minimal btn-rate-update">{{ __('messages.btn_update_rate') }}</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="premium-glass-card">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title">⚙️ {{ __('messages.title_reservations') }}</h3>
        </div>
        <div class="table-container">
            <table class="premium-data-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.th_date') }}</th>
                        <th>{{ __('messages.th_creator') }}</th>
                        <th>{{ __('messages.th_equipment') }}</th>
                        <th>{{ __('messages.th_time') }}</th>
                        <th>{{ __('messages.th_amount') }}</th>
                        <th style="width: 260px;">{{ __('messages.lbl_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($misReservas->isEmpty())
                        <tr><td colspan="6" class="empty-state">{{ __('messages.hist_empty') }}</td></tr>
                    @else
                        @foreach($misReservas as $res)
                            <tr>
                                <td class="td-date-dim">{{ date('d M Y', strtotime($res->created_at)) }}</td>
                                <td class="td-creator-name">
                                    {{-- 🔗 LINK AL PROFILE USANDO EL SLUG DEL CREADOR --}}
                                    <a href="{{ route('public.profile', $res->creator_slug ?? $res->creator_id) }}" target="_blank" class="text-blue-neon font-bold" style="text-decoration: none;">
                                        {{ $res->creator_name }} ↗️
                                    </a>
                                </td>
                                <td class="td-equipment">{{ $res->custom_name }}</td>
                                <td class="td-time-req">
                                    {{ $res->hours_requested }} hrs<br>
                                    <span class="text-date-highlight">📅 {{ __('messages.lbl_for_date') }} {{ date('d M Y', strtotime($res->reservation_date)) }}</span>
                                </td>
                                <td class="td-amount-gold">{{ number_format($res->total_fc, 0) }} FC</td>
                                <td>
                                    <div class="action-cell-flex-6">
                                        @if($res->status === 'pending')
                                            @php 
                                                // Corregido a creator_id según tu instrucción
                                                $tieneCreditoPendiente = DB::table('financing_agreements')
                                                    ->where('creator_id', $res->creator_id)
                                                    ->where('lab_id', auth()->id())
                                                    ->where('status', 'pending')
                                                    ->exists(); 
                                            @endphp

                                            @if($tieneCreditoPendiente)
                                                {{-- 🔒 REGLA ESTRICTA: Bloqueo total de las 3 acciones hasta evaluar crédito --}}
                                                <span class="status-text-warning" style="font-size: 11px; font-weight: 700; color: #f1c40f; letter-spacing: 0.3px;">
                                                    {{ __('messages.lbl_evaluate_credit_first') }}
                                                </span>
                                            @else
                                                {{-- Acciones liberadas si no requiere crédito o si ya se aprobó --}}
                                                <form action="{{ route('lab.order.approve') }}" method="POST" class="m-0">
                                                    @csrf <input type="hidden" name="order_id" value="{{ $res->id }}">
                                                    <button type="button" class="btn-back-minimal btn-min-approve" onclick="confirmarAccion(event, '{{ __('messages.swal_confirm_approve', ['asset' => $res->custom_name, 'creator' => $res->creator_name]) }}', 'success', '#2ecc71')">{{ __('messages.btn_approve') }}</button>
                                                </form>

                                                <button type="button" class="btn-back-minimal btn-min-reschedule" onclick="abrirModalReprogramar(this, {{ $res->id }}, '{{ date('Y-m-d', strtotime($res->reservation_date)) }}')">📅 {{ __('messages.btn_reschedule') }}</button>
                                                
                                                <form action="{{ route('lab.order.reject') }}" method="POST" class="m-0">
                                                    @csrf <input type="hidden" name="order_id" value="{{ $res->id }}">
                                                    <button type="button" class="btn-back-minimal btn-min-reject" onclick="confirmarAccion(event, '{{ __('messages.swal_confirm_reject') }}', 'error', '#e74c3c')">{{ __('messages.btn_reject') }}</button>
                                                </form>
                                            @endif
                                        @elseif($res->status === 'completed')
                                            <span class="status-text-approved">✓ {{ __('messages.status_approved_consumed') }}</span>
                                        @elseif($res->status === 'rescheduled')
                                            <span class="status-text-waiting">⏳ {{ __('messages.status_waiting_creator') }}</span>
                                        @else
                                            <span class="status-text-rejected">❌ {{ __('messages.status_rejected') }}</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div class="premium-glass-card">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title">🎓 {{ __('messages.title_isa_portfolio') }}</h3>
        </div>
        <div class="table-container">
            <table class="premium-data-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.th_creator') }}</th>
                        <th>{{ __('messages.th_concept') }}</th>
                        <th>{{ __('messages.th_amount') }}</th>
                        <th style="width: 180px;">{{ __('messages.th_progress') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($misFinanciamientos->isEmpty())
                        <tr><td colspan="4" class="empty-state">{{ __('messages.isa_portfolio_empty') }}</td></tr>
                    @else
                        @foreach($misFinanciamientos as $cf)
                            <tr>
                                <td>
                                    <a href="{{ route('public.profile', $cf->creator_slug ?? $cf->creator_id) }}" target="_blank" class="td-creator-name text-blue-neon" style="text-decoration: none;">
                                        {{ $cf->creator_name }} ↗️
                                    </a>
                                    <div class="td-creator-email">{{ $cf->creator_email }}</div>
                                </td>
                                <td class="td-concept-desc">{{ $cf->description }}</td>
                                <td class="td-amount-gold text-white-pure">{{ number_format($cf->amount_initial, 0) }} <small class="token-unit-small">FC</small></td>
                                <td>
                                    @if($cf->status === 'pending')
                                        <div style="display: flex; gap: 5px;">
                                            <form action="{{ route('lab.credit.approve') }}" method="POST" class="m-0">
                                                @csrf <input type="hidden" name="credit_id" value="{{ $cf->id }}">
                                                <button type="button" class="btn-back-minimal btn-min-approve" onclick="confirmarAccion(event, '{{ __('messages.swal_confirm_approve_credit', ['amount' => number_format($cf->amount_initial, 0), 'creator' => $cf->creator_name]) }}', 'success', '#2ecc71')">✅ {{ __('messages.btn_approve') }}</button>
                                            </form>
                                            <form action="{{ route('lab.credit.reject') }}" method="POST" class="m-0">
                                                @csrf <input type="hidden" name="credit_id" value="{{ $cf->id }}">
                                                <button type="button" class="btn-back-minimal btn-min-reject" onclick="confirmarAccion(event, '¿Rechazar esta solicitud de crédito?', 'error', '#e74c3c')">❌ {{ __('messages.btn_reject') }}</button>
                                            </form>
                                        </div>
                                    @else 
                                        @php $progreso = round((($cf->amount_initial - $cf->amount_remaining) / $cf->amount_initial) * 100); @endphp
                                        <div class="isa-progress-text">
                                            {{ $progreso }}% <span class="isa-progress-sub">{{ __('messages.lbl_recovered') }}</span>
                                        </div>
                                        <div class="isa-progress-bar-wrap">
                                            <div class="asset-progress-fill bg-warning-neon" style="width: {{ $progreso }}%;"></div>
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

    <div class="premium-glass-card mb-0">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title">📜 {{ __('messages.title_transactions') }}</h3>
            <form action="{{ route('lab.profile.export_csv') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn-back-minimal btn-export-excel">
                    📊 {{ __('messages.btn_export_excel') }}
                </button>
            </form>
        </div>

        <div class="table-container">
            <table class="premium-data-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.th_date') }}</th>
                        <th>{{ __('messages.th_description') }}</th>
                        <th style="text-align: right; width: 140px;">{{ __('messages.th_amount') }}</th>
                        <th style="text-align: right; width: 130px;">{{ __('messages.lbl_consumidos_bullet') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($misTransacciones->isEmpty())
                        <tr><td colspan="4" class="empty-state">{{ __('messages.empty_transactions') }}</td></tr>
                    @else
                        @foreach($misTransacciones as $tx)
                            <tr>
                                <td class="td-date-dim">{{ date('d M Y - H:i', strtotime($tx->created_at)) }}</td>
                                <td class="td-description-text" style="color: #fff; font-weight: 500;">
                                    {{ $tx->description }}
                                    
                                    @if($tx->type === 'mint')
                                        <span class="tx-badge" style="background: rgba(255, 255, 255, 0.08); color: #fff; border: 1px solid rgba(255,255,255,0.2);">{{ __('messages.badge_mint') }}</span>
                                    @elseif($tx->type === 'escrow')
                                        <span class="tx-badge" style="background: rgba(241, 196, 15, 0.1); color: #f1c40f; border: 1px solid #f1c40f;">{{ __('messages.lbl_frozen') }}</span>
                                    @elseif($tx->type === 'consumed')
                                        <span class="tx-badge" style="background: rgba(230, 126, 34, 0.1); color: #e67e22; border: 1px solid #e67e22;">{{ __('messages.badge_consumed') }}</span>
                                    @elseif($tx->type === 'info')
                                        <span class="tx-badge" style="background: rgba(149, 165, 166, 0.1); color: #95a5a6; border: 1px solid #95a5a6;">{{ __('messages.badge_info') }}</span>
                                    @elseif($tx->type === 'income')
                                        <span class="tx-badge" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71; border: 1px solid #2ecc71;">{{ __('messages.lbl_circulating') }}</span>
                                    @endif
                                </td>
                                
                                {{-- 🪙 COLUMNA 1: FLUJO LÍQUIDO --}}
                                <td class="text-right font-bold" style="font-family: 'Rajdhani', sans-serif; font-size: 16px;">
                                    @if($tx->type === 'info')
                                        <span style="color: #95a5a6; font-size: 11px; font-family: 'Inter', sans-serif; font-weight: 600; text-transform: uppercase;">{{ __('messages.badge_info') }}</span>
                                    @elseif($tx->type === 'mint')
                                        <span style="color: #fff;">+{{ number_format($tx->amount, 0) }} FC</span>
                                    @elseif($tx->type === 'escrow')
                                        <span style="color: #f1c40f;">-{{ number_format($tx->amount, 0) }} FC</span>
                                    @elseif($tx->type === 'income')
                                        <span style="color: #2ecc71;">+{{ number_format($tx->amount, 0) }} FC</span>
                                    @elseif($tx->type !== 'consumed')
                                        <span style="color: #3498db;">-{{ number_format($tx->amount, 0) }} FC</span>
                                    @else
                                        <span style="color: rgba(255,255,255,0.1); font-weight: 300;">—</span>
                                    @endif
                                </td>

                                {{-- 🎯 COLUMNA 2: VALOR QUEMADO (CONSUMIDO) --}}
                                <td class="text-right font-bold" style="font-family: 'Rajdhani', sans-serif; font-size: 16px;">
                                    @if($tx->type === 'consumed')
                                        <span style="color: #e67e22;">+{{ number_format($tx->amount, 0) }} FC</span>
                                    @else
                                        <span style="color: rgba(255,255,255,0.1); font-weight: 300;">—</span>
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

<script>
function conmutarTodosLosCheckboxes(master) {
    document.querySelectorAll('.check-activo-transformar').forEach(cb => { cb.checked = master.checked; });
    ejecutarCalculoEcuacion();
}
function ejecutarCalculoEcuacion() {
    let granTotal = 0;
    document.querySelectorAll('.fila-boveda-token').forEach(fila => {
        const checkbox = fila.querySelector('.check-activo-transformar');
        const pizarraLinea = fila.querySelector('.pizarra-subtotal-fc');
        const pizarraNetas = fila.querySelector('.pizarra-horas-netas');
        const unidad = fila.getAttribute('data-unidad');
        const capacidadBase = parseFloat(fila.getAttribute('data-capacidad-base')) || 0;
        const porcentaje = parseFloat(fila.querySelector('.select-porcentaje-boveda').value) || 0;
        const precioAjustado = parseFloat(fila.querySelector('.input-precio-boveda').value) || 0;

        const horasNetas = capacidadBase * porcentaje;
        pizarraNetas.innerHTML = Math.round(horasNetas) + ' <small class="token-unit-small">' + unidad + '</small>';

        pizarraLinea.classList.remove('text-muted-dark', 'text-warning-neon');

        if (!checkbox.checked) { pizarraLinea.classList.add('text-muted-dark'); pizarraLinea.textContent = '0 FC'; return; }
        
        const subtotal = horasNetas * precioAjustado;
        granTotal += subtotal;
        pizarraLinea.classList.add('text-warning-neon');
        pizarraLinea.textContent = Math.round(subtotal).toLocaleString('es-ES', { maximumFractionDigits: 0 }) + ' FC';
    });
    const pizarraGeneral = document.getElementById('pizarra-total-boveda');
    if (pizarraGeneral) {
        pizarraGeneral.classList.remove('text-muted-dark', 'text-success-neon');
        if (granTotal > 0) { 
            pizarraGeneral.classList.add('text-success-neon'); 
            pizarraGeneral.textContent = Math.round(granTotal).toLocaleString('es-ES', { maximumFractionDigits: 0 }) + ' FC'; 
        } else { 
            pizarraGeneral.classList.add('text-muted-dark'); 
            pizarraGeneral.textContent = '0 FC'; 
        }
    }
}
function validarSeleccionBoveda(event) {
    event.preventDefault();
    const totalMarcados = document.querySelectorAll('.check-activo-transformar:checked').length;
    if (totalMarcados === 0) {
        Swal.fire({ 
            title: "{{ __('messages.swal_no_assets_selected') }}", 
            text: "{{ __('messages.swal_no_assets_selected_desc') }}", 
            icon: 'warning', background: '#1a252f', color: '#fff', confirmButtonColor: '#3498db' 
        });
        return;
    }
    confirmarAccion(event, "{{ __('messages.swal_confirm_tokenise') }}", 'success', '#f1c40f');
}
document.addEventListener("DOMContentLoaded", function() { ejecutarCalculoEcuacion(); });

function abrirModalReprogramar(boton, orderId, fechaActual) {
    const formHtml = `
        <form id="form-reprogramar-${orderId}" action="{{ route('lab.order.reschedule') }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" value="${orderId}">
            <input type="date" name="new_date" value="${fechaActual}" min="${new Date().toISOString().split('T')[0]}" class="premium-input" style="color-scheme: dark; margin-top: 15px;" required>
        </form>
    `;

    Swal.fire({
        title: '📅 {{ __('messages.swal_reprogram_title') }}',
        text: '{{ __('messages.swal_reprogram_desc') }}',
        html: formHtml,
        background: '#1c2230',
        color: '#fff',
        showCancelButton: true,
        confirmButtonColor: '#f39c12',
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: '{{ __('messages.btn_send_proposal') }}',
        cancelButtonText: '{{ __('messages.swal_cancel') }}',
        preConfirm: () => {
            const dateInput = document.querySelector(`#form-reprogramar-${orderId} input[name="new_date"]`).value;
            if (!dateInput) { Swal.showValidationMessage('Debes seleccionar una fecha'); return false; }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById(`form-reprogramar-${orderId}`).submit();
        }
    });
}
</script>
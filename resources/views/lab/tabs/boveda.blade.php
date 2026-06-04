<!-- 🪙 MÓDULO ÚNICO: CONSOLA DE TRANSFORMACIÓN CON REJILLA FINANCIERA CORREGIDA -->
<div class="card" style="border: 1px solid rgba(255, 255, 255, 0.04); background: #1c2230; margin-bottom: 25px; padding: 24px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
    <h2 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 19px; color: #ffffff; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">🪙 {{ __('messages.tokenise_title') }}</h2>
    <p class="text-muted" style="font-size: 12.5px; margin-bottom: 25px; color: #a0aec0;">{{ __('messages.tokenise_desc') }}</p>

    @php
        $activosParaTokenizar = $misActivos->where('status', 'enlisted');
    @endphp

    @if($activosParaTokenizar->isEmpty())
        <div style="text-align: center; padding: 40px; color: #7f8c8d; background: #131722; border-radius: 8px; border: 1px dashed rgba(241, 196, 15, 0.15);">
            <p style="font-size: 13.5px; margin-bottom: 0; color: #cbd5e0;">{{ __('messages.empty_tokenise') }}</p>
            <p style="font-size: 11.5px; margin-top: 5px; color: #7f8c8d;">{{ __('messages.empty_tokenise_sub') }}</p>
        </div>
    @else
        <!-- 🔥 SOLUCIÓN: Apunta a la ruta POST real de Laravel -->
        <form action="{{ route('lab.asset.tokenise') }}" method="POST">
            @csrf
            
            <!-- CABECERA DE REJILLA MATEMÁTICA COHERENTE (7 Columnas Alineadas) -->
            <div style="display: grid; grid-template-columns: 40px 1.6fr 0.9fr 1.3fr 1fr 1fr 1.1fr; gap: 12px; margin-bottom: 12px; padding: 0 5px; align-items: center;">
                <div style="text-align: center;"><input type="checkbox" id="select-all-boveda" onclick="conmutarTodosLosCheckboxes(this)" title="Seleccionar todos"></div>
                <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('messages.th_asset_name') }}</label>
                
                <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 4px;">
                    {{ __('messages.th_capacity_year') }}
                    <span class="fx-tooltip to-bottom" style="color: #f1c40f;">? <span class="fx-tooltip-card" style="border-top-color: #f1c40f;">{{ __('messages.tooltip_real_capacity') }}</span></span>
                </label>
                
                <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 4px;">
                    {{ __('messages.th_commitment_pct') }}
                    <span class="fx-tooltip to-bottom" style="color: #f1c40f;">? <span class="fx-tooltip-card" style="border-top-color: #f1c40f;">{{ __('messages.tooltip_commitment') }}</span></span>
                </label>
                
                <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px;">
                    {{ __('messages.th_committed_qty') }}
                    <span class="fx-tooltip to-bottom" style="color: #f1c40f;">? <span class="fx-tooltip-card" style="border-top-color: #f1c40f;">{{ __('messages.tooltip_committed_qty_detail') }}</span></span>
                </label>
                
                <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 4px;">
                    {{ __('messages.th_price_unit') }}
                    <span class="fx-tooltip to-bottom" style="color: #f1c40f;">? <span class="fx-tooltip-card" style="border-top-color: #f1c40f;">{{ __('messages.tooltip_unit_price') }}</span></span>
                </label>
                
                <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; text-align: right; display: flex; align-items: center; justify-content: flex-end; gap: 4px;">
                    {{ __('messages.th_estimated_fc') }}
                    <span class="fx-tooltip to-bottom to-left" style="color: #f1c40f;">? <span class="fx-tooltip-card" style="border-top-color: #f1c40f;">{{ __('messages.tooltip_val_comercial') }}</span></span>
                </label>
            </div>

            <!-- FILAS OPERATIVAS REACTIVAS -->
            <div id="contenedor-boveda-tokenizar">
                @foreach($activosParaTokenizar as $asset)
                    @php
                        $precioSugerido = DB::table('global_catalog')->where('id', $asset->catalog_id)->value('suggested_price_fc') ?? 10;
                        $esTaller = ($asset->asset_type === 'service' && $asset->subcategory === 'workshop');
                        $unidadTexto = ($asset->asset_type === 'service' && $asset->subcategory === 'workshop') ? __('messages.unit_slots') : __('messages.unit_hours');
                    @endphp
                    
                    <div class="fila-boveda-token" data-precio-base="{{ $precioSugerido }}" data-capacidad-base="{{ $asset->useful_life_hours }}" data-unidad="{{ $unidadTexto }}" style="display: grid; grid-template-columns: 40px 1.6fr 0.9fr 1.3fr 1fr 1fr 1.1fr; gap: 12px; background: #131722; padding: 10px; border-radius: 8px; margin-bottom: 10px; align-items: center; border: 1px solid rgba(255,255,255,0.01);">
                        
                        <!-- 1. Checkbox desmarcado por defecto para seguridad -->
                        <div style="text-align: center;">
                            <input type="checkbox" name="transformar_activo[]" value="{{ $asset->id }}" class="check-activo-transformar" onchange="ejecutarCalculoEcuacionBeno()">
                        </div>

                        <!-- 2. Identidad Visual Coherente -->
                        <div style="display: flex; align-items: center; gap: 10px;">
                            @php $colorBadge = $asset->asset_type === 'machine' ? '#1abc9c' : ($asset->asset_type === 'service' ? '#3498db' : '#9b59b6'); @endphp
                            <span style="padding: 3px 6px; border-radius: 4px; font-size: 8.5px; font-weight: 800; color: white; background: {{ $colorBadge }}; text-transform: uppercase; letter-spacing: 0.5px;">
                                {{ __('messages.opt_'.$asset->asset_type) }}
                            </span>
                            <span style="color: #cbd5e0; font-weight: 500; font-size: 12.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 140px;">{{ $asset->custom_name }}</span>
                        </div>

                        <!-- 3. Capacidad Base (X) -->
                        <div>
                            <span style="color: #cbd5e0; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 14px;">
                                {{ number_format($asset->useful_life_hours, 0) }} 
                                <small style="font-family: 'Inter', sans-serif; font-size: 10px; color: #7f8c8d;">{{ $unidadTexto }}</small>
                            </span>
                        </div>

                        <!-- 4. Selector de Porcentaje Contrato -->
                        <div>
                            @if($esTaller)
                                <select name="percentage_committed[{{ $asset->id }}]" class="select-porcentaje-boveda" onchange="ejecutarCalculoEcuacionBeno()" style="width: 100%; font-weight: 700; margin-bottom: 0; background: #1a1e2a; border: 1px solid rgba(255,255,255,0.1); color: #f1c40f; height: 36px; border-radius: 6px; font-size: 12px;" readonly>
                                    <option value="1.00" selected>{{ __('messages.pct_100') }}</option>
                                </select>
                            @else
                                <select name="percentage_committed[{{ $asset->id }}]" class="select-porcentaje-boveda" onchange="ejecutarCalculoEcuacionBeno()" style="width: 100%; font-weight: 700; margin-bottom: 0; background: #1c2230; border: 1px solid rgba(255,255,255,0.06); color: #fff; height: 36px; border-radius: 6px; font-size: 12px;">
                                    <option value="" selected>-- {{ __('messages.pct_select') }} --</option>
                                    <option value="0.10">{{ __('messages.pct_10') }}</option>
                                    <option value="0.20">{{ __('messages.pct_20') }}</option>
                                    <option value="0.30">{{ __('messages.pct_30') }}</option>
                                    <option value="0.40">{{ __('messages.pct_40') }}</option>
                                    <option value="0.50">{{ __('messages.pct_50') }}</option>
                                </select>
                            @endif
                        </div>

                        <!-- 5. NUEVO: Cantidad Neta Comprometida (Y) -->
                        <div>
                            <span class="pizarra-horas-netas" style="color: #cbd5e0; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 14px;">0 <small style="font-family: 'Inter', sans-serif; font-size: 10px; color: #7f8c8d;">{{ $unidadTexto }}</small></span>
                        </div>

                        <!-- 6. Precio Ajustable Precargado -->
                        <div>
                            <input type="number" step="1" name="set_price_fc[{{ $asset->id }}]" value="{{ intval($precioSugerido) }}" class="input-precio-boveda" style="width: 100%; margin-bottom: 0; background: #1c2230; border: 1px solid rgba(255,255,255,0.06); color: #f1c40f; height: 36px; border-radius: 6px; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 14px; text-align: center; padding: 0 8px;">
                        </div>

                        <!-- 7. Subtotal en Vivo Amarillo -->
                        <div style="text-align: right; padding-right: 5px;">
                            <span class="pizarra-subtotal-fc" style="color: #7f8c8d; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 14.5px;">0 FC</span>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- TABLERO DE SUMATORIA COMPLETA ALINEADO EXACTAMENTE A LA DERECHA -->
            <div style="display: grid; grid-template-columns: 40px 1.6fr 0.9fr 1.3fr 1fr 1fr 1.1fr; gap: 12px; margin-top: 20px; align-items: center; padding-right: 5px;">
                <div style="grid-column: span 5;"></div>
                <div style="text-align: right; grid-column: span 2; background: rgba(46, 204, 113, 0.02); padding: 12px; border-radius: 10px; border: 1px solid rgba(46, 204, 113, 0.1);">
                    <span style="font-size: 10px; color: #7f8c8d; text-transform: uppercase; display: block; letter-spacing: 0.5px; margin-bottom: 2px;">{{ __('messages.lbl_total_to_emit') }}</span>
                    <span id="pizarra-total-boveda" style="font-family: 'Rajdhani', sans-serif; font-weight: 800; font-size: 24px; color: #4a5568;">0 FC</span>
                </div>
            </div>

            <div style="display: flex; justify-content: flex-end; margin-top: 20px;">
                <button type="submit" onclick="validarSeleccionBoveda(event)" class="btn-logout-v2" style="background: #f1c40f; color: #111111; border: 1px solid #f1c40f; padding: 0 28px; height: 42px; font-size: 12px; font-weight: 700; border-radius: 6px; margin: 0; box-shadow: 0 4px 15px rgba(241, 196, 15, 0.15);">{{ __('messages.btn_execute_tokenise') }}</button>
            </div>
        </form>
    @endif
</div>

<div class="card" style="border: 1px solid rgba(255, 255, 255, 0.04); background: #1c2230; padding: 24px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); margin-top: 30px;">
    <h3 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 18px; color: #ffffff; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 8px;">
        📈 {{ __('messages.rates_reg_title') }}
        <span class="fx-tooltip to-right">? 
            <span class="fx-tooltip-card" style="border-top-color: #f1c40f;">{{ __('messages.tooltip_rates_change') }}</span>
        </span>
    </h3>
    <p class="text-muted" style="font-size: 12.5px; margin-bottom: 20px; color: #a0aec0;">{{ __('messages.rates_reg_desc') }}</p>

    @php
        $activosOperativos = $misActivos->where('status', 'active');
    @endphp

    @if($activosOperativos->isEmpty())
        <p style="font-size: 12.5px; color: #4a5568; font-style: italic; margin-bottom: 0;">{{ __('messages.empty_active_rates') }}</p>
    @else
        <div style="display: flex; flex-direction: column; gap: 10px;">
            @foreach($activosOperativos as $activoActivo)
                @php 
                    $colorBadgeActivo = $activoActivo->asset_type === 'machine' ? '#1abc9c' : ($activoActivo->asset_type === 'service' ? '#3498db' : '#9b59b6');
                    $unidadRestanteLlave = ($activoActivo->asset_type === 'service' && $activoActivo->subcategory === 'workshop') ? 'messages.lbl_slots_remaining' : 'messages.lbl_hours_remaining';
                @endphp
                <div style="display: flex; justify-content: space-between; align-items: center; background: #131722; padding: 12px 18px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.01);">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span style="padding: 3px 6px; border-radius: 4px; font-size: 8.5px; font-weight: 800; color: white; background: {{ $colorBadgeActivo }}; text-transform: uppercase; letter-spacing: 0.5px;">
                            {{ __('messages.opt_'.$activoActivo->asset_type) }}
                        </span>
                        <span style="font-size: 13px; color: #cbd5e0; font-weight: 600;">{{ $activoActivo->custom_name }}</span>
                        <span style="font-size: 11px; color: #7f8c8d;">({{ number_format($activoActivo->useful_life_hours - $activoActivo->consumed_hours, 0) }} {{ __($unidadRestanteLlave) }})</span>
                    </div>
                    
                    <form action="{{ route('lab.asset.updatePrice') }}" method="POST" style="margin: 0; display: flex; align-items: center; gap: 10px;">
                        @csrf
                        <input type="hidden" name="asset_id" value="{{ $activoActivo->id }}">
                        <div style="position: relative; display: flex; align-items: center;">
                            <input type="number" step="1" name="nuevo_precio" value="{{ intval($activoActivo->set_price_fc) }}" style="width: 95px; height: 34px; background: #1c2230; border: 1px solid rgba(255,255,255,0.06); color: #f1c40f; font-weight: 700; border-radius: 6px; padding: 0 8px; margin-bottom: 0; text-align: center; font-family: 'Rajdhani', sans-serif; font-size: 14px;">
                        </div>
                        <button type="submit" onclick="confirmarAccion(event, '{{ __('messages.swal_confirm_price_change') }}', 'warning', '#f1c40f')" class="btn-back-minimal" style="padding: 0 14px; height: 34px; font-size: 11px; display: flex; align-items: center; background: rgba(241, 196, 15, 0.04); border-color: rgba(241, 196, 15, 0.2); color: #f1c40f; font-weight: 700; border-radius: 6px;">{{ __('messages.btn_update_rate') }}</button>
                    </form>
                </div>
            @endforeach
        </div>
    @endif
</div>

<!-- 🧪 MOTOR MATEMÁTICO REACTIVO AUTOMATIZADO -->
<script>
function conmutarTodosLosCheckboxes(master) {
    document.querySelectorAll('.check-activo-transformar').forEach(cb => {
        cb.checked = master.checked;
    });
    ejecutarCalculoEcuacionBeno();
}

function ejecutarCalculoEcuacionBeno() {
    let granTotal = 0;
    
    document.querySelectorAll('.fila-boveda-token').forEach(fila => {
        const checkbox = fila.querySelector('.check-activo-transformar');
        const pizarraLinea = fila.querySelector('.pizarra-subtotal-fc');
        const pizarraNetas = fila.querySelector('.pizarra-horas-netas');
        const unidad = fila.getAttribute('data-unidad');
        
        const capacidadBase = parseFloat(fila.getAttribute('data-capacidad-base')) || 0;
        const porcentaje = parseFloat(fila.querySelector('.select-porcentaje-boveda').value) || 0;
        const precioAjustado = parseFloat(fila.querySelector('.input-precio-boveda').value) || 0;
        
        // 1. Cálculo de Horas Comprometidas Reales (Y)
        const horasNetas = capacidadBase * porcentaje;
        pizarraNetas.innerHTML = Math.round(horasNetas) + ' <small style="font-family: \'Inter\', sans-serif; font-size: 10px; color: #7f8c8d;">' + unidad + '</small>';

        // 2. Si la casilla no está marcada, no suma al total macroeconómico
        if (!checkbox.checked) {
            pizarraLinea.style.color = '#4a5568';
            pizarraLinea.textContent = '0 FC';
            return;
        }

        // Ecuación Contable Directa: Y * Precio Ajustado
        const subtotal = horasNetas * precioAjustado;
        granTotal += subtotal;

        pizarraLinea.style.color = '#f1c40f';
        pizarraLinea.textContent = subtotal.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 0 }) + ' FC';
    });

    const pizarraGeneral = document.getElementById('pizarra-total-boveda');
    if (pizarraGeneral) {
        if (granTotal > 0) {
            pizarraGeneral.style.color = '#2ecc71'; // Se enciende en verde cuando hay colateral marcado
            pizarraGeneral.textContent = granTotal.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 0 }) + ' FC';
        } else {
            pizarraGeneral.style.color = '#4a5568';
            pizarraGeneral.textContent = '0 FC';
        }
    }
}

// 🚀 AÑADE ESTA FUNCIÓN DENTRO DEL <script> DE BOVEDA:
function validarSeleccionBoveda(event) {
    event.preventDefault();
    
    // Contamos cuántas casillas reales están marcadas en este instante
    const totalMarcados = document.querySelectorAll('.check-activo-transformar:checked').length;
    
    if (totalMarcados === 0) {
        Swal.fire({
            title: "{{ __('messages.swal_no_assets_selected') }}",
            text: "{{ __('messages.swal_no_assets_selected_desc') }}",
            icon: 'warning',
            background: '#1a252f',
            color: '#fff',
            confirmButtonColor: '#3498db'
        });
        return; // Frena el flujo en seco
    }
    
    // Si pasa la auditoría, invoca el SweetAlert de confirmación legítimo
    confirmarAccion(event, '¿Deseas transformar el colateral seleccionado en FabCoins oficiales de presupuesto? Quedarán asentados permanentemente en el Libro contable.', 'success', '#f1c40f');
}

// Inicialización reactiva al renderizar
document.addEventListener("DOMContentLoaded", function() {
    ejecutarCalculoEcuacionBeno();
});
</script>
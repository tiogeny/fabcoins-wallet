<div class="card" style="border: 1px solid rgba(255, 255, 255, 0.04); background: #1c2230; margin-bottom: 25px; padding: 24px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
    <h2 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 19px; color: #ffffff; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">🏢 {{ __('messages.register_asset_title') }}</h2>
    <p class="text-muted" style="font-size: 12.5px; margin-bottom: 25px; color: #a0aec0;">{{ __('messages.register_asset_desc') }}</p>

    <script>
        const catalogoGlobalActivar = {!! json_encode(DB::table('global_catalog')->get()) !!};
    </script>

    <form action="{{ route('lab.asset.store') }}" method="POST">
        @csrf
        
        <div style="display: grid; grid-template-columns: 1.4fr 1.6fr 2fr 1.2fr 40px; gap: 15px; margin-bottom: 10px; padding: 0 5px;">
            <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('messages.lbl_category') }}</label>
            <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('messages.lbl_global_cat') }}</label>
            <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px;">{{ __('messages.lbl_specific_name') }}</label>
            <label style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                {{ __('messages.lbl_capacity_avail') }}
                <span class="fx-tooltip">?
                    <span class="fx-tooltip-card">
                        <strong>{{ __('messages.tooltip_capacity_title') }}</strong><br>
                        • <strong>{{ __('messages.opt_machine') }} / {{ __('messages.opt_lab') }}:</strong> {{ __('messages.tooltip_capacity_machine') }}<br>
                        • <strong>{{ __('messages.opt_service') }}:</strong> {{ __('messages.tooltip_capacity_service') }}
                    </span>
                </span>
            </label>
            <div></div>
        </div>

        <div id="contenedor-filas-enlistar">
            <div class="row-token" style="display: grid; grid-template-columns: 1.4fr 1.6fr 2fr 1.2fr 40px; gap: 15px; background: #131722; padding: 10px; border-radius: 8px; margin-bottom: 10px; align-items: center; border: 1px solid rgba(255,255,255,0.01);">
                
                <div>
                    <select name="asset_type[]" class="select-macro-tipo-activar" onchange="adaptarFilaEcosistema(this)" style="width: 100%; font-weight: 700; margin-bottom: 0; background: #1c2230; border: 1px solid rgba(255,255,255,0.06); color: #fff; height: 38px; border-radius: 6px; font-size: 12px;" required>
                        <option value="">-- {{ __('messages.opt_select') }} --</option>
                        <option value="machine">⚙️ {{ __('messages.opt_machine') }}</option>
                        <option value="service">🧠 {{ __('messages.opt_service') }}</option>
                        <option value="lab">🏢 {{ __('messages.opt_lab') }}</option>
                    </select>
                </div>

                <div>
                    <select name="catalog_id[]" class="select-catalogo-activar" onchange="actualizarUnidadFila(this)" style="width: 100%; margin-bottom: 0; background: #1c2230; border: 1px solid rgba(255,255,255,0.06); color: #fff; height: 38px; border-radius: 6px; font-size: 12px;" disabled required>
                        <option value="">-- {{ __('messages.opt_select') }} --</option>
                    </select>
                </div>

                <div>
                    <input type="text" name="custom_name[]" class="input-modelo-activar" placeholder="{{ __('messages.ph_select_category') }}" style="width: 100%; margin-bottom: 0; background: #1c2230; border: 1px solid rgba(255,255,255,0.06); color: #fff; height: 38px; border-radius: 6px; font-size: 12.5px;" required>
                </div>

                <div style="position: relative; display: flex; align-items: center;">
                    <input type="number" name="quantity_declared[]" class="input-capacidad-activar" placeholder="{{ __('messages.ph_capacity_base') }}" min="1" style="width: 100%; margin-bottom: 0; background: #1c2230; border: 1px solid rgba(255,255,255,0.06); color: #fff; height: 38px; border-radius: 6px; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 14px; padding-right: 48px;" required>
                    <span class="badge-unidad-dinamica" style="position: absolute; right: 10px; font-size: 10px; color: #1abc9c; font-weight: 700; text-transform: uppercase;">Und</span>
                </div>

                <div class="espaciador-borrar-enlistar" style="text-align: center;"></div>
            </div>
        </div>

        <div style="display:flex; gap:15px; align-items:center; margin-top:20px;">
            <button type="button" onclick="agregarFilaEnlistar()" class="btn-back-minimal" style="border: 1px solid rgba(255,255,255,0.12); color: #ffffff; padding: 0 16px; height: 38px; font-size: 11px; background: transparent; border-radius: 6px;">+ {{ __('messages.btn_add_more') }}</button>
            <button type="submit" class="btn-logout-v2" style="background: #1abc9c; color: #ffffff; border: 1px solid #1abc9c; padding: 0 20px; height: 38px; font-size: 12px; font-weight: 700; display: flex; align-items: center; justify-content: center; border-radius: 6px; margin: 0;">💾 {{ __('messages.btn_save_inventory') }}</button>
        </div>
    </form>
</div>

<div class="card" style="background: #1c2230; border: 1px solid rgba(255, 255, 255, 0.04); padding: 24px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
    <h2 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 18px; color: #ffffff; margin-bottom: 18px; text-transform: uppercase; letter-spacing: 0.5px;">📦 {{ __('messages.inv_title') }}</h2>
    <div class="table-container">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.08); text-align: left;">
                    <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase;">{{ __('messages.th_category') }}</th>
                    <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase;">{{ __('messages.th_asset') }}</th>
                    <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase; width: 220px;">{{ __('messages.th_avail_capacity') }}</th>
                    <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase; width: 165px;">{{ __('messages.th_status') }}</th>
                    <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase; text-align: center; width: 80px;">ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @if($misActivos->isEmpty())
                    <tr><td colspan="5" style="text-align:center; padding:50px; color:#7f8c8d; font-size: 13px;">{{ __('messages.inv_empty') }}</td></tr>
                @else
                    @foreach($misActivos as $activo)
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.02); transition: background 0.2s;">
                            <td style="padding: 14px 12px;">
                                @php
                                    $colorBadge = $activo->asset_type === 'machine' ? '#1abc9c' : ($activo->asset_type === 'service' ? '#3498db' : '#9b59b6');
                                @endphp
                                <span style="padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: 800; color: white; background: {{ $colorBadge }}; text-transform: uppercase; letter-spacing: 0.5px;">
                                    {{ __('messages.opt_'.$activo->asset_type) }}
                                </span>
                            </td>
                            
                            <td style="padding: 14px 12px;">
                                <span style="color: #cbd5e0; font-weight: 500; font-size: 13px;">{{ $activo->custom_name }}</span>
                            </td>
                            
                            <td style="padding: 14px 12px;">
                                <div style="color: #ffffff; font-family: 'Rajdhani', sans-serif; font-size: 13.5px; font-weight: 700; margin-bottom: 5px; width: 100%;">
                                    <span>{{ number_format($activo->useful_life_hours - $activo->consumed_hours, 0) }} / {{ number_format($activo->useful_life_hours, 0) }}</span>
                                    <span style="font-size: 10px; color: #a0aec0; font-weight: 500; font-family: 'Inter', sans-serif;">
                                        {{ $activo->asset_type === 'service' && $activo->subcategory === 'workshop' ? 'Cupos' : 'Hrs' }}
                                    </span>
                                    @if($activo->tokenization_pct > 0)
                                        <span style="font-size: 11px; color: #f1c40f; font-weight: 500; font-family: 'Inter', sans-serif; float: right; margin-right: 15px;">
                                            ({{ number_format(($activo->useful_life_hours * $activo->tokenization_pct) / 100, 0) }} Tokenizadas)
                                        </span>
                                    @endif
                                </div>
                                @php
                                    $pct = ($activo->useful_life_hours > 0) ? (($activo->useful_life_hours - $activo->consumed_hours) / $activo->useful_life_hours) * 100 : 0;
                                    $cBar = $pct > 50 ? '#1abc9c' : ($pct > 20 ? '#f1c40f' : '#e74c3c');
                                @endphp
                                <div style="width: 90%; height: 3px; background: rgba(255,255,255,0.06); border-radius: 2px; overflow: hidden;">
                                    <div style="width: {{ $pct }}%; height: 100%; background: {{ $cBar }}; border-radius: 2px;"></div>
                                </div>
                            </td>
                            
                            <td style="padding: 14px 12px;">
                                <div style="display: flex; align-items: center; gap: 12px; width: 100%;">
                                    @if($activo->status === 'enlisted')
                                        <span style="padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; background: rgba(160,174,192,0.08); color: #a0aec0; letter-spacing: 0.3px; white-space: nowrap;">{{ __('messages.status_enlisted') }}</span>
                                        <button type="button" onclick="abrirWorkspaceHubPersistente('workspace-tokenizar')" style="background: transparent; border: none; color: #f1c40f; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 11px; cursor: pointer; padding: 0; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; display: inline-flex; align-items: center;" title="Ir al panel de acuñación monetaria">🪙 TOKENIZAR</button>
                                    @elseif($activo->status === 'active')
                                        <span style="padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; background: rgba(46,204,113,0.1); color: #2ecc71; letter-spacing: 0.3px;">{{ __('messages.status_operative') }}</span>
                                    @else
                                        <span style="padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; background: rgba(231,76,60,0.1); color: #e74c3c; letter-spacing: 0.3px;">{{ __('messages.status_retired') }}</span>
                                    @endif
                                </div>
                            </td>

                            <td style="padding: 14px 12px; text-align: center;">
                                @if($activo->status === 'enlisted')
                                    <form action="{{ route('lab.asset.destroy', $activo->id) }}" method="POST" style="margin:0;">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" onclick="confirmarAccion(event, '{{ __('messages.swal_ays_detail') }}', 'warning', '#e74c3c')" style="background: transparent; border: none; color: #e74c3c; font-size: 16px; cursor: pointer; padding: 4px; line-height: 1;">
                                            &times;
                                        </button>
                                    </form>
                                @else
                                    <span style="font-size: 12px; color: #4a5568;">🔒</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

<script>
function adaptarFilaEcosistema(selectMacro) {
    const fila = selectMacro.closest('.row-token');
    const tipo = selectMacro.value;
    const selectCatalogo = fila.querySelector('.select-catalogo-activar');
    const inputModelo = fila.querySelector('.input-modelo-activar');
    const inputCapacidad = fila.querySelector('.input-capacidad-activar');
    const badgeUnidad = fila.querySelector('.badge-unidad-dinamica');
    
    selectCatalogo.innerHTML = '<option value="">-- {{ __("messages.opt_select") }} --</option>';
    
    if (!tipo) {
        selectCatalogo.disabled = true;
        inputModelo.placeholder = "Selecciona categoría...";
        inputCapacidad.placeholder = "Cant. Base";
        badgeUnidad.textContent = "Und";
        return;
    }
    
    if (tipo === 'machine') {
        inputModelo.placeholder = "Ej: Ultimaker S5";
        inputCapacidad.placeholder = "Horas (Ej: 1000)";
        badgeUnidad.textContent = "Hrs";
    } else if (tipo === 'service') {
        inputModelo.placeholder = "Ej: Fab Academy / Mentoría";
        inputCapacidad.placeholder = "Selecciona especialidad...";
        badgeUnidad.textContent = "Und";
    } else if (tipo === 'lab') {
        inputModelo.placeholder = "Ej: Estación CNC";
        inputCapacidad.placeholder = "Horas (Ej: 100)";
        badgeUnidad.textContent = "Hrs";
    }

    const itemsFiltrados = catalogoGlobalActivar.filter(item => item.asset_type === tipo);
    itemsFiltrados.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.generic_name;
        selectCatalogo.appendChild(opt);
    });
    
    selectCatalogo.disabled = false;
}

// 🔥 SEPARADOR SEMÁNTICO: Discrimina entre Talleres (Cupos) y Consultorías (Horas)
function actualizarUnidadFila(selectCatalogo) {
    const fila = selectCatalogo.closest('.row-token');
    const inputCapacidad = fila.querySelector('.input-capacidad-activar');
    const badgeUnidad = fila.querySelector('.badge-unidad-dinamica');
    const selectMacro = fila.querySelector('.select-macro-tipo-activar').value;
    
    if (selectMacro !== 'service') return;
    
    const opcionSeleccionada = selectCatalogo.options[selectCatalogo.selectedIndex].text.toLowerCase();
    
    if (opcionSeleccionada.includes('taller') || opcionSeleccionada.includes('workshop') || opcionSeleccionada.includes('programa')) {
        inputCapacidad.placeholder = "Cupos (Ej: 15)";
        badgeUnidad.textContent = "Cupos";
    } else {
        inputCapacidad.placeholder = "Horas (Ej: 40)";
        badgeUnidad.textContent = "Hrs";
    }
}

function agregarFilaEnlistar() {
    const contenedor = document.getElementById('contenedor-filas-enlistar');
    const filaOriginal = document.querySelector('#contenedor-filas-enlistar .row-token');
    const nuevaFila = filaOriginal.cloneNode(true);
    
    nuevaFila.querySelector('.select-macro-tipo-activar').value = '';
    const selectCat = nuevaFila.querySelector('.select-catalogo-activar');
    selectCat.innerHTML = '<option value="">-- {{ __("messages.opt_select") }} --</option>';
    selectCat.disabled = true;
    
    const inputMod = nuevaFila.querySelector('.input-modelo-activar');
    inputMod.value = '';
    inputMod.placeholder = "{{ __('messages.ph_select_category') }}";

    const inputCap = nuevaFila.querySelector('.input-capacidad-activar');
    inputCap.value = '';
    inputCap.placeholder = "{{ __('messages.ph_capacity_base') }}";
    
    // Inyección de botón de eliminación respetando el espacio de la 5ta columna grid
    const espaciador = nuevaFila.querySelector('.espaciador-borrar-enlistar');
    if (espaciador) {
        espaciador.innerHTML = '<button type="button" onclick="this.closest(\'.row-token\').remove()" style="background: transparent; color: #e74c3c; border: none; font-size: 18px; cursor: pointer; padding: 0; line-height: 1; display: flex; align-items: center; justify-content: center; width: 100%; height: 38px;">&times;</button>';
    }
    
    contenedor.appendChild(nuevaFila);
}
</script>
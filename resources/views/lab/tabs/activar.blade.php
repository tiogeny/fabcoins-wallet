<!-- 💎 SECCIÓN 1: FORMULARIO DE ENLISTAMIENTO PURO -->
<div class="card" style="border: 1px dashed var(--c-green); background: rgba(46, 204, 113, 0.01); margin-bottom: 25px;">
    <h2>🏢 {{ __('messages.register_asset_title') ?? 'Declarar Infraestructura y Capacidad' }}</h2>
    <p class="text-muted font-12" style="margin-top: -8px; margin-bottom: 20px;">
        {{ __('messages.register_asset_desc') ?? 'Enlista las máquinas, servicios o espacios de tu laboratorio para incorporarlos al inventario antes de su valorización monetaria.' }}
    </p>

    {{-- Script de Catálogo para el filtrado dependiente nativo --}}
    <script>
        const catalogoGlobalActivar = {!! json_encode(DB::table('global_catalog')->get()) !!};
    </script>

    <form action="{{ route('lab.asset.store') ?? '#' }}" method="POST">
        @csrf
        <div id="contenedor-filas-enlistar">
            <div class="row-token" style="display: flex; gap: 12px; align-items: flex-end; background: #1c242c; padding: 16px; border-radius: 12px; margin-bottom: 12px;">
                
                <!-- Macro Categoría -->
                <div style="flex: 1; min-width: 160px;">
                    <label class="font-10 font-bold text-muted text-uppercase" style="display:block; margin-bottom:6px;">{{ __('messages.lbl_category') ?? 'Categoría Eje' }}</label>
                    <select name="asset_type[]" class="select-macro-tipo-activar" onchange="filtrarCatalogoActivar(this)" style="width: 100%; font-weight: bold;" required>
                        <option value="">-- {{ __('messages.opt_select') ?? 'Selecciona' }} --</option>
                        <option value="machine">⚙️ {{ __('messages.opt_machine') ?? 'Máquinas / Equipos' }}</option>
                        <option value="service">🧠 {{ __('messages.opt_service') ?? 'Servicios (Mentorías / Capacitaciones)' }}</option>
                        <option value="workshop">🎓 {{ __('messages.opt_workshop') ?? 'Talleres / Cursos formativos' }}</option>
                        <option value="space">🏢 {{ __('messages.opt_space') ?? 'Labs / Estaciones de Trabajo' }}</option>
                    </select>
                </div>

                <!-- Catálogo Global Dependiente -->
                <div style="flex: 1; min-width: 200px;">
                    <label class="font-10 font-bold text-muted text-uppercase" style="display:block; margin-bottom:6px;">{{ __('messages.lbl_global_cat') ?? 'Especialidad Global' }}</label>
                    <select name="catalog_id[]" class="select-catalogo-activar" style="width: 100%;" disabled required>
                        <option value="">-- {{ __('messages.opt_select') ?? 'Selecciona' }} --</option>
                    </select>
                </div>

                <!-- Nombre Personalizado de Fábrica -->
                <div style="flex: 1; min-width: 200px;">
                    <label class="font-10 font-bold text-muted text-uppercase" style="display:block; margin-bottom:6px;">{{ __('messages.lbl_specific_name') ?? 'Nombre Específico / Modelo' }}</label>
                    <input type="text" name="custom_name[]" placeholder="Ej: Ultimaker S5 / Experto CNC" style="width: 100%; margin-bottom:0;" required>
                </div>

                <!-- Capacidad Declarada -->
                <div style="width: 130px;">
                    <label class="font-10 font-bold text-muted text-uppercase" style="display:block; margin-bottom:6px;">{{ __('messages.lbl_capacity_declared') ?? 'Capacidad Base' }}</label>
                    <input type="number" name="quantity_declared[]" placeholder="Hrs / Cupos" min="1" style="width: 100%; margin-bottom:0;" required>
                </div>

                <!-- Espaciador para botón borrar filas clonadas -->
                <div class="espaciador-borrar-enlistar" style="width: 24px;"></div>
            </div>
        </div>

        <div style="display:flex; gap:15px; align-items:center; margin-top:20px;">
            <button type="button" onclick="agregarFilaEnlistar()" class="btn-back-minimal" style="border-color: rgba(255,255,255,0.15); color: #fff;">+ {{ __('messages.btn_add_more') ?? 'Añadir otra línea' }}</button>
            <button type="submit" class="btn-mint" style="background: var(--c-green); color: white;">💾 {{ __('messages.btn_save_inventory') ?? 'ENLISTAR EN INVENTARIO' }}</button>
        </div>
    </form>
</div>

<!-- 📦 SECCIÓN 2: BITÁCORA DEL INVENTARIO GENERAL -->
<div class="card">
    <h2>📦 {{ __('messages.inv_title') ?? 'Inventario Completo e Infraestructura' }}</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>{{ __('messages.th_category') ?? 'Categoría' }}</th>
                    <th>{{ __('messages.th_asset') ?? 'Activo Declarado' }}</th>
                    <th>{{ __('messages.th_avail_capacity') ?? 'Capacidad Declarada' }}</th>
                    <th>{{ __('messages.th_status') ?? 'Estado Financiero' }}</th>
                </tr>
            </thead>
            <tbody>
                @if($misActivos->isEmpty())
                    <tr><td colspan="4" style="text-align:center; padding:40px; color:#7f8c8d;">{{ __('messages.inv_empty') ?? 'No tienes infraestructura registrada en este laboratorio.' }}</td></tr>
                @else
                    @foreach($misActivos as $activo)
                        <tr>
                            <td>
                                @php
                                    // Asignación de colores corporativos unificados
                                    $colorBadge = '#7f8c8d';
                                    if($activo->asset_type === 'machine') $colorBadge = '#1abc9c';
                                    elseif(in_array($activo->asset_type, ['service', 'workshop'])) $colorBadge = '#3498db';
                                    elseif($activo->asset_type === 'space') $colorBadge = '#9b59b6';
                                @endphp
                                <span style="padding: 4px 10px; border-radius: 8px; font-size: 10px; font-weight: bold; color: white; background: {{ $colorBadge }}; text-transform: uppercase;">
                                    {{ __('messages.opt_'.$activo->asset_type) ?? $activo->asset_type }}
                                </span>
                            </td>
                            <td>
                                <strong>{{ $activo->custom_name }}</strong>
                            </td>
                            <td>
                                {{ number_format($activo->useful_life_hours - $activo->consumed_hours, 0) }} / {{ number_format($activo->useful_life_hours, 0) }} 
                                <span class="text-muted" style="font-size: 11px;">Hrs/Cupos</span>
                            </td>
                            <td>
                                @if($activo->status === 'enlisted')
                                    <span class="tag-status" style="background: rgba(255,255,255,0.1); color: #bdc3c7;">● {{ __('messages.status_enlisted') ?? 'Enlistado (Sin emitir)' }}</span>
                                @elseif($activo->status === 'active')
                                    <span class="tag-status" style="background: rgba(46, 204, 113, 0.1); color: #2ecc71;">● {{ __('messages.status_operative') ?? 'Tokenizado (Activo)' }}</span>
                                @else
                                    <span class="tag-status" style="background: rgba(231, 76, 60, 0.1); color: #e74c3c;">● {{ __('messages.status_retired') ?? 'De Baja (Penalizado)' }}</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>
    </div>
</div>

{{-- 🧠 SCRIPTS DE DINÁMICA DE FORMULARIOS FILTRADOS DE ACTIVAR --}}
<script>
function filtrarCatalogoActivar(selectMacro) {
    const fila = selectMacro.closest('.row-token');
    const tipoSeleccionado = selectMacro.value;
    const selectCatalogo = fila.querySelector('.select-catalogo-activar');
    
    selectCatalogo.innerHTML = '<option value="">-- {{ __("messages.opt_select") ?? "Selecciona" }} --</option>';
    
    if (!tipoSeleccionado) {
        selectCatalogo.disabled = true;
        return;
    }
    
    const itemsFiltrados = catalogoGlobalActivar.filter(item => item.asset_type === tipoSeleccionado);
    
    itemsFiltrados.forEach(item => {
        const opt = document.createElement('option');
        opt.value = item.id;
        opt.textContent = item.generic_name;
        selectCatalogo.appendChild(opt);
    });
    
    selectCatalogo.disabled = false;
}

function agregarFilaEnlistar() {
    const contenedor = document.getElementById('contenedor-filas-enlistar');
    const filaOriginal = document.querySelector('#contenedor-filas-enlistar .row-token');
    const nuevaFila = filaOriginal.cloneNode(true);
    
    nuevaFila.querySelectorAll('input').forEach(input => input.value = '');
    const selectCatalogo = nuevaFila.querySelector('.select-catalogo-activar');
    selectCatalogo.innerHTML = '<option value="">-- {{ __("messages.opt_select") ?? "Selecciona" }} --</option>';
    selectCatalogo.disabled = true;
    
    const espaciador = nuevaFila.querySelector('.espaciador-borrar-enlistar');
    if (espaciador) {
        espaciador.outerHTML = '<button type="button" class="btn-remove" onclick="this.closest(\'.row-token\').remove()" style="background:transparent; color:#e74c3c; border:none; font-size:22px; cursor:pointer; padding:0; margin-bottom:2px;">&times;</button>';
    }
    
    contenedor.appendChild(nuevaFila);
}
</script>
<div class="focus-glow-verde">
    <div class="premium-glass-card">
        <h2 class="premium-glass-card-title">🏢 {{ __('messages.register_asset_title') }}</h2>
        <p class="premium-glass-card-subtitle">{{ __('messages.register_asset_desc') }}</p>

        <script>
            const catalogoGlobalActivar = {!! json_encode(DB::table('global_catalog')->get()) !!};
        </script>

        <form action="{{ route('lab.asset.store') }}" method="POST">
            @csrf
            
            <div class="grid-headers-enlistar">
                <label class="premium-label">{{ __('messages.lbl_category') }}</label>
                <label class="premium-label">{{ __('messages.lbl_global_cat') }}</label>
                <label class="premium-label">{{ __('messages.lbl_specific_name') }}</label>
                <label class="premium-label">{{ __('messages.lbl_capacity_avail') }}
                    <span class="fx-tooltip to-bottom to-left">?
                        <span class="fx-tooltip-card" style="border-top: 2px solid #1abc9c;">
                            <strong>{{ __('messages.tooltip_capacity_title') }}</strong><br>
                            • <strong>{{ __('messages.opt_machine') }} / {{ __('messages.opt_lab') }}:</strong> {{ __('messages.tooltip_capacity_machine') }}<br>
                            • <strong>{{ __('messages.opt_service') }}:</strong> {{ __('messages.tooltip_capacity_service') }}
                        </span>
                    </span>
                </label>
                <div></div>
            </div>

            <div id="contenedor-filas-enlistar">
                <div class="row-token row-token-enlistar">
                    <div>
                        <select name="asset_type[]" class="premium-select select-macro-tipo-activar" onchange="adaptarFilaEcosistema(this)" required>
                            <option value="">-- {{ __('messages.opt_select') }} --</option>
                            <option value="machine">⚙️ {{ __('messages.opt_machine') }}</option>
                            <option value="service">🧠 {{ __('messages.opt_service') }}</option>
                            <option value="lab">🏢 {{ __('messages.opt_lab') }}</option>
                        </select>
                    </div>

                    <div>
                        <select name="catalog_id[]" class="premium-select select-catalogo-activar" onchange="actualizarUnidadFila(this)" disabled required>
                            <option value="">-- {{ __('messages.opt_select') }} --</option>
                        </select>
                    </div>

                    <div>
                        <input type="text" name="custom_name[]" class="premium-input input-modelo-activar" placeholder="{{ __('messages.ph_select_category') }}" required>
                    </div>

                    <div class="input-with-badge-wrapper">
                        <input type="number" name="quantity_declared[]" class="premium-input input-capacidad-activar" placeholder="{{ __('messages.ph_capacity_base') }}" min="1" required>
                        <span class="badge-unidad-dinamica">Und</span>
                    </div>

                    <div class="espaciador-borrar-enlistar text-center-wrapper"></div>
                </div>
            </div>

            <div class="form-actions-row">
                <button type="button" onclick="agregarFilaEnlistar()" class="btn-back-minimal">+ {{ __('messages.btn_add_more') }}</button>
                <button type="submit" class="btn-premium btn-verde-hub">💾 {{ __('messages.btn_save_inventory') }}</button>
            </div>
        </form>
    </div>

    <div class="premium-glass-card">
        <div class="premium-glass-card-header">
            <h2 class="premium-glass-card-title">📦 {{ __('messages.inv_title') }}</h2>
            <span class="badge-alert-neon">
                🔥 {{ __('messages.lbl_total_burned_kpi') }}: <strong>{{ number_format($totalHistoricoQuemado, 0) }} FC</strong>
            </span>
        </div>
        
        <div class="table-container">
            <table class="premium-data-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.th_category') }}</th>
                        <th>{{ __('messages.th_asset') }}</th>
                        <th style="width: 220px;">{{ __('messages.th_avail_capacity') }}</th>
                        <th style="width: 165px;">{{ __('messages.th_status') }}</th>
                        <th style="text-align: center; width: 80px;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @if($misActivos->isEmpty())
                        <tr><td colspan="5" class="empty-state">{{ __('messages.inv_empty') }}</td></tr>
                    @else
                        @foreach($misActivos as $activo)
                            <tr>
                                <td>
                                    <span class="badge-semantic badge-{{ $activo->asset_type }}">
                                        {{ __('messages.opt_'.$activo->asset_type) }}
                                    </span>
                                </td>
                                
                                <td>
                                    <span>{{ $activo->custom_name }}</span>
                                </td>
                                
                                <td>
                                    <div class="capacity-progress-wrapper">
                                        <span>{{ number_format($activo->useful_life_hours - $activo->consumed_hours, 0) }} / {{ number_format($activo->useful_life_hours, 0) }}</span>
                                        <span class="capacity-progress-unit">
                                            {{ $activo->asset_type === 'service' && $activo->subcategory === 'workshop' ? 'Cupos' : 'Hrs' }}
                                        </span>
                                        @if($activo->tokenization_pct > 0)
                                            <span class="capacity-progress-tokenized">
                                                ({{ number_format(($activo->useful_life_hours * $activo->tokenization_pct) / 100, 0) }} Tokenizadas)
                                            </span>
                                        @endif
                                    </div>
                                    @php
                                        $pct = ($activo->useful_life_hours > 0) ? (($activo->useful_life_hours - $activo->consumed_hours) / $activo->useful_life_hours) * 100 : 0;
                                        $claseBarra = $pct > 50 ? 'asset-progress-high' : ($pct > 20 ? 'asset-progress-med' : 'asset-progress-low');
                                    @endphp
                                    <div class="asset-progress-bar">
                                        <div class="asset-progress-fill {{ $claseBarra }}" style="width: {{ $pct }}%;"></div>
                                    </div>
                                    @if($activo->consumed_hours > 0)
                                        <div class="capacity-progress-burned">
                                            <span>🔥 {{ number_format($activo->consumed_hours, 0) }} {{ $activo->asset_type === 'service' && $activo->subcategory === 'workshop' ? __('messages.unit_slots') : __('messages.unit_hours') }} {{ __('messages.lbl_status_consumed') }}</span>
                                        </div>
                                    @endif
                                </td>
                                
                                <td>
                                    <div class="action-cell-flex">
                                        @if($activo->status === 'enlisted')
                                            <span class="badge-semantic badge-status-enlisted">{{ __('messages.status_enlisted') }}</span>
                                            <button type="button" onclick="abrirHubPersistente('hub-tokenizar')" class="btn-text-amarillo" title="Ir al panel de acuñación monetaria">🪙 TOKENIZAR</button>
                                        @elseif($activo->status === 'active')
                                            <span class="badge-semantic badge-status-operative">{{ __('messages.status_operative') }}</span>
                                        @else
                                            <span class="badge-semantic badge-status-retired">{{ __('messages.status_retired') }}</span>
                                        @endif
                                    </div>
                                </td>

                                <td class="text-center">
                                    @if($activo->status === 'enlisted')
                                        <form action="{{ route('lab.asset.destroy', $activo->id) }}" method="POST" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" onclick="confirmarAccion(event, '{{ __('messages.swal_ays_detail') }}', 'warning', '#e74c3c')" class="btn-delete-icon">&times;</button>
                                        </form>
                                    @else
                                        <span class="empty-state">🔒</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div id="seccion-perfil-mapa" class="premium-glass-card mt-25">
        <form action="{{ route('lab.update_profile') }}" method="POST" class="m-0">
            @csrf
            <div class="premium-glass-card-header">
                <h2 class="premium-glass-card-title">👤 {{ __('messages.title_bio_links') }}</h2>
                <button type="submit" class="btn-premium btn-verde-hub">💾 {{ __('messages.btn_save_profile') }}</button>
            </div>

            <div class="profile-panoramic-grid">
                <div>
                    <label class="premium-label">{{ __('messages.lbl_tell_creators') }}</label>
                    <textarea name="bio" rows="8" class="premium-textarea">{{ $lab->bio }}</textarea>
                </div>
                <div class="social-links-grid-activar">
                    <input type="url" name="social_fabacademy" value="{{ $lab->social_fabacademy }}" placeholder="🎓 URL Fab Academy">
                    <input type="url" name="social_linkedin" value="{{ $lab->social_linkedin }}" placeholder="🔗 URL LinkedIn">
                    <input type="url" name="social_github" value="{{ $lab->social_github }}" placeholder="🐙 URL GitHub">
                    <input type="url" name="social_portfolio" value="{{ $lab->social_portfolio }}" placeholder="🌐 URL Web / Portafolio">
                    <input type="url" name="social_instagram" value="{{ $lab->social_instagram }}" placeholder="📸 URL Instagram">
                </div>
            </div>

            <div class="map-section-container">
                <div>
                    <label class="premium-label">{{ __('messages.lbl_city_country') }}</label>
                    <div class="address-input-wrapper">
                        <input type="text" name="address" id="address-input" value="{{ $lab->address }}" placeholder="{{ __('messages.ph_map_search') }}" class="premium-input" required>
                        <button type="button" id="btn-search-map" class="btn-premium btn-verde-hub">🔍 {{ __('messages.btn_search_map') }}</button>
                    </div>
                </div>
                <input type="hidden" name="latitude" id="lat-input" value="{{ $lab->latitude }}">
                <input type="hidden" name="longitude" id="lng-input" value="{{ $lab->longitude }}">
                <div id="lab-map" class="map-container"></div>
            </div>
        </form>
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
        inputModelo.placeholder = "{{ __('messages.ph_select_category') }}";
        inputCapacidad.placeholder = "{{ __('messages.ph_capacity_base') }}";
        badgeUnidad.textContent = "{{ __('messages.lbl_unit_generic') }}";
        return;
    }
    
    if (tipo === 'machine') {
        inputModelo.placeholder = "{{ __('messages.ph_model_machine') }}";
        inputCapacidad.placeholder = "{{ __('messages.ph_capacity_machine') }}";
        badgeUnidad.textContent = "{{ __('messages.unit_hours') }}";
    } else if (tipo === 'service') {
        inputModelo.placeholder = "{{ __('messages.ph_model_service') }}";
        inputCapacidad.placeholder = "{{ __('messages.ph_capacity_service') }}";
        badgeUnidad.textContent = "{{ __('messages.lbl_unit_generic') }}";
    } else if (tipo === 'lab') {
        inputModelo.placeholder = "{{ __('messages.ph_model_lab') }}";
        inputCapacidad.placeholder = "{{ __('messages.ph_capacity_lab') }}";
        badgeUnidad.textContent = "{{ __('messages.unit_hours') }}";
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
        inputCapacidad.placeholder = "{{ __('messages.unit_slots') }} (Ej: 15)";
    badgeUnidad.textContent = "{{ __('messages.unit_slots') }}";
    } else {
        inputCapacidad.placeholder = "{{ __('messages.ph_capacity_machine') }}";
        badgeUnidad.textContent = "{{ __('messages.unit_hours') }}";
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
        espaciador.innerHTML = '<button type="button" onclick="this.closest(\'.row-token\').remove()" class="btn-delete-icon flex-center-38">&times;</button>';
    }
    
    contenedor.appendChild(nuevaFila);
}

function abrirHubPersistente(hubId) {
        sessionStorage.setItem('active_lab_hub', hubId);
        
        document.querySelectorAll('.hub-section').forEach(sec => {
            sec.style.display = 'none';
            sec.classList.remove('active');
        });
        
        const homeHub = document.getElementById('main-home-hub-view');
        if (homeHub) homeHub.style.display = 'none';
        
        const target = document.getElementById(hubId);
        if (target) {
            target.style.display = 'block';
            setTimeout(() => target.classList.add('active'), 50);
        }
    }

// 🗺️ MOTOR GEOGRÁFICO AUTÓNOMO REPARADO
document.addEventListener("DOMContentLoaded", function() {
    // Asegurar la inyección dinámica de la hoja de estilos de Leaflet para evitar paneles rotos
    if (!document.getElementById('leaflet-css-cdn')) {
        let link = document.createElement('link');
        link.id = 'leaflet-css-cdn';
        link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
        document.head.appendChild(link);
    }

    let lat = {{ $lab->latitude ?: '-12.046374' }};
    let lng = {{ $lab->longitude ?: '-77.042793' }};

    var map = L.map('lab-map', { zoomControl: false }).setView([lat, lng], 14);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19
    }).addTo(map);

    var labIcon = L.divIcon({
        className: 'custom-lab-pin',
        html: '<div class="leaflet-custom-pin-icon">🏭</div>',
        iconSize: [32, 32],
        iconAnchor: [16, 32]
    });

    var marker = L.marker([lat, lng], {draggable: true, icon: labIcon}).addTo(map);

    marker.on('dragend', function (e) {
        document.getElementById('lat-input').value = marker.getLatLng().lat;
        document.getElementById('lng-input').value = marker.getLatLng().lng;
    });

    // 🚀 INYECTOR DE RECTIFICACIÓN GEOMÉTRICA (Solución definitiva para pestañas ocultas)
    function forzarRedibujadoDelMapa() {
        if (document.getElementById('hub-activar').style.display !== 'none') {
            setTimeout(() => { 
                map.invalidateSize();
                map.setView([marker.getLatLng().lat, marker.getLatLng().lng], 14);
            }, 150);
        }
    }

    // Escuchar clics en los Hubs para redibujar
    document.querySelectorAll('.card-activar-neon, .lab-profile-trigger').forEach(btn => {
        btn.addEventListener('click', forzarRedibujadoDelMapa);
    });

    // Ejecución de respaldo al arranque si la sesión persistente inicia directo en este Hub
    setTimeout(forzarRedibujadoDelMapa, 500);

    // Buscador Nominatim
    const btnSearch = document.getElementById('btn-search-map');
    const addressInput = document.getElementById('address-input');

    if(btnSearch && addressInput) {
        btnSearch.addEventListener('click', function() {
            const query = addressInput.value.trim();
            if(!query) return;

            btnSearch.innerHTML = "⏳";
            btnSearch.disabled = true;

            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    btnSearch.innerHTML = "🔍 {{ __('messages.btn_search_map') }}";
                    btnSearch.disabled = false;

                    if(data && data.length > 0) {
                        const nLat = data[0].lat;
                        const nLng = data[0].lon;

                        map.setView([nLat, nLng], 14);
                        marker.setLatLng([nLat, nLng]);

                        document.getElementById('lat-input').value = nLat;
                        document.getElementById('lng-input').value = nLng;
                        map.invalidateSize();
                    }
                }).catch(() => {
                    btnSearch.innerHTML = "🔍 BUSCAR";
                    btnSearch.disabled = false;
                });
        });
    }
});
</script>
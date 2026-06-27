<div class="focus-glow-yellow">
    <div class="premium-glass-card">
        <h2 class="premium-glass-card-title">🗺️ {{ __('messages.map_explorer_title') }}</h2>
        <p class="premium-glass-card-subtitle">{{ __('messages.map_explorer_desc') }}</p>
        
        <div class="market-search-bar">
            <input type="text" id="creator-search-input" placeholder="{{ __('messages.ph_search_district') }}" class="premium-input m-0">
            <button type="button" id="btn-creator-search" class="btn-premium btn-yellow-hub m-0 w-auto">🔍 {{ __('messages.btn_search') }}</button>
        </div>
        
        <div id="creator-map" class="map-container-380"></div>
    </div>

    <div class="premium-glass-card">
        <div class="premium-glass-card-header">
            <h2 class="premium-glass-card-title m-0">🏭 {{ __('messages.market_capacity_title') }}</h2>
            <button type="button" id="btn-reset-market-filter" class="btn-back-minimal btn-reset-filter" onclick="restablecerFiltroTotalMercado()">🔄 {{ __('messages.btn_clear_filter') }}</button>
        </div>
        <p class="premium-glass-card-subtitle">{{ __('messages.market_capacity_desc') }}</p>
        
        <div class="flex-col-gap-15 mb-20">
            <select id="filter-cat" onchange="filtrarCatalogoMercadoVivo()" class="premium-select m-0">
                <option value="all">⚙️ {{ __('messages.opt_all_tech') }}</option>
                <option value="machine">{{ __('messages.opt_machine') }}</option>
                <option value="service">{{ __('messages.opt_service') }}</option>
                <option value="lab">{{ __('messages.opt_lab') }}</option>
            </select>
            <input type="text" id="filter-text" placeholder="{{ __('messages.ph_search_asset') }}" onkeyup="filtrarCatalogoMercadoVivo()" class="premium-input m-0">
        </div>

        <div class="creator-asset-grid mb-20">
            @forelse($recursosMercado as $r)
                @php $disp = $r->useful_life_hours - $r->consumed_hours; @endphp
                <div class="creator-asset-card" data-category="{{ $r->asset_type }}" data-lab-id="{{ $r->lab_id }}" data-search="{{ strtolower($r->lab_name . ' ' . $r->custom_name . ' ' . $r->display_name) }}">
                    
                    <div>
                        <div class="creator-asset-header">
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                <a href="{{ route('public.profile', $r->lab_slug ?? $r->lab_owner_id) }}" target="_blank" class="asset-lab-badge" style="text-decoration: none; color: #3498db; font-weight: 700;">
                                    🏭 {{ $r->lab_name }} ↗️
                                </a>
                                @if(!empty($r->lab_address) || !empty($r->address))
                                    <span class="asset-address-text" style="margin-top: 0; font-size: 10px;">📍 {{ $r->lab_address ?? $r->address }}</span>
                                @endif
                            </div>
                            <span class="price-tag td-amount-gold" data-unit-price="{{ $r->set_price_fc }}" style="color: #2ecc71;">{{ number_format($r->set_price_fc, 2) }} FC/h</span>
                        </div>
                        
                        <h3 class="asset-display-title asset-title-large" style="margin-bottom: 6px;">{{ $r->custom_name }}</h3>
                        
                        @php 
                            $bgBadge = '#7f8c8d';
                            if($r->asset_type === 'machine') $bgBadge = '#1abc9c'; 
                            elseif($r->asset_type === 'service') $bgBadge = '#3498db'; 
                            elseif(in_array($r->asset_type, ['lab', 'space', 'workshop'])) $bgBadge = '#9b59b6'; 
                        @endphp
                        <span class="asset-type-badge" style="background: {{ $bgBadge }}; color: white; padding: 4px 12px; border-radius: 20px; font-size: 10px; font-weight: 700; display: inline-block; margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.3px;">
                            {{ $r->display_name }}
                        </span>

                        <div class="asset-capacity-wrap" style="margin-top: 5px;">
                            <span class="asset-capacity-label">{{ __('messages.lbl_avail_capacity') }} {{ number_format($disp, 1) }}h</span>
                            <div class="asset-bar-bg">
                                <div class="asset-bar-fill" style="width: {{ ($r->useful_life_hours > 0) ? ($disp / $r->useful_life_hours) * 100 : 0 }}%;"></div>
                            </div>
                        </div>
                        </div>

                        <form action="{{ route('creator.book_asset') }}" method="POST" class="m-0">
                        @csrf 
                        <input type="hidden" name="asset_id" value="{{ $r->id }}">

                        {{-- 🎯 DETECCIÓN INTELIGENTE: Si el servicio se llama Taller, Academy, Curso o Diplomado, actúa por Cupos --}}
                        @php 
                            $nombreLimpio = strtolower($r->custom_name . ' ' . $r->display_name);
                            $esCursoEDu = false;
                            foreach(['academy', 'taller', 'curso', 'diplomado', 'clase', 'bootcamp'] as $palabra) {
                                if (str_contains($nombreLimpio, $palabra)) {
                                    $esCursoEDu = true;
                                    break;
                                }
                            }
                            $esTaller = ($r->asset_type === 'workshop') || ($r->asset_type === 'service' && $esCursoEDu);
                        @endphp
                        <div class="form-reserve-row" style="align-items: center; gap: 6px; grid-template-columns: {{ $esTaller ? '1.5fr 1fr' : '1.4fr 0.8fr 1fr' }};">
                            @if(!$esTaller)
                                <div style="position: relative; width: 100%; height: 36px;">
                                    <span class="calendar-icon-overlay-sm" style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; z-index: 5; font-size: 12px;">📅</span>
                                    <input type="date" name="reservation_date" required min="{{ date('Y-m-d') }}" class="input-date-sm">
                                </div>
                            @else
                                {{-- Si es taller la fecha ya viene en el título, inyectamos la de hoy oculta para no romper el 'required' del controlador --}}
                                <input type="hidden" name="reservation_date" value="{{ date('Y-m-d') }}">
                            @endif
                            
                            <input type="number" name="hours" 
                                   step="{{ $esTaller ? '1' : '0.5' }}" 
                                   min="{{ $esTaller ? '1' : '0.5' }}" 
                                   max="{{ $disp }}" 
                                   placeholder="{{ $esTaller ? (__('messages.ph_spots') ?? 'Cupos') : 'Hrs' }}" 
                                   required class="input-hours-sm" style="padding: 0 4px;">
                                   
                            <button type="submit" class="btn-premium btn-yellow-hub btn-reserve-sm" onclick="interceptarCalculoReserva(event, this)">
                                {{ $esTaller ? (__('messages.btn_register_workshop') ?? 'Inscribirse') : __('messages.btn_reserve') }}
                            </button>
                        </div>
                        </form>
                </div>
            @empty
                <div class="empty-state-warning">
                    <p class="m-0 text-neutral-muted">{{ __('messages.market_empty') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="premium-glass-card">
        <h2 class="premium-glass-card-title">📋 {{ __('messages.monitor_title') }}</h2>
        <p class="premium-glass-card-subtitle">{{ __('messages.monitor_desc') }}</p>

        <div class="table-container">
            <table class="premium-data-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.th_date') }}</th>
                        <th>{{ __('messages.th_equipment') }}</th>
                        <th>{{ __('messages.th_lab') }}</th>
                        <th>{{ __('messages.th_total_cost') }}</th>
                        <th>{{ __('messages.th_status') }}</th>
                        <th class="text-center" style="width: 180px;">{{ __('messages.lbl_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($misReservas->isEmpty())
                        <tr><td colspan="6" class="empty-state">{{ __('messages.monitor_empty') }}</td></tr>
                    @else
                        @foreach($misReservas as $res)
                            <tr>
                                <td class="td-date-dim">
                                    {{ date('d M Y', strtotime($res->created_at)) }}<br>
                                    <span class="text-date-highlight" style="white-space: nowrap;">📅 {{ date('d M Y', strtotime($res->reservation_date)) }}</span>
                                </td>
                                <td>
                                    <div class="td-creator-name">{{ $res->custom_name }}</div>
                                    <div class="td-creator-email">⏱️ {{ $res->hours_requested }} hrs</div>
                                </td>
                                <td class="td-equipment">🏭 {{ $res->lab_name }}</td>
                                <td class="td-amount-gold">{{ number_format($res->total_fc, 0) }} FC</td>
                                <td>
                                    @if($res->status === 'pending')
                                        <span class="badge-ghost-warning">⏳ {{ __('messages.status_waiting') }}</span>
                                    @elseif($res->status === 'completed')
                                        <span class="badge-ghost-success">✓ {{ __('messages.status_approved') }}</span>
                                    @elseif($res->status === 'rescheduled')
                                        <span class="badge-ghost-info">⚠️ {{ __('messages.status_rescheduled') }}</span>
                                    @else
                                        <span class="badge-ghost-danger">❌ {{ __('messages.status_rejected') }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($res->status === 'rescheduled')
                                        <div style="display: flex; gap: 6px; justify-content: center;">
                                            <form action="{{ route('creator.accept_date') }}" method="POST" class="m-0">
                                                @csrf <input type="hidden" name="order_id" value="{{ $res->id }}">
                                                <button type="submit" class="btn-back-minimal btn-min-approve">{{ __('messages.btn_accept') }}</button>
                                            </form>
                                            <form action="{{ route('creator.reject_date') }}" method="POST" class="m-0">
                                                @csrf <input type="hidden" name="order_id" value="{{ $res->id }}">
                                                <button type="submit" class="btn-back-minimal btn-min-reject">{{ __('messages.btn_cancel') }}</button>
                                            </form>
                                        </div>
                                    @elseif($res->status === 'completed' && empty($res->is_reviewed))
                                        <button type="button" class="btn-back-minimal btn-min-eval-gold" onclick="abrirModalReputacion({{ $res->id }}, {{ $res->lab_owner_id }}, '{{ $res->lab_name }}', '{{ $res->custom_name }}', '{{ $res->asset_type ?? 'machine' }}')">⭐ {{ __('messages.btn_rate_service') }}</button>
                                    @elseif($res->status === 'completed' && !empty($res->is_reviewed))
                                        {{-- 🚀 REPARADO: Muestra una confirmación limpia y alineada al estándar del Lab --}}
                                        <span class="status-text-approved">✓ {{ __('messages.lbl_rating_sent') ?? 'Calificado' }}</span>
                                    @else
                                        <span class="status-text-rejected">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    {{-- 📦 PLANTILLA MAESTRA DE EVALUACIÓN RECTIFICADA (Categoría con color) --}}
    <template id="review-modal-template">
        <form id="form-evaluar-laboratorio" action="{{ route('creator.rate_lab') }}" method="POST">
            @csrf
            <input type="hidden" name="order_id" id="swal-order-id">
            <input type="hidden" name="lab_id" id="swal-lab-id">

            <div class="modal-info-box grid-mission-inputs">
                <div>
                    <div class="modal-rating-label">{{ __('messages.th_lab') }}</div>
                    <div id="swal-pizarra-nombre-lab" class="modal-creator-name text-white-pure"></div>
                </div>
                <div>
                    {{-- 🟢 REPARADO: La categoría (Máquina/Servicio/Lab) ahora es la dueña del Badge de Color --}}
                    <div class="modal-rating-label-container">
                        <span id="swal-pizarra-tipo-recurso" class="badge-semantic"></span>
                    </div>
                    <div id="swal-pizarra-nombre-activo" class="modal-creator-name text-white-pure"></div>
                </div>
            </div>

            <div class="rating-stars-row">
                <label class="modal-rating-label">{{ __('messages.lbl_general_rating') }}</label>
                <div class="star-rating-cyber">
                    <input type="radio" id="lab-star5" name="rating" value="5" checked><label for="lab-star5">★</label>
                    <input type="radio" id="lab-star4" name="rating" value="4"><label for="lab-star4">★</label>
                    <input type="radio" id="lab-star3" name="rating" value="3"><label for="lab-star3">★</label>
                    <input type="radio" id="lab-star2" name="rating" value="2"><label for="lab-star2">★</label>
                    <input type="radio" id="lab-star1" name="rating" value="1"><label for="lab-star1">★</label>
                </div>
            </div>

            <div class="mb-22">
                <textarea name="comment" placeholder="{{ __('messages.ph_review_comment') }}" class="premium-textarea m-0 h-90" required></textarea>
            </div>
        </form>
    </template>
</div>
<script>
const nodesMapaGlobal = {!! $labsMapaJson !!};

function filtrarCatalogoMercadoVivo(filtroLabId = null) {
    const cat = document.getElementById('filter-cat').value;
    const text = document.getElementById('filter-text').value.toLowerCase();
    const cards = document.querySelectorAll('.creator-asset-card'); 
    const btnReset = document.getElementById('btn-reset-market-filter');

    if (filtroLabId) {
        if(btnReset) btnReset.style.display = 'inline-flex';
        
        // 🚀 AUTO-SCROLL OPTIMIZADO: Apuntamos al selector de categorías y lo mandamos al inicio de la pantalla
        setTimeout(() => {
            const barraFiltros = document.getElementById('filter-cat');
            if (barraFiltros) {
                barraFiltros.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, 200); // ⏱️ Tiempo calibrado para soltar el mapa de Leaflet
    }

    cards.forEach(card => {
        const matchesCat = (cat === 'all' || card.getAttribute('data-category') === cat);
        const matchesText = card.getAttribute('data-search').includes(text);
        const matchesLab = (!filtroLabId || parseInt(card.getAttribute('data-lab-id')) === parseInt(filtroLabId));

        if (matchesCat && matchesText && matchesLab) {
            card.style.display = 'flex'; 
        } else {
            card.style.display = 'none';
        }
    });
}

function restablecerFiltroTotalMercado() {
    document.getElementById('filter-cat').value = 'all';
    document.getElementById('filter-text').value = '';
    const btnReset = document.getElementById('btn-reset-market-filter');
    if(btnReset) btnReset.style.display = 'none';
    filtrarCatalogoMercadoVivo();
}

function abrirModalReputacion(orderId, labId, labName, assetName, assetType = 'machine') {
    // 1. Identificación del idioma de la categoría
    let etiquetaRecurso = "";
    if (assetType === 'machine') {
        etiquetaRecurso = "{{ __('messages.opt_machine') }}";
    } else if (assetType === 'service') {
        etiquetaRecurso = "{{ __('messages.opt_service') }}";
    } else {
        etiquetaRecurso = "{{ __('messages.opt_lab') }}";
    }

    // 2. Lanzamiento del motor SweetAlert2
    Swal.fire({
        title: '⭐ {{ __('messages.modal_rate_title') }}',
        // Levantamos el HTML puro directamente desde el template de Blade
        html: document.getElementById('review-modal-template').innerHTML,
        background: '#1c2230',
        color: '#fff',
        showCancelButton: true,
        confirmButtonColor: '#f1c40f',
        cancelButtonColor: '#7f8c8d',
        confirmButtonText: '💾 {{ __('messages.btn_submit_review') }}',
        cancelButtonText: '{{ __('messages.swal_cancel') }}',
        customClass: { popup: 'premium-popup' },
        
        // 🚀 CIRCUITO BLINDADO: Escribimos directo en el DOM activo del modal
        didOpen: () => {
            const modalVivo = Swal.getHtmlContainer();
            
            modalVivo.querySelector('#swal-order-id').value = orderId;
            modalVivo.querySelector('#swal-lab-id').value = labId;
            modalVivo.querySelector('#swal-pizarra-nombre-lab').textContent = labName;
            modalVivo.querySelector('#swal-pizarra-nombre-activo').textContent = assetName;
            
            const badgeCategoria = modalVivo.querySelector('#swal-pizarra-tipo-recurso');
            badgeCategoria.textContent = etiquetaRecurso;
            badgeCategoria.className = 'badge-semantic badge-' + assetType;
            
            modalVivo.querySelector('#lab-star5').checked = true;
        },
        
        preConfirm: () => {
            const form = Swal.getPopup().querySelector('#form-evaluar-laboratorio');
            if (!form.checkValidity()) {
                form.reportValidity();
                return false;
            }
            return true;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.getPopup().querySelector('#form-evaluar-laboratorio').submit();
        }
    });
}

function interceptarCalculoReserva(event, boton) {
    event.preventDefault();
    const form = boton.closest('form');
    const card = boton.closest('.creator-asset-card'); 
    
    const inputHoras = form.querySelector('input[name="hours"]');
    const inputFecha = form.querySelector('input[name="reservation_date"]');
    
    const horas = parseFloat(inputHoras.value) || 0;
    const fecha = inputFecha.value;
    const precioUnitario = parseFloat(card.querySelector('.price-tag').getAttribute('data-unit-price')) || 0;
    const nombreActivo = card.querySelector('.asset-display-title').textContent;
    
    if (!fecha || horas <= 0) {
        form.reportValidity();
        return;
    }

    const costoCalculado = horas * precioUnitario;
    
    // Inyectamos el saldo total del usuario desde el backend de Blade
    const saldoUsuario = {{ $saldoTotal ?? 0 }}; 

    if (costoCalculado > saldoUsuario) {
        // FLUJO 2: NO LE ALCANZA -> OFRECER CRÉDITO
        const diferencia = costoCalculado - saldoUsuario;
        
        Swal.fire({
            title: "{{ __('messages.swal_credit_title') }}",
            text: "{{ __('messages.swal_credit_desc_1') }} " + costoCalculado.toLocaleString() + " FC, {{ __('messages.swal_credit_desc_2') }} " + saldoUsuario.toLocaleString() + " FC. {{ __('messages.swal_credit_desc_3') }} " + diferencia.toLocaleString() + " FC?",
            icon: 'warning',
            background: '#1c2230',
            color: '#fff',
            showCancelButton: true,
            confirmButtonColor: '#f39c12', // Color Naranja/yellow de crédito
            cancelButtonColor: '#7f8c8d',
            confirmButtonText: "🤝 {{ __('messages.btn_req_credit') }}",
            cancelButtonText: "{{ __('messages.swal_cancel') }}"
        }).then((result) => {
            if (result.isConfirmed) {
                // Agregamos un input oculto para avisarle al backend que es un crédito
                const hiddenCredit = document.createElement('input');
                hiddenCredit.type = 'hidden';
                hiddenCredit.name = 'request_credit';
                hiddenCredit.value = '1';
                form.appendChild(hiddenCredit);
                form.submit();
            }
        });
        
    } else {
        // FLUJO 1: LE ALCANZA PERFECTAMENTE -> RESERVA NORMAL
        Swal.fire({
            title: "{{ __('messages.swal_are_you_sure') }}",
            text: "{{ __('messages.swal_reserve_calc_1') }} " + costoCalculado.toLocaleString() + " {{ __('messages.swal_reserve_calc_2') }} '" + nombreActivo + "' {{ __('messages.swal_reserve_calc_3') }} " + horas + " {{ __('messages.swal_reserve_calc_4') }}",
            icon: 'info',
            background: '#1c2230',
            color: '#fff',
            showCancelButton: true,
            confirmButtonColor: '#2ecc71',
            cancelButtonColor: '#7f8c8d',
            confirmButtonText: "{{ __('messages.swal_confirm_reserve') }}",
            cancelButtonText: "{{ __('messages.swal_cancel') }}"
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    }
}

document.addEventListener("DOMContentLoaded", function() {
    if (!document.getElementById('leaflet-css-cdn')) {
        let link = document.createElement('link'); link.id = 'leaflet-css-cdn'; link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'; document.head.appendChild(link);
    }

    let centerLat = -12.046374; let centerLng = -77.042793;
    if(nodesMapaGlobal.length > 0) { centerLat = nodesMapaGlobal[0].latitude; centerLng = nodesMapaGlobal[0].longitude; }

    var creatorMap = L.map('creator-map', { zoomControl: false }).setView([centerLat, centerLng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(creatorMap);

    var labIcon = L.divIcon({
        className: 'custom-lab-pin',
        html: '<div style="font-size: 30px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4)); cursor: pointer;">🏭</div>',
        iconSize: [32, 32], iconAnchor: [16, 32]
    });

    nodesMapaGlobal.forEach(lab => {
        let marker = L.marker([lab.latitude, lab.longitude], { icon: labIcon }).addTo(creatorMap);
        
        let popupContent = `<div style="color:#fff; font-family:'Inter', sans-serif; padding:5px; min-width:160px;">
            <strong class="leaflet-popup-lab-title">${lab.name}</strong>
            <span class="leaflet-popup-lab-addr">📍 ${lab.address}</span>
            <button type="button" onclick="filtrarCatalogoMercadoVivo(${lab.id})" class="btn-popup-filter">⚙️ {{ __('messages.btn_filter_activos_lab') }}</button>
        </div>`;
        
        marker.bindPopup(popupContent, { background: '#1c2230', className: 'premium-map-popup' });
    });

    function rectificarMapaCreator() {
        if (document.getElementById('hub-mercado') && document.getElementById('hub-mercado').style.display !== 'none') {
            setTimeout(() => { creatorMap.invalidateSize(); }, 200);
        }
    }
    document.querySelectorAll('.card-mercado-neon, .lab-profile-trigger').forEach(btn => btn.addEventListener('click', rectificarMapaCreator));
    setTimeout(rectificarMapaCreator, 600);

    const btnSearch = document.getElementById('btn-creator-search');
    const inputSearch = document.getElementById('creator-search-input');
    if(btnSearch && inputSearch) {
        btnSearch.addEventListener('click', function() {
            const query = inputSearch.value.trim(); if(!query) return;
            btnSearch.innerHTML = "⏳";
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                .then(r => r.json()).then(data => {
                    btnSearch.innerHTML = "🔍 {{ __('messages.btn_search') }}";
                    if(data && data.length > 0) {
                        creatorMap.setView([data[0].lat, data[0].lon], 14);
                        creatorMap.invalidateSize();
                    }
                }).catch(() => { btnSearch.innerHTML = "🔍 {{ __('messages.btn_search') }}"; });
        });
    }
});
</script>
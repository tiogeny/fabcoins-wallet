<div class="focus-glow-blue">
    <div class="premium-glass-card">
        <h2 class="premium-glass-card-title">🗺️ {{ __('messages.map_explorer_title') }}</h2>
        <p class="premium-glass-card-subtitle">{{ __('messages.map_explorer_desc') }}</p>
        
        <div class="market-search-bar">
            <input type="text" id="creator-search-input" placeholder="{{ __('messages.ph_search_district') }}" class="premium-input m-0">
            <button type="button" id="btn-creator-search" class="btn-premium btn-blue-hub m-0 w-auto">🔍 {{ __('messages.btn_search') }}</button>
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
                                <a href="profile.php?u={{ $r->lab_slug ?? $r->lab_owner_id }}" target="_blank" class="asset-lab-badge" style="text-decoration: none; color: #3498db; font-weight: 700;">
                                    🏭 {{ $r->lab_name }} ↗️
                                </a>
                                @if(!empty($r->lab_address) || !empty($r->address))
                                    <span class="asset-address-text" style="margin-top: 0; font-size: 10px;">📍 {{ $r->lab_address ?? $r->address }}</span>
                                @endif
                            </div>
                            <span class="price-tag td-amount-gold" data-unit-price="{{ $r->set_price_fc }}" style="color: #2ecc71;">{{ number_format($r->set_price_fc, 2) }} FC/h</span>
                        </div>
                        
                        <h3 class="asset-display-title asset-title-large">{{ $r->custom_name }}</h3>
                        
                        @php 
                            $bgBadge = '#7f8c8d';
                            if($r->asset_type === 'machine') $bgBadge = '#1abc9c'; // Verde
                            elseif($r->asset_type === 'service') $bgBadge = '#3498db'; // Azul
                            elseif(in_array($r->asset_type, ['lab', 'space', 'workshop'])) $bgBadge = '#9b59b6'; // Morado
                        @endphp
                        <span class="asset-type-badge" style="background: {{ $bgBadge }}; color: white;">{{ $r->display_name }}</span>
                        
                        <div class="asset-capacity-wrap">
                            <span class="asset-capacity-label">{{ __('messages.lbl_avail_capacity') }} {{ number_format($disp, 1) }}h</span>
                            <div class="asset-bar-bg">
                                <div class="asset-bar-fill" style="width: {{ ($r->useful_life_hours > 0) ? ($disp / $r->useful_life_hours) * 100 : 0 }}%;"></div>
                            </div>
                        </div>
                    </div>
                    
                    <form action="{{ route('creator.book_asset') }}" method="POST" class="m-0">
                        @csrf 
                        <input type="hidden" name="asset_id" value="{{ $r->id }}">
                        
                        <div class="form-reserve-row">
                            <div style="position: relative; width: 100%; height: 36px;">
                                <span class="calendar-icon-overlay-sm" style="top: 10px;">📅</span>
                                <input type="date" name="reservation_date" required min="{{ date('Y-m-d') }}" class="input-date-sm">
                            </div>
                            <input type="number" name="hours" step="0.5" min="0.5" max="{{ $disp }}" placeholder="Hrs" required class="input-hours-sm">
                            <button type="submit" class="btn-premium btn-blue-hub btn-reserve-sm" onclick="interceptarCalculoReserva(event, this)">{{ __('messages.btn_reserve') }}</button>
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
                                        <button type="button" class="btn-back-minimal btn-min-eval-gold" style="width: auto;" onclick="abrirModalReputacion({{ $res->id }}, {{ $res->lab_owner_id }}, '{{ $res->lab_name }}', '{{ $res->custom_name }}')">⭐ {{ __('messages.btn_rate_service') }}</button>
                                    @else
                                        <span style="font-size: 12px; color: #4a5568;">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-evaluar-reputacion" class="modal-overlay-blur">
        <div class="modal-container-dark">
            <div class="modal-header-glass">
                <h3 class="modal-title-glass">⭐ {{ __('messages.modal_rate_title') }}</h3>
                <button type="button" onclick="document.getElementById('modal-evaluar-reputacion').style.display='none'" class="modal-close-btn">&times;</button>
            </div>

            <form action="{{ route('creator.rate_lab') }}" method="POST">
                @csrf
                <input type="hidden" name="order_id" id="modal-rating-order-id">
                <input type="hidden" name="lab_id" id="modal-rating-lab-id">

                <div class="modal-info-box grid-mission-inputs" style="gap: 12px;">
                    <div>
                        <div class="modal-rating-label">{{ __('messages.th_lab') }}</div>
                        <div id="modal-pizarra-nombre-lab" class="modal-creator-name text-white-pure">-</div>
                    </div>
                    <div>
                        <div class="modal-rating-label">{{ __('messages.th_equipment') }}</div>
                        <div id="modal-pizarra-nombre-activo" class="modal-creator-name hub-text-azul">-</div>
                    </div>
                </div>

                <div class="flex-align-gap-10 mb-20">
                    <label class="modal-rating-label m-0">{{ __('messages.lbl_stars') }}</label>
                    <select name="rating" class="modal-rating-select" required>
                        <option value="5" selected>⭐ ⭐ ⭐ ⭐ ⭐ (5/5)</option>
                        <option value="4">⭐ ⭐ ⭐ ⭐ (4/5)</option>
                        <option value="3">⭐ ⭐ ⭐ (3/5)</option>
                        <option value="2">⭐ ⭐ (2/5)</option>
                        <option value="1">⭐ (1/5)</option>
                    </select>
                </div>

                <div class="mb-22">
                    <textarea name="comment" placeholder="{{ __('messages.ph_review_comment') }}" class="premium-textarea m-0 h-90" required></textarea>
                </div>

                <button type="submit" class="btn-logout-v2 btn-modal-submit btn-modal-gold" onclick="confirmarAccion(event, '{{ __('messages.swal_confirm_rating') }}', 'info', '#f1c40f')">💾 {{ __('messages.btn_submit_review') }}</button>
            </form>
        </div>
    </div>
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
    }

    cards.forEach(card => {
        const matchesCat = (cat === 'all' || card.getAttribute('data-category') === cat);
        const matchesText = card.getAttribute('data-search').includes(text);
        const matchesLab = (!filtroLabId || parseInt(card.getAttribute('data-lab-id')) === parseInt(filtroLabId));

        if (matchesCat && matchesText && matchesLab) {
            card.style.display = 'flex'; // Usando flex para mantener la estructura horizontal
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

function abrirModalReputacion(orderId, labId, labName, assetName) {
    document.getElementById('modal-rating-order-id').value = orderId;
    document.getElementById('modal-rating-lab-id').value = labId;
    document.getElementById('modal-pizarra-nombre-lab').textContent = labName;
    document.getElementById('modal-pizarra-nombre-activo').textContent = assetName;
    document.getElementById('modal-evaluar-reputacion').style.display = 'flex';
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
            confirmButtonColor: '#f39c12', // Color Naranja/Amarillo de crédito
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
            <button type="button" onclick="filtrarCatalogoMercadoVivo(${lab.id})" class="btn-popup-filter">⚙️ {{ __('messages.btn_filter_this_lab') }}</button>
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
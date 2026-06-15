<div class="focus-glow-blue">
    <div class="card" style="border: 1px solid rgba(255, 255, 255, 0.04); background: #1c2230; margin-bottom: 25px; padding: 24px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <h2 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 19px; color: #ffffff; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">🗺️ {{ __('messages.map_explorer_title') }}</h2>
        <p class="text-muted" style="font-size: 12.5px; margin-bottom: 20px; color: #a0aec0;">{{ __('messages.map_explorer_desc') }}</p>
        
        <div style="display: flex; gap: 10px; margin-bottom: 20px; align-items: center;">
            <input type="text" id="creator-search-input" placeholder="{{ __('messages.ph_search_district') }}" style="flex: 1; height: 40px; background: #131722; color: white; border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; padding: 0 15px; font-size: 13px; margin: 0;">
            <button type="button" id="btn-creator-search" class="btn-logout-v2" style="background: #3498db; border-color: #3498db; color: white; height: 40px; padding: 0 24px; margin: 0; font-weight: 700; border-radius: 6px;">🔍 {{ __('messages.btn_search') }}</button>
        </div>
        
        <div id="creator-map" style="height: 380px; width: 100%; border-radius: 8px; border: 1px solid rgba(255,255,255,0.04); z-index: 1; background: #131722;"></div>
    </div>

    <div class="card" style="border: 1px solid rgba(255, 255, 255, 0.04); background: #1c2230; margin-bottom: 25px; padding: 24px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
            <h2 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 19px; color: #ffffff; text-transform: uppercase; letter-spacing: 0.5px; margin: 0;">🏭 {{ __('messages.market_capacity_title') }}</h2>
            <button type="button" id="btn-reset-market-filter" class="btn-back-minimal" style="display: none; padding: 0 12px; height: 28px; font-size: 10.5px; font-weight: bold; border-color: rgba(52,152,219,0.3); color: #3498db;" onclick="restablecerFiltroTotalMercado()">🔄 {{ __('messages.btn_clear_filter') }}</button>
        </div>
        <p class="text-muted" style="font-size: 12.5px; margin-bottom: 20px; color: #a0aec0;">{{ __('messages.market_capacity_desc') }}</p>
        
        <div style="display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px;">
            <select id="filter-cat" onchange="filtrarCatalogoMercadoVivo()" style="width: 100%; height: 40px; background: #131722; color: white; border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; padding: 0 15px; font-size: 13px; font-weight: 600;">
                <option value="all">⚙️ {{ __('messages.opt_all_tech') }}</option>
                <option value="machine">{{ __('messages.opt_machine') }}</option>
                <option value="service">{{ __('messages.opt_service') }}</option>
                <option value="lab">{{ __('messages.opt_lab') }}</option>
            </select>
            <input type="text" id="filter-text" placeholder="{{ __('messages.ph_search_asset') }}" onkeyup="filtrarCatalogoMercadoVivo()" style="width: 100%; height: 40px; background: #131722; color: white; border: 1px solid rgba(255,255,255,0.06); border-radius: 8px; padding: 0 15px; font-size: 13px; margin: 0;">
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @forelse($recursosMercado as $r)
                @php 
                    $disp = $r->useful_life_hours - $r->consumed_hours; 
                @endphp
                <div class="creator-asset-row" data-category="{{ $r->asset_type }}" data-lab-id="{{ $r->lab_id }}" data-search="{{ strtolower($r->lab_name . ' ' . $r->custom_name . ' ' . $r->display_name) }}" style="display: flex; justify-content: space-between; align-items: center; background: #131722; padding: 16px 20px; border-radius: 8px; border: 1px solid rgba(255,255,255,0.02); flex-wrap: wrap; gap: 15px;">
                    
                    <div style="flex: 1; min-width: 250px;">
                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                            <strong style="font-size: 11px; color: #a0aec0; text-transform: uppercase; letter-spacing: 0.5px;">🏭 {{ $r->lab_name }}</strong>
                            <span class="price-tag" data-unit-price="{{ $r->set_price_fc }}" style="color: #2ecc71; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 14px;">{{ number_format($r->set_price_fc, 0) }} FC/h</span>
                        </div>
                        <h3 class="asset-display-title" style="margin: 0 0 6px 0; font-size: 15px; color: #ffffff;">{{ $r->custom_name }}</h3>
                        <span style="padding: 3px 8px; border-radius: 4px; font-size: 9px; font-weight: 800; background: rgba(255,255,255,0.08); color: #cbd5e0; text-transform: uppercase; letter-spacing: 0.5px;">{{ $r->display_name }}</span>
                    </div>
                    
                    <form action="{{ route('creator.book_asset') }}" method="POST" style="margin: 0; display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                        @csrf 
                        <input type="hidden" name="asset_id" value="{{ $r->id }}">
                        <div style="position: relative;">
                            <span style="position: absolute; left: 10px; top: 10px; font-size: 12px; pointer-events: none;">📅</span>
                            <input type="date" name="reservation_date" required min="{{ date('Y-m-d') }}" style="background: #1c2230; color: white; border: 1px solid rgba(255,255,255,0.06); height: 38px; border-radius: 6px; font-size: 12px; padding: 0 10px 0 32px; margin: 0; color-scheme: dark; width: 140px;">
                        </div>
                        
                        <input type="number" name="hours" step="1" min="1" max="{{ $disp }}" placeholder="Hrs" required style="background: #1c2230; color: white; border: 1px solid rgba(255,255,255,0.06); height: 38px; border-radius: 6px; font-size: 13px; font-family: 'Rajdhani', sans-serif; font-weight: 700; text-align: center; margin: 0; width: 80px;">
                        
                        <button type="submit" class="btn-logout-v2" style="background: #2ecc71; border-color: #2ecc71; color: white; height: 38px; padding: 0 20px; margin: 0; font-size: 12px; font-weight: 700; border-radius: 6px;" onclick="interceptarCalculoReserva(event, this)">{{ __('messages.btn_reserve') }}</button>
                    </form>
                </div>
            @empty
                <div style="text-align: center; padding: 40px; color: #7f8c8d; background: #131722; border-radius: 8px; border: 1px dashed rgba(255,255,255,0.1);">
                    <p style="font-size: 13.5px; margin-bottom: 0;">{{ __('messages.market_empty') }}</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="card" style="border: 1px solid rgba(255, 255, 255, 0.04); background: #1c2230; padding: 24px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <h2 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 18px; color: #ffffff; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">📋 {{ __('messages.monitor_title') }}</h2>
        <p class="text-muted" style="font-size: 12.5px; margin-bottom: 20px; color: #a0aec0;">{{ __('messages.monitor_desc') }}</p>

        <div class="table-container">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.08); text-align: left;">
                        <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase;">{{ __('messages.th_date') }}</th>
                        <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase;">{{ __('messages.th_equipment') }}</th>
                        <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase;">{{ __('messages.th_lab') }}</th>
                        <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase;">{{ __('messages.th_total_cost') }}</th>
                        <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase;">{{ __('messages.th_status') }}</th>
                        <th style="padding: 12px; font-size: 10px; color: #7f8c8d; text-transform: uppercase; text-align: center; width: 180px;">{{ __('messages.lbl_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if($misReservas->isEmpty())
                        <tr><td colspan="6" style="text-align:center; padding:35px; color:#7f8c8d; font-size: 12.5px; font-style: italic;">{{ __('messages.monitor_empty') }}</td></tr>
                    @else
                        @foreach($misReservas as $res)
                            <tr style="border-bottom: 1px solid rgba(255,255,255,0.02); vertical-align: middle;">
                                <td style="padding: 12px; font-size: 12px; color: #a0aec0;">
                                    {{ date('d M Y', strtotime($res->created_at)) }}<br>
                                    <span style="font-size: 10px; color: #3498db; font-weight: bold; white-space: nowrap;">📅 {{ date('d M Y', strtotime($res->reservation_date)) }}</span>
                                </td>
                                <td style="padding: 12px; font-size: 13px; font-weight: 600; color: #ffffff;">
                                    {{ $res->custom_name }}
                                    <div style="font-size: 10.5px; color: #7f8c8d; font-weight: 500; margin-top: 2px;">⏱️ {{ $res->hours_requested }} hrs</div>
                                </td>
                                <td style="padding: 12px; font-size: 13px; color: #cbd5e0;">🏭 {{ $res->lab_name }}</td>
                                <td style="padding: 12px; font-family: 'Rajdhani', sans-serif; font-weight: 800; color: #f1c40f; font-size: 15px;">{{ number_format($res->total_fc, 0) }} FC</td>
                                <td style="padding: 12px;">
                                    @if($res->status === 'pending')
                                        <span style="padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; background: rgba(241,196,15,0.08); color: #f1c40f; letter-spacing: 0.3px;">⏳ {{ __('messages.status_waiting') }}</span>
                                    @elseif($res->status === 'completed')
                                        <span style="padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; background: rgba(46,204,113,0.08); color: #2ecc71; letter-spacing: 0.3px;">✓ {{ __('messages.status_approved') }}</span>
                                    @elseif($res->status === 'rescheduled')
                                        <span style="padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; background: rgba(52,152,219,0.08); color: #3498db; letter-spacing: 0.3px;">⚠️ {{ __('messages.status_rescheduled') }}</span>
                                    @else
                                        <span style="padding: 3px 6px; border-radius: 4px; font-size: 9px; font-weight: 600; background: rgba(231,76,60,0.08); color: #e74c3c; letter-spacing: 0.3px;">❌ {{ __('messages.status_rejected') }}</span>
                                    @endif
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    @if($res->status === 'rescheduled')
                                        <div style="display: flex; gap: 6px; justify-content: center;">
                                            <form action="{{ route('creator.accept_date') }}" method="POST" style="margin:0;">
                                                @csrf <input type="hidden" name="order_id" value="{{ $res->id }}">
                                                <button type="submit" class="btn-back-minimal" style="padding: 4px 10px; font-size: 10px; background: rgba(46,204,113,0.06); border-color: rgba(46,204,113,0.3); color: #2ecc71; font-weight: 700;">{{ __('messages.btn_accept') }}</button>
                                            </form>
                                            <form action="{{ route('creator.reject_date') }}" method="POST" style="margin:0;">
                                                @csrf <input type="hidden" name="order_id" value="{{ $res->id }}">
                                                <button type="submit" class="btn-back-minimal" style="padding: 4px 10px; font-size: 10px; border-color: rgba(231,76,60,0.2); color: #e74c3c;">{{ __('messages.btn_cancel') }}</button>
                                            </form>
                                        </div>
                                    @elseif($res->status === 'completed' && !$res->is_reviewed)
                                        <button type="button" class="btn-back-minimal" style="padding: 5px 14px; font-size: 10.5px; border-color: rgba(241,196,15,0.3); color: #f1c40f; font-weight: 700; width: auto;" onclick="abrirModalReputacion({{ $res->id }}, {{ $res->lab_owner_id }}, '{{ $res->lab_name }}', '{{ $res->custom_name }}')">⭐ {{ __('messages.btn_rate_service') }}</button>
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

    <div id="modal-evaluar-reputacion" class="modal-overlay-blur" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.75); z-index: 3000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
        <div class="modal-container-dark" style="background: #1c2230; width: 100%; max-width: 480px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.06); box-shadow: 0 20px 50px rgba(0,0,0,0.6); padding: 24px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px; border-bottom: 1px solid rgba(255,255,255,0.04); padding-bottom: 12px;">
                <h3 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 17px; color: #ffffff; text-transform: uppercase; margin: 0; letter-spacing: 0.5px;">⭐ {{ __('messages.modal_rate_title') }}</h3>
                <button type="button" onclick="document.getElementById('modal-evaluar-reputacion').style.display='none'" style="background: transparent; border: none; color: #7f8c8d; font-size: 22px; cursor: pointer; line-height: 1;">&times;</button>
            </div>

            <form action="{{ route('creator.rate_lab') }}" method="POST">
                @csrf
                <input type="hidden" name="order_id" id="modal-rating-order-id">
                <input type="hidden" name="lab_id" id="modal-rating-lab-id">

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; background: #131722; padding: 12px; border-radius: 8px; margin-bottom: 20px; border: 1px solid rgba(255,255,255,0.01);">
                    <div>
                        <div style="font-size: 9px; color: #7f8c8d; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">{{ __('messages.th_lab') }}</div>
                        <div id="modal-pizarra-nombre-lab" style="font-size: 13.5px; color: #ffffff; font-weight: 600; margin-top: 2px;">-</div>
                    </div>
                    <div>
                        <div style="font-size: 9px; color: #7f8c8d; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">{{ __('messages.th_equipment') }}</div>
                        <div id="modal-pizarra-nombre-activo" style="font-size: 13.5px; color: #3498db; font-weight: 600; margin-top: 2px;">-</div>
                    </div>
                </div>

                <div style="margin-bottom: 20px; display: flex; align-items: center; gap: 15px;">
                    <label style="font-size: 11px; font-weight: 700; color: #a0aec0; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0;">{{ __('messages.lbl_stars') }}</label>
                    <select name="rating" style="flex: 1; font-weight: 700; background: #131722; border: 1px solid rgba(255,255,255,0.06); color: #f1c40f; height: 36px; border-radius: 6px; font-size: 13px; text-align: center;" required>
                        <option value="5" selected>⭐ ⭐ ⭐ ⭐ ⭐ (5/5)</option>
                        <option value="4">⭐ ⭐ ⭐ ⭐ (4/5)</option>
                        <option value="3">⭐ ⭐ ⭐ (3/5)</option>
                        <option value="2">⭐ ⭐ (2/5)</option>
                        <option value="1">⭐ (1/5)</option>
                    </select>
                </div>

                <div style="margin-bottom: 22px;">
                    <textarea name="comment" placeholder="{{ __('messages.ph_review_comment') }}" style="width: 100%; height: 85px; background: #131722; border: 1px solid rgba(255,255,255,0.06); color: #fff; border-radius: 6px; font-size: 12.5px; padding: 12px; resize: none; font-family: 'Inter', sans-serif;" required></textarea>
                </div>

                <button type="submit" class="btn-logout-v2" style="width: 100%; height: 42px; background: #f1c40f; border-color: #f1c40f; color: #111111 !important; font-weight: 700; font-size: 12px; border-radius: 6px;" onclick="confirmarAccion(event, '{{ __('messages.swal_confirm_rating') }}', 'info', '#f1c40f')">💾 {{ __('messages.btn_submit_review') }}</button>
            </form>
        </div>
    </div>
</div>
<script>
const nodesMapaGlobal = {!! $labsMapaJson !!};

function filtrarCatalogoMercadoVivo(filtroLabId = null) {
    const cat = document.getElementById('filter-cat').value;
    const text = document.getElementById('filter-text').value.toLowerCase();
    const cards = document.querySelectorAll('.creator-asset-row'); // Cambiado a .creator-asset-row
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
    const row = boton.closest('.creator-asset-row');
    const inputHoras = form.querySelector('input[name="hours"]');
    const inputFecha = form.querySelector('input[name="reservation_date"]');
    
    const horas = parseFloat(inputHoras.value) || 0;
    const fecha = inputFecha.value;
    const precioUnitario = parseFloat(row.querySelector('.price-tag').getAttribute('data-unit-price')) || 0;
    const nombreActivo = row.querySelector('.asset-display-title').textContent;
    
    if (!fecha || horas <= 0) {
        form.reportValidity();
        return;
    }

    const costoCalculado = horas * precioUnitario;
    
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
            <strong style="color:#3498db; font-size:12px; display:block; margin-bottom:2px;">${lab.name}</strong>
            <span style="font-size:10px; color:#bdc3c7; display:block; margin-bottom:6px; line-height:1.2;">📍 ${lab.address}</span>
            <button type="button" onclick="filtrarCatalogoMercadoVivo(${lab.id})" style="background:#3498db; border:none; color:white; font-size:10px; padding:4px 8px; border-radius:4px; cursor:pointer; font-weight:bold; width:100%; text-transform:uppercase; letter-spacing:0.3px;">⚙️ {{ __('messages.btn_filter_this_lab') }}</button>
        </div>`;
        
        marker.bindPopup(popupContent, { background: '#1c2230', className: 'premium-map-popup' });
    });

    function rectificarMapaCreator() {
        if (document.getElementById('workspace-mercado') && document.getElementById('workspace-mercado').style.display !== 'none') {
            setTimeout(() => { creatorMap.invalidateSize(); }, 200);
        }
    }
    document.querySelectorAll('.card-activar-neon, .lab-profile-trigger').forEach(btn => btn.addEventListener('click', rectificarMapaCreator));
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
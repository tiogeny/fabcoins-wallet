<div class="focus-glow-blue">

    <div class="card" style="border: 1px solid rgba(255, 255, 255, 0.04); background: #1c2230; margin-bottom: 25px; padding: 24px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <h2 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 19px; color: #ffffff; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">🗺️ {{ __('messages.map_explorer_title') }}</h2>
        <p class="text-muted" style="font-size: 12.5px; margin-bottom: 20px; color: #a0aec0;">{{ __('messages.map_explorer_desc') }}</p>
        
        <div class="market-search-bar">
            <input type="text" id="creator-search-input" placeholder="{{ __('messages.ph_map_creator_search') }}" style="flex: 1; margin: 0;">
            <button type="button" id="btn-creator-search" class="btn-blue-hub" style="height: 40px;">🔍 {{ __('messages.btn_search_map') }}</button>
        </div>
        <div id="creator-map" style="height: 380px; width: 100%; border-radius: 8px; border: 1px solid rgba(255,255,255,0.04); z-index: 1; background: #131722;"></div>
    </div>

    <div class="card" style="border: 1px solid rgba(255, 255, 255, 0.04); background: #1c2230; padding: 24px; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <h2 style="font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 19px; color: #ffffff; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">🏭 {{ __('messages.market_capacity_title') }}</h2>
        <p class="text-muted" style="font-size: 12.5px; margin-bottom: 20px; color: #a0aec0;">{{ __('messages.market_capacity_desc') }}</p>
        
        <div class="market-filters">
            <select id="filter-cat" style="flex: 1;" onchange="filtrarCatalogoMercadoVivo()">
                <option value="all">{{ __('messages.opt_all_tech') }}</option>
                <option value="machine">⚙️ {{ __('messages.opt_machine') }}</option>
                <option value="service">🧠 {{ __('messages.opt_service') }}</option>
                <option value="lab">🏢 {{ __('messages.opt_lab') }}</option>
            </select>
            <input type="text" id="filter-text" placeholder="{{ __('messages.ph_market_grid_search') }}" style="flex: 2;" onkeyup="filtrarCatalogoMercadoVivo()">
        </div>

        <div class="creator-mission-grid">
            @forelse($recursosMercado as $r)
                <div class="creator-asset-card" data-category="{{ $r->asset_type }}" data-lab-id="{{ $r->lab_id }}" data-search="{{ strtolower($r->lab_name . ' ' . $r->custom_name . ' ' . $r->lab_address) }}">
                    <div>
                        <div class="creator-asset-header">
                            <strong>🏭 {{ strtoupper($r->lab_name) }}</strong>
                            <span class="price-tag">{{ number_format($r->set_price_fc, 0) }} FC/h</span>
                        </div>
                        <h3 style="margin: 0 0 4px 0; font-size: 14.5px; color: #ffffff;">{{ $r->custom_name }}</h3>
                        <span style="padding: 2px 6px; border-radius: 4px; font-size: 8.5px; font-weight: 800; background: rgba(255,255,255,0.05); color: #bdc3c7; text-transform: uppercase; letter-spacing: 0.3px;">{{ $r->display_name }}</span>
                    </div>
                    
                    <form action="{{ route('creator.book_asset') }}" method="POST" style="margin-top: 15px; border-top: 1px solid rgba(255,255,255,0.02); padding-top: 12px;">
                        @csrf 
                        <input type="hidden" name="asset_id" value="{{ $r->id }}">
                        <div style="display: grid; grid-template-columns: 1.2fr 0.8fr auto; gap: 6px; align-items: center;">
                            <input type="date" name="reservation_date" required min="{{ date('Y-m-d') }}" style="background: #1c2230; color: white; border: 1px solid rgba(255,255,255,0.06); height: 34px; border-radius: 4px; font-size: 11px; padding: 0 4px; margin: 0; color-scheme: dark;">
                            <input type="number" name="hours" step="1" min="1" placeholder="Hrs" required style="background: #1c2230; color: white; border: 1px solid rgba(255,255,255,0.06); height: 34px; border-radius: 4px; font-size: 12px; font-family: 'Rajdhani', sans-serif; font-weight: 700; text-align: center; margin: 0;">
                            <button type="submit" class="btn-blue-hub" style="height: 34px; padding: 0 12px; font-size: 11px;">{{ __('messages.btn_book') }}</button>
                        </div>
                    </form>
                </div>
            @empty
                <p class="text-muted" style="text-align:center; padding:40px; width:100%; grid-column: span 3; font-style: italic;">{{ __('messages.market_empty') }}</p>
            @endforelse
        </div>
    </div>
</div>

<script>
const nodesMapaGlobal = {!! $labsMapaJson !!};

function filtrarCatalogoMercadoVivo(filtroLabId = null) {
    const cat = document.getElementById('filter-cat').value;
    const text = document.getElementById('filter-text').value.toLowerCase();
    const cards = document.querySelectorAll('.creator-asset-card');

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

document.addEventListener("DOMContentLoaded", function() {
    if (!document.getElementById('leaflet-css-cdn')) {
        let link = document.createElement('link'); link.id = 'leaflet-css-cdn'; link.rel = 'stylesheet';
        link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'; document.head.appendChild(link);
    }

    // Centrado inicial en Lima o coordenadas del primer Lab
    let centerLat = -12.046374; let centerLng = -77.042793;
    if(nodesMapaGlobal.length > 0) { centerLat = nodesMapaGlobal[0].latitude; centerLng = nodesMapaGlobal[0].longitude; }

    var creatorMap = L.map('creator-map', { zoomControl: false }).setView([centerLat, centerLng], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19 }).addTo(creatorMap);

    var labIcon = L.divIcon({
        className: 'custom-lab-pin',
        html: '<div style="font-size: 30px; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.4)); cursor: pointer;">🏭</div>',
        iconSize: [32, 32], iconAnchor: [16, 32]
    });

    // Renderizar Pines y conectar evento Clic
    nodesMapaGlobal.forEach(lab => {
        let marker = L.marker([lab.latitude, lab.longitude], { icon: labIcon }).addTo(creatorMap);
        
        let popupContent = `<div style="color:#fff; font-family:'Inter', sans-serif; padding:5px;">
            <strong style="color:#3498db; font-size:12px;">${lab.name}</strong><br>
            <span style="font-size:10px; color:#bdc3c7;">📍 ${lab.address}</span><br>
            <button type="button" onclick="filtrarCatalogoMercadoVivo(${lab.id})" style="background:#3498db; border:none; color:white; font-size:10px; padding:3px 8px; border-radius:4px; margin-top:6px; cursor:pointer; font-weight:bold; width:100%;">⚙️ {{ __('messages.btn_filter_this_lab') }}</button>
        </div>`;
        
        marker.bindPopup(popupContent, { background: '#1c2230', className: 'premium-map-popup' });
    });

    // Forzar redibujado geométrico al abrir el Hub 1
    function rectificarMapaCreator() {
        if (document.getElementById('workspace-mercado') && document.getElementById('workspace-mercado').style.display !== 'none') {
            setTimeout(() => { creatorMap.invalidateSize(); }, 200);
        }
    }
    document.querySelectorAll('.card-activar-neon, .lab-profile-trigger').forEach(btn => btn.addEventListener('click', rectificarMapaCreator));
    setTimeout(rectificarMapaCreator, 600);

    // Geocodificación del buscador del mapa
    const btnSearch = document.getElementById('btn-creator-search');
    const inputSearch = document.getElementById('creator-search-input');
    if(btnSearch && inputSearch) {
        btnSearch.addEventListener('click', function() {
            const query = inputSearch.value.trim(); if(!query) return;
            btnSearch.innerHTML = "⏳";
            fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`)
                .then(r => r.json()).then(data => {
                    btnSearch.innerHTML = "🔍 {{ __('messages.btn_search_map') }}";
                    if(data && data.length > 0) {
                        creatorMap.setView([data[0].lat, data[0].lon], 14);
                        creatorMap.invalidateSize();
                    }
                }).catch(() => { btnSearch.innerHTML = "🔍 {{ __('messages.btn_search_map') }}"; });
        });
    }
});
</script>
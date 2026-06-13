@extends('layouts.app')

@section('title', __('messages.maker_portal'))

@section('content')
<div class="container">
    
    <header class="header">
        <div style="display: flex; gap: 15px; align-items: center;" class="profile-info">
            <img src="{{ $maker->avatar_url ?: 'https://via.placeholder.com/60' }}" style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid var(--c-blue);">
            <div>
                <h1 class="m-0" style="font-family: 'Rajdhani', sans-serif; font-weight: 700;">👤 {{ $maker->name }}</h1>
                <div class="font-12 text-muted">⭐ {{ number_format($maker->reputation_score, 1) }} {{ __('messages.reputation') }}</div>
            </div>
        </div>
        <div class="flex-between" style="gap: 20px;">
            <div style="display: flex; gap: 10px; background: rgba(0,0,0,0.3); padding: 5px 15px; border-radius: 20px; align-items: center;">
                <button type="button" onclick="iniciarTourMaker()" style="background:transparent; border:none; color:white; cursor:pointer; font-size:18px; padding:0; width:auto; opacity:0.8;">❓</button>
                <div style="width: 1px; height: 15px; background: rgba(255,255,255,0.2);"></div>
                <a href="?lang=es" style="opacity: {{ app()->getLocale() == 'es' ? '1' : '0.4' }};">🇪🇸</a>
                <a href="?lang=en" style="opacity: {{ app()->getLocale() == 'en' ? '1' : '0.4' }};">🇺🇸</a>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf<a href="{{ route('logout') }}" class="btn-logout" onclick="event.preventDefault(); this.closest('form').submit();">Cerrar Sesión</a></form>
        </div>
    </header>

    @if($creditoActual)
        @if($creditoActual->status == 'pending')
            <div class="card mb-20" style="border: 2px dashed #f39c12; background: rgba(243, 156, 18, 0.02); margin-top:20px;">
                <h3 style="color: #f39c12; margin-top: 0;">🎓 Financiación de Crédito Educativo ISA Ofertado</h3>
                <p class="font-13">La sede <strong>{{ $creditoActual->lab_name }}</strong> te propone un financiamiento de honor por un monto de <strong>{{ number_format($creditoActual->amount_initial, 0) }} FC</strong> para tu formación técnica bajo el concepto: <em>{{ $creditoActual->description }}</em>.</p>
                <form action="{{ route('maker.sign_credit') }}" method="POST">
                    @csrf <input type="hidden" name="contract_id" value="{{ $creditoActual->id }}">
                    <button type="submit" class="btn-apply" style="background:#2ecc71; width:auto; padding:10px 20px; font-weight:bold;">Firmar y Activar Crédito Educativo</button>
                </form>
            </div>
        @elseif($creditoActual->status == 'active')
            @php $porcentaje = round((($creditoActual->amount_initial - $creditoActual->amount_remaining)/$creditoActual->amount_initial)*100); @endphp
            <div id="tour-credito-maker" class="card mb-20" style="border: 1px solid #f1c40f; background: rgba(241, 196, 15, 0.05); margin-top: 20px;">
                <h3 style="color: #f1c40f; margin-top: 0; font-size: 16px;">🎓 {{ __('messages.isa_active_title') ?? 'Crédito Fab Activo:' }} {{ $creditoActual->lab_name }}</h3>
                
                <div style="margin-bottom: 10px; font-size: 12px; color: #bdc3c7;">
                    {{ __('messages.lbl_original_amount') ?? 'Monto Original:' }} <strong style="color: white;">{{ number_format($creditoActual->amount_initial, 0) }} FC</strong>
                </div>
                
                <div class="flex-between mb-5">
                    <span class="font-12 text-muted">{{ __('messages.lbl_progress') ?? 'Progreso:' }} <strong style="color: white;">{{ $porcentaje }}%</strong></span>
                    <span class="font-12 font-bold" style="color: #f1c40f;">{{ __('messages.lbl_debt') ?? 'Deuda:' }} {{ number_format($creditoActual->amount_remaining, 0) }} FC</span>
                </div>
                
                <div style="background: #1a1a1a; border-radius: 10px; height: 12px; overflow: hidden; border: 1px solid #34495e; margin-bottom: 15px;">
                    <div style="width: {{ $porcentaje }}%; background: #f1c40f; height: 100%; border-radius: 10px;"></div>
                </div>

                @if(count($historialAbonos) > 0)
                    <div style="margin-top: 15px; border-top: 1px dashed rgba(241, 196, 15, 0.3); padding-top: 10px;">
                        <h4 style="color: #f1c40f; margin: 0 0 10px 0; font-size: 12px; text-transform: uppercase;">📉 {{ __('messages.isa_payment_history') ?? 'Tus últimos abonos (Trabajo realizado)' }}</h4>
                        @foreach($historialAbonos as $abono)
                            <div style="display: flex; justify-content: space-between; font-size: 11px; color: #bdc3c7; margin-bottom: 5px;">
                                <span>✔️ {{ $abono->title }} <em style="font-size:9px;">({{ date('d M Y', strtotime($abono->created_at)) }})</em></span>
                                <strong style="color: #2ecc71;">-{{ number_format($abono->amount, 2) }} FC</strong>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endif
    @endif

    <section class="grid-kpis" style="margin-top:20px;">
        <div class="kpi-card border-green"><span class="kpi-label">{{ __('messages.kpi_wallet') }}</span><span class="kpi-value" style="color:var(--c-orange);">{{ number_format($saldoTotal, 2) }} FC</span></div>
        <div class="kpi-card border-blue"><span class="kpi-label">Postulaciones Activas</span><span class="kpi-value">{{ $misPostulaciones->where('status','pending')->count() }}</span></div>
        <div class="kpi-card border-yellow"><span class="kpi-label">Misiones Completadas</span><span class="kpi-value">{{ $misionesCompletadasKpi }}</span></div>
    </section>

    <div style="text-align: left; margin-top: 20px; margin-bottom: 20px;">
        <button type="button" id="btn-show-p2p" class="btn-apply" style="background: var(--c-green); width: auto; padding: 10px 25px;" onclick="document.getElementById('p2p-container').style.display='block'; this.style.display='none';">💸 Enviar Fondos P2P a otro Maker</button>
    </div>
    <div id="p2p-container" class="card" style="display:none; border:1px solid #34495e; background:var(--bg-card-dark); margin-bottom:20px;">
        <h3 class="m-0 mb-15 text-blue">🤝 Transferencia Contable P2P Directa</h3>
        <form action="{{ route('maker.transfer_p2p') }}" method="POST" style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
            @csrf
            <div>
                <label class="font-11 text-muted font-bold">CORREO DESTINATARIO</label>
                <input type="email" name="dest_email" id="p2p-email" required onblur="validarDestinatarioP2P(this.value)">
                <small id="p2p-preview" class="font-11 font-bold mt-5 d-block" style="min-height:15px;"></small>
            </div>
            <div style="display:flex; flex-direction:column;">
                <label class="font-11 text-muted font-bold">MONTO (FC)</label>
                <input type="number" step="0.01" name="monto_p2p" required>
                <button type="submit" class="btn-apply" style="background:var(--c-blue); margin-top:auto; padding:11px;">Confirmar Envío Inmediato</button>
            </div>
        </form>
    </div>

    <nav class="tabs-nav">
        <button class="tab-btn active" onclick="openTab(event, 'tab-bolsa')">🎯 Bolsa</button>
        <button class="tab-btn" onclick="openTab(event, 'tab-mis-trabajos')">📋 Postulaciones</button>
        <button class="tab-btn" onclick="openTab(event, 'tab-mercado')">🏭 Mercado</button>
        <button class="tab-btn" onclick="openTab(event, 'tab-movimientos')">💳 Movimientos</button>
        <button class="tab-btn" onclick="openTab(event, 'tab-mapa')">🗺️ Sodes Labs</button>
        <button class="tab-btn" onclick="openTab(event, 'tab-perfil')">👤 Mi Perfil</button>
    </nav>

    <div id="tab-bolsa" class="tab-content active"> @include('maker.tabs.bolsa') </div>
    <div id="tab-mis-trabajos" class="tab-content" style="display:none;"> @include('maker.tabs.postulaciones') </div>
    <div id="tab-mercado" class="tab-content" style="display:none;"> @include('maker.tabs.mercado') </div>
    <div id="tab-movimientos" class="tab-content" style="display:none;"> @include('maker.tabs.movimientos') </div>
    <div id="tab-mapa" class="tab-content" style="display:none;"> @include('maker.tabs.mapa') </div>
    <div id="tab-perfil" class="tab-content" style="display:none;"> @include('maker.tabs.perfil') </div>

</div>
@endsection

@section('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js" referrerpolicy="origin"></script>
<script>
    function openTab(evt, tabName) {
        let i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) tabcontent[i].style.display = "none";
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) tablinks[i].classList.remove("active");
        document.getElementById(tabName).style.display = "block";
        if(evt) evt.currentTarget.classList.add("active");
        sessionStorage.setItem('activeTabMaker', tabName);
    }

    function checkLimit(type, maxLimit) {
        let checked = document.querySelectorAll('.skill-chip-' + type + ':checked').length;
        document.getElementById(type + '-counter').innerText = checked + ' / ' + maxLimit;
        if (checked > maxLimit) {
            Swal.fire({icon: 'error', title: 'Límite Excedido', text: 'Solo puedes destacar un máximo de ' + maxLimit + ' habilidades.', background: '#1a252f', color: '#fff'});
            event.target.checked = false;
            document.getElementById(type + '-counter').innerText = (checked - 1) + ' / ' + maxLimit;
        }
    }

    function validarDestinatarioP2P(email) {
        let p = document.getElementById('p2p-preview'); if(!email) return;
        p.innerText = "⏳ Buscando Maker..."; p.style.color = "var(--c-blue)";
        fetch(`{{ route('maker.check_email_p2p') }}?email=${encodeURIComponent(email)}`)
            .then(r => r.json()).then(data => {
                if(data.name === 'NOT_FOUND') { p.innerText = "❌ No se encontró ningún Maker registrado con ese correo."; p.style.color = "var(--c-red)"; }
                else { p.innerText = "✅ Fondos dirigidos a: " + data.name; p.style.color = "var(--c-green)"; }
            });
    }

    document.addEventListener("DOMContentLoaded", function() {
        tinymce.init({ selector: '#bio-editor', menubar: false, toolbar: 'bold italic | bullist numlist', skin: 'oxide-dark', content_css: 'dark', height: 260, branding: false, setup: function(e){ e.on('change', function(){ e.save(); }); } });
        checkLimit('hard', 6); checkLimit('soft', 4);

        const filterCat = document.getElementById('filter-cat');
        const filterText = document.getElementById('filter-text');
        if(filterCat && filterText) {
            let cards = document.querySelectorAll('#tab-mercado .mission-card');
            let runFilter = () => {
                let cat = filterCat.value; let txt = filterText.value.toLowerCase().trim();
                cards.forEach(c => {
                    let mCat = (cat === 'all' || c.getAttribute('data-category') === cat);
                    let mTxt = (txt === '' || c.getAttribute('data-search').includes(txt));
                    c.style.display = (mCat && mTxt) ? 'block' : 'none';
                });
                };
            filterCat.addEventListener('change', runFilter); filterText.addEventListener('input', runFilter);
        }
    });

    document.addEventListener("DOMContentLoaded", function() {
        var mapContainer = document.getElementById('maker-map'); if(!mapContainer) return;
        var map = L.map('maker-map').setView([-9.1900, -75.0152], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);

        var icon = L.divIcon({ className: 'custom-lab-pin', html: '<div style="font-size: 28px; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">🏭</div>', iconSize: [28, 28], iconAnchor: [14, 28] });
        var data = {!! $labsMapaJson !!}; var bounds = [];

        data.forEach(function(lab) {
            if(lab.latitude && lab.longitude) {
                let marker = L.marker([lab.latitude, lab.longitude], {icon: icon}).addTo(map);
                marker.bindPopup(`<div style="text-align:center; color:#2c3e50;"><h3 style="margin:0 0 5px 0; font-size:14px;">${lab.name}</h3><p style="font-size:11px; margin:0;">📍 ${lab.address}</p></div>`);
                bounds.push([lab.latitude, lab.longitude]);
            }
        });

        document.querySelector('button[onclick*="tab-mapa"]').addEventListener('click', function() {
            setTimeout(() => { map.invalidateSize(); if(bounds.length > 0) map.fitBounds(bounds, {padding:[30,30]}); }, 200);
        });
    });

    function iniciarTourMaker() {
        const tour = window.driver.js.driver({
            showProgress: true, allowClose: false,
            doneBtnText: '¡A Fabricar!', nextBtnText: 'Siguiente ➔', prevBtnText: '⬅ Atrás',
            steps: [
                { element: '.profile-info', popover: { title: '👋 Ecosistema Maker', description: 'Tu identidad digital en la red Fab Lab.', side: "bottom" } },
                { element: '.grid-kpis', popover: { title: '🪙 Mi Monedero', description: 'Control de tus FabCoins ganados co-creando.', side: "bottom" } }
            ]
        });
        tour.drive();
    }
</script>
@endsection
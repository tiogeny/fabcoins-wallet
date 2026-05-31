@extends('layouts.app')

@section('title', __('messages.lab_portal'))

@section('content')
<div class="container">
    
    <header class="header">
        <div style="display: flex; gap: 15px; align-items: center;" class="profile-info">
            <img src="{{ $lab->avatar_url ?: 'https://via.placeholder.com/60' }}" style="width: 60px; height: 60px; border-radius: 50%; border: 2px solid var(--c-green);">
            <div>
                <h1 class="m-0" style="font-family: 'Rajdhani', sans-serif; font-weight: 700;">🏢 {{ $lab->name }}</h1>
                <div class="font-12 text-muted">⭐ {{ number_format($lab->reputation_score, 1) }} {{ __('messages.reputation') }}</div>
            </div>
        </div>
        <div class="flex-between" style="gap: 20px;">
            <div style="display: flex; gap: 10px; background: rgba(0,0,0,0.3); padding: 5px 15px; border-radius: 20px; align-items: center;">
                <button type="button" onclick="iniciarTourLab()" style="background:transparent; border:none; color:white; cursor:pointer; font-size:18px; padding:0; width:auto; opacity:0.8;">❓</button>
                <div style="width: 1px; height: 15px; background: rgba(255,255,255,0.2);"></div>
                <a href="?lang=es" style="opacity: {{ app()->getLocale() == 'es' ? '1' : '0.4' }};">🇪🇸</a>
                <a href="?lang=en" style="opacity: {{ app()->getLocale() == 'en' ? '1' : '0.4' }};">🇺🇸</a>
            </div>
            <div class="notif-wrapper">
                <a href="{{ route('lab.read_notifs') }}" style="font-size: 24px; position: relative;">
                    🔔 @if($unreadCount > 0) <span style="position: absolute; top: -5px; right: -8px; background: #e74c3c; color: white; border-radius: 50%; padding: 2px 6px; font-size: 10px; font-weight: bold;">{{ $unreadCount }}</span> @endif
                </a>
                <div class="notif-dropdown">
                    @forelse($notificaciones as $n)
                        <div class="notif-item {{ !$n->is_read ? 'unread' : '' }}">{{ $n->message }}</div>
                    @empty
                        <div class="notif-item" style="text-align: center; color: #7f8c8d;">{{ __('messages.no_notifications') }}</div>
                    @endforelse
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">@csrf<a href="{{ route('logout') }}" class="btn-logout" onclick="event.preventDefault(); this.closest('form').submit();">🚪 {{ __('messages.btn_logout') }}</a></form>
        </div>
    </header>

    @if($isFrozen)
        <div class="frozen-overlay">
            <h2>🚨 {{ __('messages.frozen_title') }}</h2>
            <p class="m-0 font-13">{{ __('messages.frozen_desc_1') }} <strong>{{ number_format($saldoTotal, 2) }} FC</strong> {{ __('messages.frozen_desc_2') }}</p>
        </div>
    @endif

    <section class="grid-kpis" style="grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
        <div class="kpi-card border-purple"><span class="kpi-label">🪙 {{ __('messages.kpi_wallet') }}</span><span class="kpi-value" style="color: var(--c-green);">{{ number_format($saldoTotal, 2) }} FC</span></div>
        <div class="kpi-card border-blue"><span class="kpi-label">⚡ {{ __('messages.kpi_capacity') }}</span><span class="kpi-value">{{ $misActivos->where('status', 'active')->count() }} <span class="font-16">{{ __('messages.kpi_assets') }}</span></span></div>
        <div class="kpi-card border-red"><span class="kpi-label">🎯 {{ __('messages.kpi_missions') }}</span><span class="kpi-value">{{ $misMisiones->count() }}</span></div>
        <div class="kpi-card border-yellow"><span class="kpi-label">🎓 {{ __('messages.kpi_financed') }}</span><span class="kpi-value">{{ $totalFinanciados }}</span></div>
    </section>

    <nav class="tabs-nav">
        <button class="tab-btn active" onclick="openTab(event, 'tab-boveda')">📦 {{ __('messages.tab_vault') }}</button>
        <button class="tab-btn" onclick="openTab(event, 'tab-misiones')">🎯 {{ __('messages.tab_missions_lab') }}</button>
        <button class="tab-btn" onclick="openTab(event, 'tab-historial')">📜 {{ __('messages.tab_history') }}</button>
        <button class="tab-btn" onclick="openTab(event, 'tab-credits')">📊 {{ __('messages.tab_credits') }}</button>
        <button class="tab-btn" onclick="openTab(event, 'tab-movimientos')">💳 {{ __('messages.tab_transactions') }}</button>
        <button class="tab-btn" onclick="openTab(event, 'tab-talentos')">🧠 {{ __('messages.tab_talent') }}</button>
        <button class="tab-btn" onclick="openTab(event, 'tab-perfil-lab')">👤 {{ __('messages.tab_profile') }}</button>
    </nav>

    <div id="tab-boveda" class="tab-content active"> @include('lab.tabs.boveda') </div>
    <div id="tab-misiones" class="tab-content" style="display:none;"> @include('lab.tabs.misiones') </div>
    <div id="tab-historial" class="tab-content" style="display:none;"> @include('lab.tabs.historial') </div>
    <div id="tab-credits" class="tab-content" style="display:none;"> @include('lab.tabs.credits') </div>
    <div id="tab-movimientos" class="tab-content" style="display:none;"> @include('lab.tabs.movimientos') </div>
    <div id="tab-talentos" class="tab-content" style="display:none;"> @include('lab.tabs.talentos') </div>
    <div id="tab-perfil-lab" class="tab-content" style="display:none;"> @include('lab.tabs.perfil') </div>

</div>

<div id="modalEvaluacionGlobal" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width: 420px; text-align: left; position: relative;">
        <button type="button" onclick="document.getElementById('modalEvaluacionGlobal').style.display='none'" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: white; font-size: 20px; cursor: pointer;">✕</button>
        <h3 id="modalEvalTitulo" style="color:var(--c-blue); margin-top:0;">🎯 {{ __('messages.btn_assign_maker') }}</h3>
        <p id="modalEvalSub" class="font-12 text-muted"></p>
        <form action="{{ route('lab.complete_mission') }}" method="POST">
            @csrf
            <input type="hidden" name="mission_id" id="modalInputMision">
            <input type="hidden" name="maker_id" id="modalInputMaker">
            <label class="font-11 text-muted font-bold mb-5 d-inline-block">{{ __('messages.lbl_general_rating') }}</label>
            <select name="rating" required class="mb-15">
                <option value="5">⭐⭐⭐⭐⭐</option><option value="4">⭐⭐⭐⭐</option><option value="3">⭐⭐⭐</option>
            </select>
            <input type="text" name="comment" placeholder="Reseña del entregable..." required class="mb-15">
            <button type="submit" class="btn-mint" style="background:var(--c-green);">CONFIRMAR EVALUACIÓN</button>
        </form>
    </div>
</div>

<div id="modalReprogramarGlobal" style="display: none; position: fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.8); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width: 320px; position: relative;">
        <button type="button" onclick="document.getElementById('modalReprogramarGlobal').style.display='none'" style="position: absolute; top: 10px; right: 15px; background: none; border: none; color: white; font-size: 20px; cursor: pointer;">✕</button>
        <h3 style="margin-top:0; color: var(--c-orange);">📅 Reprogramar Reserva</h3>
        <form action="{{ route('lab.reschedule') }}" method="POST">
            @csrf <input type="hidden" name="order_id" id="modalReprogInputOrder">
            <input type="date" name="nueva_fecha" required class="mb-15">
            <button type="submit" class="btn-apply" style="background:var(--c-orange); color:#1a1a1a;">Enviar Propuesta</button>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function openTab(evt, tabName) {
        let i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) tabcontent[i].style.display = "none";
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) tablinks[i].classList.remove("active");
        document.getElementById(tabName).style.display = "block";
        if(evt) evt.currentTarget.classList.add("active");
        sessionStorage.setItem('activeTabLab', tabName);
    }
    document.addEventListener("DOMContentLoaded", function() {
        let savedTab = sessionStorage.getItem('activeTabLab');
        if (savedTab && document.getElementById(savedTab)) {
            let buttons = document.getElementsByClassName("tab-btn");
            for(let i=0; i<buttons.length; i++) {
                if(buttons[i].getAttribute('onclick').includes(savedTab)) { buttons[i].click(); break; }
            }
        }
    });
    function filtrarTalentosLive() {
        let val = document.getElementById('search-talent').value.toLowerCase();
        document.querySelectorAll('.maker-card').forEach(c => { c.style.display = c.innerText.toLowerCase().includes(val) ? 'flex' : 'none'; });
    }
    function abrirModalEvaluacion(misionId, makerId, makerName, mTitulo) {
        document.getElementById('modalInputMision').value = misionId;
        document.getElementById('modalInputMaker').value = makerId;
        document.getElementById('modalEvalSub').innerHTML = `<strong>${makerName}</strong><br>Misión: ${mTitulo}`;
        document.getElementById('modalEvaluacionGlobal').style.display = 'flex';
    }
    function abrirModalReprogramar(orderId) {
        document.getElementById('modalReprogInputOrder').value = orderId;
        document.getElementById('modalReprogramarGlobal').style.display = 'flex';
    }
</script>
@endsection
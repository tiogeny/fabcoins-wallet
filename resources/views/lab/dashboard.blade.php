@extends('layouts.app')

@section('title', __('messages.lab_portal'))

@section('content')
<div class="container">
    
    <header class="header">
        <div>
            <h1 class="m-0" style="font-family: 'Rajdhani', sans-serif; font-weight: 700;">
                🏢 {{ $lab->name }}
            </h1>
            <p class="m-0 text-muted font-12">
                📍 {{ $lab->address ?? __('messages.onb_ph_loc') }}
            </p>
        </div>
        
        <div class="flex-between" style="gap: 20px;">
            <div style="font-size: 16px;">
                <a href="?lang=es" style="opacity: {{ app()->getLocale() == 'es' ? '1' : '0.4' }}; margin-right: 8px;">🇪🇸</a>
                <a href="?lang=en" style="opacity: {{ app()->getLocale() == 'en' ? '1' : '0.4' }};">🇺🇸</a>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <a href="{{ route('logout') }}" class="btn-logout" onclick="event.preventDefault(); this.closest('form').submit();">
                    🚪 {{ __('messages.btn_logout') }}
                </a>
            </form>
        </div>
    </header>

    @if($isFrozen)
        <div class="frozen-overlay">
            <h2>🚨 {{ __('messages.frozen_title') }}</h2>
            <p class="m-0 font-13">
                {{ __('messages.frozen_desc_1') }} <strong>{{ number_format($saldoTotal, 2) }} FC</strong> {{ __('messages.frozen_desc_2') }}
            </p>
        </div>
    @endif

    <section class="grid-kpis-auto">
        <div class="kpi-card border-yellow">
            <span class="kpi-label">🪙 {{ __('messages.kpi_wallet') }}</span>
            <span class="kpi-value" style="color: var(--c-yellow);">{{ number_format($saldoTotal, 2) }} <span class="font-13">FC</span></span>
        </div>

        <div class="kpi-card border-green">
            <span class="kpi-label">⚡ {{ __('messages.lbl_total_minted_kpi') }}</span>
            <span class="kpi-value">{{ number_format($totalHistoricoEmitido, 2) }} <span class="font-13">FC</span></span>
        </div>

        <div class="kpi-card border-blue">
            <span class="kpi-label">💼 {{ __('messages.kpi_missions') }}</span>
            <span class="kpi-value">{{ $misMisiones->count() }}</span>
        </div>
    </section>

    <nav class="tabs-nav">
        <button class="tab-btn active" onclick="switchTab(event, 'inventario')">📦 {{ __('messages.tab_vault') }}</button>
        <button class="tab-btn" onclick="switchTab(event, 'misiones')">🚀 {{ __('messages.tab_missions_lab') }}</button>
        <button class="tab-btn" onclick="switchTab(event, 'historial')">⏳ {{ __('messages.tab_history') }}</button>
    </nav>

    <div id="inventario" class="tab-content active">
        <div class="card">
            <h2>🛠️ {{ __('messages.inv_title') }}</h2>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('messages.th_category') }}</th>
                            <th>{{ __('messages.th_asset') }}</th>
                            <th>{{ __('messages.th_avail_capacity') }}</th>
                            <th>{{ __('messages.th_price_hr') }}</th>
                            <th>{{ __('messages.th_status') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($misActivos as $activo)
                            <tr>
                                <td><span class="badge badge-blue">{{ $activo->asset_type }}</span></td>
                                <td><strong>{{ $activo->custom_name }}</strong></td>
                                <td>{{ number_format($activo->useful_life_hours - $activo->consumed_hours, 2) }} h</td>
                                <td>{{ number_format($activo->set_price_fc, 2) }} FC</td>
                                <td>
                                    <span class="badge {{ $activo->status === 'active' ? 'badge-green' : 'badge-gray' }}">
                                        {{ $activo->status === 'active' ? __('messages.status_operative') : __('messages.status_retired') }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-muted" style="text-align: center; padding: 30px;">
                                    📭 {{ __('messages.inv_empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="misiones" class="tab-content">
        <div class="card">
            <h2>💼 {{ __('messages.miss_list_title') }}</h2>
            <p class="text-muted font-13">{{ __('messages.miss_list_empty') }}</p>
        </div>
    </div>

    <div id="historial" class="tab-content">
        <div class="card">
            <h2>🧾 {{ __('messages.tab_transactions') }}</h2>
            <p class="text-muted font-13">{{ __('messages.no_notifications') }}</p>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    // Lógica interactiva responsiva para conmutar las pestañas sin recargar la página
    function switchTab(evt, tabName) {
        let i, tabcontent, tablinks;
        tabcontent = document.getElementsByClassName("tab-content");
        for (i = 0; i < tabcontent.length; i++) {
            tabcontent[i].classList.remove("active");
        }
        tablinks = document.getElementsByClassName("tab-btn");
        for (i = 0; i < tablinks.length; i++) {
            tablinks[i].classList.remove("active");
        }
        document.getElementById(tabName).classList.add("active");
        evt.currentTarget.classList.add("active");
    }
</script>
@endsection

@section('body-class', 'theme-lab is-active')
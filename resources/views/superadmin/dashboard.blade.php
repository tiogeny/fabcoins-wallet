@extends('layouts.app')

@section('title', __('messages.title_superadmin_dashboard'))

@section('content')
<div class="profile-container-v2">
    
    <!-- 🎛️ ENCABEZADO INDUSTRIAL DE GOBERNANZA -->
    <div class="lab-header-v2">
        <div class="lab-profile-trigger">
            <div class="lab-identity-meta">
                <h1>
                    {{ __('messages.title_superadmin_dashboard') }} 
                    <span class="badge-semantic badge-service">[SUPERADMIN]</span>
                </h1>
                <div class="td-date-dim">
                    {{ __('messages.lbl_assigned_creator') }}: <strong class="text-white-pure">{{ $superadmin->name }}</strong>
                </div>
            </div>
        </div>
        <div>
            <form method="POST" action="{{ route('logout') }}" class="m-0">
                @csrf
                <button type="submit" class="btn-logout-v2">{{ __('messages.btn_logout') ?? '🔒 Cerrar Sesión' }}</button>
            </form>
        </div>
    </div>

    <!-- NOTIFICACIONES TEMPORALES DEL CORE -->
    <div id="global-alert">
        @if(session('msg') == 'lab_ok') <div class="badge-semantic badge-status-operative w-100 mb-20 text-center block-16">🏢 {{ __('messages.msg_lab_ok') }}</div> @endif
        @if(session('msg') == 'cat_ok') <div class="badge-semantic badge-service w-100 mb-20 text-center block-16">📦 {{ __('messages.msg_cat_ok') }}</div> @endif
        @if(session('msg') == 'pct_updated') <div class="badge-semantic badge-machine w-100 mb-20 text-center block-16">📈 {{ __('messages.msg_pct_updated') }}</div> @endif
        @if(session('msg') == 'precio_ok') <div class="badge-semantic badge-status-operative w-100 mb-20 text-center block-16">💾 {{ __('messages.msg_precio_ok') }}</div> @endif
        @if(session('msg') == 'borrado_ok') <div class="badge-semantic badge-status-retired w-100 mb-20 text-center block-16">🗑️ {{ __('messages.msg_borrado_ok') }}</div> @endif
        @if(session('error')) <div class="badge-alert-neon w-100 mb-20 text-center block-16">❌ {{ session('error') }}</div> @endif
        @if(session('msg') == 'skill_ok') <div class="badge-semantic badge-service w-100 mb-20 text-center block-16">🧠 Habilidad registrada exitosamente en la base de datos global.</div> @endif
    </div>

    <!-- =======================================================================
         🪙 HUB I: ESTABILIDAD MACROECONÓMICA & EMISIÓN (FÓRMULA MAESTRA)
         ======================================================================= -->
    <div class="premium-glass-card hub-bar-yellow" style="padding: 26px;">
        <div class="premium-glass-card-header" style="margin-bottom: 12px;">
            <h3 class="premium-glass-card-title m-0 hub-text-yellow">{{ __('messages.hub1_title') }}</h3>
        </div>
        <p class="premium-glass-card-subtitle" style="margin-bottom: 25px;">{{ __('messages.hub1_desc') }}</p>
        
        <div style="width: 100%; text-align: center; margin-bottom: 25px;">
            <div class="creator-asset-card cursor-pointer" style="border: 2px solid #ffffff; max-width: 450px; margin: 0 auto; padding: 20px; background: rgba(255,255,255,0.01);" onclick="cargarDesglose('mint', '{{ __('messages.lbl_minted_mass') }}', '🪙')">
                <div class="stat-label" style="font-size: 11px; letter-spacing: 1px;">{{ __('messages.lbl_minted_mass') }}</div>
                <div class="stat-value" style="color: #ffffff; font-size: 32px; font-weight: 800; margin-top: 5px;">🪙 {{ number_format($total_fc, 0) }}</div>
                <div class="td-date-dim" style="margin-top: 10px; font-size: 10px;">✨ {{ __('messages.lbl_minted_mass_click') }}</div>
            </div>
        </div>

        <div style="background: #131722; border: 1px dashed rgba(255, 255, 255, 0.06); padding: 20px; border-radius: 12px; margin-bottom: 25px;">
            <div style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; text-align: left;">
                ⚖️ COMPONENTES COMPROMETIDOS DE LA BALANZA (SUMA EQUIVALENTE AL 100%)
            </div>
            
            <div class="action-hubs-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; min-height: auto; margin: 0;">
                <div class="creator-asset-card hub-bar-blue cursor-pointer" style="background: #1c2230;" onclick="cargarDesglose('bovedas', '{{ __('messages.lbl_vault_reservations') }}', '🏦')">
                    <div class="stat-label">{{ __('messages.lbl_vault_reservations') }}</div>
                    <div class="stat-value text-blue-neon" style="font-size: 20px;">🏦 {{ number_format($total_bovedas, 0) }}</div>
                    <div class="td-date-dim style-custom-mtop" style="margin-top:12px; font-size:10px;">{{ __('messages.lbl_vault_click') }}</div>
                </div>

                <div class="creator-asset-card hub-bar-green cursor-pointer" style="background: #1c2230;" onclick="cargarDesglose('circulante', '{{ __('messages.lbl_circulating_creators') }}', '🥮')">
                    <div class="stat-label">{{ __('messages.lbl_circulating_creators') }}</div>
                    <div class="stat-value text-green-neon" style="font-size: 20px;">🥮 {{ number_format($total_circulando, 0) }}</div>
                    <div class="td-date-dim style-custom-mtop" style="margin-top:12px; font-size:10px;">{{ __('messages.lbl_circulating_click') }}</div>
                </div>

                <div class="creator-asset-card hub-bar-yellow cursor-pointer" style="background: #1c2230;" onclick="cargarDesglose('escrow', '{{ __('messages.lbl_total_escrow') }}', '🔒')">
                    <div class="stat-label">{{ __('messages.lbl_total_escrow') }}</div>
                    <div class="stat-value" style="color: #f1c40f !important; font-size: 20px;">🔒 {{ number_format($total_escrow, 0) }}</div>
                    <div class="td-date-dim style-custom-mtop" style="margin-top:12px; font-size:10px;">{{ __('messages.lbl_escrow_click') }}</div>
                </div>

                <div class="creator-asset-card cursor-pointer" style="background: #1c2230; border-bottom: 2px solid #e67e22;" onclick="cargarDesglose('burn', 'Tokens Consumidos', '🔥')">
                    <div class="stat-label">Tokens Consumidos (Deflación)</div>
                    <div class="stat-value" style="color: #e67e22 !important; font-size: 20px;">🔥 {{ number_format($total_quemado, 0) }}</div>
                    <div class="td-date-dim style-custom-mtop" style="margin-top:12px; font-size:10px;">{{ __('messages.lbl_burn_click') }}</div>
                </div>
            </div>
        </div>

        <div class="action-hubs-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; min-height: auto; margin-top: 0; border-top: 1px solid rgba(255,255,255,0.04); padding-top: 20px;">
            <div class="creator-asset-card hub-bar-blue cursor-pointer" style="background: rgba(52, 152, 219, 0.01);" onclick="cargarDesglose('pib', '{{ __('messages.lbl_pib_30d') }}', '⚡')">
                <div class="stat-label">{{ __('messages.lbl_pib_30d') }}</div>
                <div class="stat-value text-blue-neon" style="font-size: 22px;">⚡ {{ number_format($volumen_30d, 0) }} FC</div>
                <div class="td-date-dim style-custom-mtop" style="margin-top:10px; font-size:10px;">{{ __('messages.lbl_pib_click') }}</div>
            </div>

            <div class="creator-asset-card cursor-pointer" style="border-bottom: 2px solid #cbd5e0; background: rgba(255,255,255,0.01);" onclick="cargarDesglose('velocidad', '{{ __('messages.lbl_absorption_rate') }}', '📉')">
                <div class="stat-label">{{ __('messages.lbl_absorption_rate') }}</div>
                <div class="stat-value text-white-pure" style="font-size: 22px;">📉 {{ number_format($tasa_absorcion, 2) }}%</div>
                <div class="td-date-dim style-custom-mtop" style="margin-top:10px; font-size:10px;">{{ __('messages.lbl_absorption_click') }}</div>
            </div>
        </div>
    </div>

    <div class="profile-panoramic-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        
        <div class="premium-glass-card hub-bar-green" style="margin-bottom: 0;">
            <h2 class="premium-glass-card-title" style="font-size: 16px; margin-bottom: 12px;">{{ __('messages.lbl_invite_incorporate_node') }}</h2>
            <form method="POST" action="{{ route('superadmin.lab.invite') }}" class="flex-col-gap-10">
                @csrf
                
                <div class="grid-mission-inputs" style="grid-template-columns: 1fr 1fr;">
                    <div>
                        <label class="premium-label">{{ __('messages.lbl_institution_name') }}</label>
                        <input type="text" name="name" placeholder="Ej: Fab Lab Lima" class="premium-input" required>
                    </div>
                    <div>
                        <label class="premium-label">{{ __('messages.lbl_official_email') }}</label>
                        <input type="email" name="email" placeholder="lab@institucion.org" class="premium-input" required>
                    </div>
                </div>

                <div class="grid-mission-inputs" style="grid-template-columns: 1fr; margin-top: 5px;">
                    <div>
                        <label class="premium-label">{{ __('messages.lbl_node_lang') }}</label>
                        <select name="lab_lang" class="premium-select">
                            <option value="es">Español (ES)</option>
                            <option value="en">English (EN)</option>
                        </select>
                    </div>
                </div>
                
                <div class="text-right" style="margin-top: 10px;">
                    <button type="submit" class="btn-premium btn-green-hub w-100" style="background:#2ecc71; border-color:#2ecc71;">{{ __('messages.btn_dispatch_invite') }}</button>
                </div>
            </form>
        </div>
        <div class="premium-glass-card hub-bar-green" style="margin-bottom: 0;">
            <div>
                <h2 class="premium-glass-card-title" style="font-size: 16px; margin-bottom: 8px;">🔍 Auditoría Clínica Inmediata</h2>
                <p class="td-date-dim" style="font-size: 11px; line-height: 1.4;">Inspecciona los libros contables y desgloses de saldo líquido de cualquier nodo o creador sin comprometer sus claves de acceso.</p>
            </div>
            <div style="margin-top:10px;">
                <label class="premium-label">Selecciona el nodo o cuenta a auditar</label>
                <select id="select-auditoria-usuario" class="premium-select" onchange="if(this.value) auditarUsuarioDirecto(this.value, this.options[this.selectedIndex].text)" style="border-color: rgba(52,152,219,0.3);">
                    <option value="">-- Buscar cuenta por email o nombre --</option>
                    @foreach($todos_los_usuarios as $usr)
                        <option value="{{ $usr->id }}">{{ $usr->role === 'lab' ? '🏭' : '👤' }} {{ $usr->name }} ({{ $usr->emailBlock ?? $usr->email }})</option>
                    @endforeach
                </select>
            </div>
        </div>

    </div>

    <!-- 🔍 CONSOLA DE AUDITORÍA CLÍNICA DE NODOS -->
    

    <!-- =======================================================================
         📊 HUB II: CAPACIDAD PRODUCTIVA (DPI) & AUDITORÍA DE CRÉDITOS ISA
         ======================================================================= -->
    <div class="profile-panoramic-grid">
        
        <div class="premium-glass-card hub-bar-green" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div class="premium-glass-card-header">
                    <h3 class="premium-glass-card-title m-0 hub-text-green">{{ __('messages.hub2_title') }}</h3>
                </div>
                <p class="premium-glass-card-subtitle">{{ __('messages.hub2_desc') }}</p>
                
                <div class="grid-mission-inputs">
                    <div class="modal-info-box m-0">
                        <div class="modal-rating-label">{{ __('messages.lbl_machine_potential') }}</div>
                        <div class="td-amount-gold text-success-neon font-rajdhani-15" style="font-size: 20px;">
                            {{ number_format($capacidad_horas, 0) }} Hrs
                        </div>
                    </div>
                    <div class="modal-info-box m-0">
                        <div class="modal-rating-label">{{ __('messages.lbl_active_density') }}</div>
                        <div class="text-white-pure font-bold font-rajdhani-15" style="font-size: 13px; margin-top: 5px;">
                            🏭 {{ $total_labs_count }} {{ $total_labs_count == 1 ? __('messages.lbl_lab_singular') : __('messages.lbl_labs_plural') }} / 
                            {{ $total_creators_count }} {{ __('messages.lbl_creators_plural') }}
                        </div>
                    </div>
                </div>

                <div class="modal-info-box" style="margin-top: 15px; margin-bottom: 0;">
                    <div class="modal-rating-label">{{ __('messages.lbl_inventory_mix') }}</div>
                    <div class="td-concept-desc font-rajdhani-15" style="margin-top: 8px; font-size: 13px; display: flex; gap: 20px;">
                        <span>⚙️ {{ __('messages.lbl_equipments_physical') }}: <strong class="text-white-pure">{{ $mix['machine'] ?? 0 }}</strong></span>
                        <span>🧠 {{ __('messages.lbl_services_talent') }}: <strong class="text-white-pure">{{ $mix['service'] ?? 0 }}</strong></span>
                    </div>
                </div>
            </div>

            <div class="materials-block" style="margin-top: 15px;">
                <div class="materials-title">{{ __('messages.lbl_historical_value_nature') }}</div>
                <div class="td-concept-desc" style="font-size: 12px; line-height: 1.8; margin-top: 5px;">
                    🔹 {{ __('messages.lbl_by_infrastructure') }}: <strong class="text-white-pure">{{ number_format($val_equipos, 0) }} FC</strong><br>
                    🔹 {{ __('messages.lbl_by_missions') }}: <strong class="text-white-pure">{{ number_format($val_talento, 0) }} FC</strong>
                </div>
            </div>
        </div>

        <div class="premium-glass-card hub-bar-yellow" style="margin-bottom: 0;">
            <div class="premium-glass-card-header">
                <h3 class="premium-glass-card-title m-0 text-warning-neon">{{ __('messages.lbl_isa_global_debt') }}</h3>
                <span class="badge-semantic badge-machine" style="background: rgba(241,196,15,0.1); color: #f1c40f !important; border: 1px solid rgba(241,196,15,0.2); font-weight: 700;">Activos: {{ number_format($total_deuda_global, 0) }} FC</span>
            </div>
            <p class="premium-glass-card-subtitle">{{ __('messages.lbl_isa_global_desc') }}</p>

            <div class="table-container" style="max-height: 230px;">
                <table class="premium-data-table">
                    <thead>
                        <tr>
                            <th>{{ __('messages.lbl_financial_applicant') }}</th>
                            <th class="text-right">{{ __('messages.lbl_amortization_balance') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($creators_financiados->isEmpty())
                            <tr><td colspan="2" class="empty-state">No se registran financiamientos activos en la red.</td></tr>
                        @else
                            @foreach($creators_financiados as $mf) 
                                @php $prog = $mf->amount_initial > 0 ? round((($mf->amount_initial - $mf->amount_remaining) / $mf->amount_initial) * 100) : 0; @endphp
                            <tr>
                                <td>
                                    <strong class="text-white-pure">👤 {{ $mf->creator_name }}</strong>
                                    <div class="td-creator-email">{{ __('messages.lbl_creditor') }}: {{ $mf->lab_name }}</div>
                                </td>
                                <td class="text-right" style="vertical-align: middle;">
                                    <strong class="td-amount-gold">{{ number_format($mf->amount_remaining, 0) }} FC</strong>
                                    <div style="display: flex; align-items: center; justify-content: flex-end; gap: 8px; margin-top: 5px;">
                                        <span class="td-date-dim" style="font-size: 10px;">{{ $prog }}% {{ __('messages.lbl_returned_percentage') }}</span>
                                        <div class="asset-bar-bg" style="width: 60px; height: 4px;">
                                            <div class="asset-bar-fill" style="width: {{ $prog }}%; background: var(--c-yellow);"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- =======================================================================
         ⚙️ HUB III: GOBERNANZA GLOBAL & PANEL OPERATIVO DE CONFIGURACIONES
         ======================================================================= --> 
    

    <!-- CARGA DE ÍTEMS AL CATÁLOGO -->
    <div class="premium-glass-card">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title m-0">{{ __('messages.lbl_multi_line_structuring') }}</h3>
        </div>
        <p class="premium-glass-card-subtitle">{{ __('messages.lbl_multi_line_desc') }}</p>
        
        <form method="POST" action="{{ route('superadmin.catalog.store') }}" id="form-catalogo">
            @csrf
            
            <div class="grid-headers-enlistar" style="grid-template-columns: 1.2fr 2fr 2fr 1fr 1fr 40px; font-size:11px; font-weight:700;">
                <label>{{ __('messages.lbl_asset_nature') }}</label>
                <label>{{ __('messages.lbl_multi_line_structuring') ?? 'Nombre (ES)' }}</label>
                <label>Nombre (EN)</label>
                <label>Unidad</label>
                <label>{{ __('messages.lbl_ref_price_fc') }}</label>
                <div></div>
            </div>

            <div id="contenedor-filas-admin">
                <div class="row-catalogo row-token-enlistar" style="grid-template-columns: 1.2fr 2fr 2fr 1fr 1fr 40px; gap:10px;">
                    <select name="asset_type[]" required onchange="cambiarPlaceholdersAdmin(this)" class="premium-select">
                        <option value="machine">⚙️ {{ __('messages.opt_machine') }}</option>
                        <option value="service">🧠 {{ __('messages.opt_service') }}</option>
                        <option value="lab">🏭 {{ __('messages.lbl_lab_singular') }}</option>
                    </select>
                    <input type="text" name="generic_name_es[]" class="input-nombre-admin premium-input" placeholder="Ej: Impresora 3D Resina" required>
                    <input type="text" name="generic_name_en[]" class="input-nombre-admin-en premium-input" placeholder="Eg: Resin 3D Printer" required>
                    <input type="text" name="measurement_unit[]" class="input-unidad-admin premium-input" placeholder="Ej: hora" value="hora" required>
                    <input type="number" step="0.01" name="suggested_price_fc[]" class="premium-input" placeholder="10.00" required>
                    <div class="espaciador-borrar"></div>
                </div>
            </div>

            <div class="form-actions-row">
                <button type="button" onclick="agregarFilaAdmin()" class="btn-back-minimal">➕</button>
                <button type="submit" class="btn-premium btn-yellow-hub" style="flex-grow: 1; width: auto !important; margin:0;">{{ __('messages.btn_confirm_sync_lines') }}</button>
            </div>
        </form>
    </div>

    

    <!-- TABLA DE LISTADO DE REFERENCIA DEL MERCADO GLOBAL -->
    <div class="premium-glass-card">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title m-0">{{ __('messages.lbl_homologated_prices') }}</h3>
        </div>
        <div class="table-container">
            <table class="premium-data-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.lbl_asset_nature') }}</th>
                        <th>{{ __('messages.lbl_descriptor_unit') }}</th>
                        <th>{{ __('messages.lbl_ref_price_fc') }}</th>
                        <th class="text-right">{{ __('messages.lbl_control_actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($catalogo as $cat)
                    <tr>
                        <td>
                            @php 
                                $badgeClass = 'badge-machine'; 
                                $texto = __('messages.opt_machine');
                                
                                if ($cat->asset_type == 'service') { 
                                    $badgeClass = 'badge-service'; 
                                    $texto = __('messages.opt_service'); 
                                } elseif ($cat->asset_type == 'lab') { 
                                    $badgeClass = 'badge-space'; 
                                    $texto = __('messages.lbl_lab_singular'); 
                                }
                            @endphp
                            <span class="badge-semantic {{ $badgeClass }}">
                                @if($cat->asset_type == 'machine') ⚙️ @endif
                                @if($cat->asset_type == 'service') 🧠 @endif
                                @if($cat->asset_type == 'lab') 🏭 @endif
                                {{ strtoupper($texto) }}
                            </span>
                        </td>
                        <td>
                            <strong class="text-white-pure">{{ $cat->generic_name }}</strong> <span class="td-date-dim">(ES)</span><br>
                            <span class="td-date-dim" style="font-size: 12px;">{{ $cat->generic_name_en }}</span> <span class="td-date-dim">(EN)</span><br>
                            <span class="text-date-highlight">Cobro por: {{ $cat->measurement_unit }}</span>
                        </td>
                        <td style="vertical-align: middle;">
                            <input type="number" step="0.01" name="nuevo_precio" value="{{ $cat->suggested_price_fc }}" form="form_cat_{{ $cat->id }}" class="input-rate-update">
                        </td>
                        <td>
                            <div style="display:flex; gap:8px; justify-content:flex-end;">
                                <form id="form_cat_{{ $cat->id }}" method="POST" action="{{ route('superadmin.catalog.update') }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="cat_id" value="{{ $cat->id }}">
                                    <button type="submit" class="btn-back-minimal btn-min-eval-gold" style="height:32px; line-height:1;">💾</button>
                                </form>
                                <form method="POST" action="{{ route('superadmin.catalog.destroy') }}" class="m-0">
                                    @csrf
                                    <input type="hidden" name="cat_id" value="{{ $cat->id }}">
                                    <button type="submit" class="btn-back-minimal" style="background:rgba(231,76,60,0.1) !important; color:#e74c3c !important; border-color:rgba(231,76,60,0.15) !important; height:32px; line-height:1;" onclick="return confirm('¿Confirmas la remoción absoluta del descriptor?');">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="premium-glass-card hub-bar-blue" style="padding: 26px;">
        <h2 class="premium-glass-card-title" style="font-size: 16px; margin-bottom: 12px;">🧠 {{ __('messages.title_register_skills') }}</h2>
        
        <form method="POST" action="{{ route('superadmin.skills.store_multiple') }}">
            @csrf
            
            <div id="contenedor-filas-habilidades" style="width: 100%;">
                <div class="row-token-enlistar" style="display: grid; grid-template-columns: 2.5fr 2.5fr 2fr auto; gap: 10px; margin-bottom: 10px; background: transparent; padding: 0;">
                    <div>
                        <input type="text" name="name_es[]" placeholder="Competencia (ES)" class="premium-input" style="margin-bottom:0;" required>
                    </div>
                    <div>
                        <input type="text" name="name_en[]" placeholder="Skill Name (EN)" class="premium-input" style="margin-bottom:0;" required>
                    </div>
                    <div>
                        <select name="type[]" class="premium-select" style="margin-bottom:0;">
                            <option value="hard">⚙️ {{ __('messages.lbl_hard_skill') }}</option>
                            <option value="soft">🧠 {{ __('messages.lbl_soft_skill') }}</option>
                        </select>
                    </div>
                    <div style="width: 30px;"></div> </div>
            </div>

            <div class="form-actions-row" style="display: flex; gap: 10px; margin-top: 15px;">
                <button type="button" onclick="agregarFilaHabilidadGobernanza()" class="btn-back-minimal" style="width: auto;">+ {{ __('messages.btn_add_more') }}</button>
                <button type="submit" class="btn-premium btn-blue-hub" style="margin: 0; flex: 1;">💾 {{ __('messages.btn_save_skill') }}</button>
            </div>
        </form>
    </div>

    <div class="premium-glass-card hub-bar-yellow" style="padding: 26px;">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title m-0">📋 Habilidades Activas en el Mercado</h3>
        </div>
        <div class="table-container">
            <table class="premium-data-table">
                <thead>
                    <tr>
                        <th>Naturaleza</th>
                        <th>Competencia (Español)</th>
                        <th>Competency Name (English)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $todasLasSkills = DB::table('skills')->orderBy('type', 'asc')->orderBy('name_es', 'asc')->get();
                    @endphp
                    @forelse($todasLasSkills as $s)
                        <tr>
                            <td>
                                @if($s->type === 'hard')
                                    <span class="badge-semantic badge-machine">⚙️ HARD SKILL</span>
                                @else
                                    <span class="badge-semantic badge-lab">🧠 SOFT SKILL</span>
                                @endif
                            </td>
                            <td class="text-white-pure font-bold">{{ $s->name_es }}</td>
                            <td class="text-muted">{{ $s->name_en }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="empty-state">No hay habilidades registradas en el catálogo global aún.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- SECCIÓN PANORÁMICA DE REPUTACIÓN Y OPERACIONES EN RED -->
    <div class="action-hubs-grid" style="align-items: start; margin-bottom: 26px;">
        
        <!-- TOP LABS -->
        <div class="premium-glass-card m-0">
            <h2 class="premium-glass-card-title" style="font-size: 15px; margin-bottom: 15px;">{{ __('messages.lbl_top_five_labs') }}</h2>
            <table class="premium-data-table">
                @foreach($top_labs as $l)
                <tr class="cursor-pointer" onclick="auditarUsuarioDirecto({{ $l->id }}, '{{ $l->name }}')">
                    <td><strong class="text-white-pure">🏭 {{ $l->name }}</strong><div style="font-size:9px; color:#3498db; text-transform:uppercase;">Click para auditar kpis</div></td>
                    <td class="td-amount-gold text-right">⭐ {{ number_format($l->reputation_score, 1) }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <!-- TOP CREATORS -->
        <div class="premium-glass-card m-0">
            <h2 class="premium-glass-card-title" style="font-size: 15px; margin-bottom: 15px;">{{ __('messages.lbl_top_five_creators') }}</h2>
            <table class="premium-data-table">
                @foreach($top_creators as $f)
                <tr class="cursor-pointer" onclick="auditarUsuarioDirecto({{ $f->id }}, '{{ $f->name }}')">
                    <td><strong class="text-white-pure">👤 {{ $f->name }}</strong><div style="font-size:9px; color:#3498db; text-transform:uppercase;">Click para auditar kpis</div></td>
                    <td class="td-amount-gold text-right">⭐ {{ number_format($f->reputation_score, 1) }}</td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>

    <!-- ÚLTIMAS TRANSACCIONES -->
    <div class="premium-glass-card hub-bar-yellow" style="padding: 26px;">
        <h2 class="premium-glass-card-title" style="font-size: 15px; margin-bottom: 15px;">{{ __('messages.lbl_last_ledger_operations') }}</h2>
        <div class="table-container" style="max-height: 220px; overflow-y: auto;">
            <table class="premium-data-table">
                @foreach($ultimas_tx as $tx)
                <tr>
                    <td>
                        <strong class="text-success-neon font-rajdhani-15" style="font-size:14px;">{{ number_format($tx->amount, 0) }} FC</strong><br>
                        <span class="td-date-dim" style="font-size: 11px;">{{ $tx->user_name }}</span>
                    </td>
                    <td class="td-concept-desc" style="font-size: 11.5px; line-height:1.3;">{{ $tx->description }}</td>
                </tr>
                @endforeach
            </table>
        </div>
    </div>
    
    <!-- RADAR DE MISIONES DE CO-CREACIÓN -->
    <div class="premium-glass-card hub-bar-pink" style="margin-top:25px;">
        <div class="premium-glass-card-header">
            <h3 class="premium-glass-card-title m-0 text-pink-neon">{{ __('messages.lbl_global_missions_radar') }}</h3>
        </div>
        <div class="table-container">
            <table class="premium-data-table">
                <thead>
                    <tr style="font-size: 10px; color: #7f8c8d; text-transform: uppercase;">
                        <th>{{ __('messages.lbl_issuer_node') }}</th>
                        <th>{{ __('messages.lbl_mission_objective') }}</th>
                        <th>{{ __('messages.lbl_financial_reward') }}</th>
                        <th>{{ __('messages.lbl_deadline_date') }}</th>
                        <th class="text-right">{{ __('messages.lbl_operative_status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($radar_misiones->isEmpty())
                        <tr><td colspan="5" class="empty-state">No se detectan misiones registradas en el radar.</td></tr>
                    @endif
                    @foreach($radar_misiones as $rm)
                        <tr>
                            <td><strong class="text-white-pure">🏭 {{ $rm->lab_name }}</strong></td>
                            <td class="td-creator-name text-white-pure">{{ $rm->title }}</td>
                            <td class="td-amount-gold" style="font-size:14px;">{{ number_format($rm->reward_fc, 0) }} FC</td>
                            <td class="td-date-dim">📅 {{ date('d M Y', strtotime($rm->deadline)) }}</td>
                            <td class="text-right">
                                @php 
                                    $badgeStatusClass = 'badge-status-enlisted';
                                    if ($rm->status == 'open') $badgeStatusClass = 'badge-service';
                                    if ($rm->status == 'assigned') $badgeStatusClass = 'badge-machine';
                                    if ($rm->status == 'completed') $badgeStatusClass = 'badge-status-operative';
                                @endphp
                                <span class="badge-semantic {{ $badgeStatusClass }}">{{ strtoupper($rm->status) }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    if (window.location.search.includes('msg=')) {
        const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
        window.history.replaceState({path: cleanUrl}, '', cleanUrl);
    }
    setTimeout(() => {
        let alertBox = document.getElementById('global-alert');
        if(alertBox) alertBox.style.display = 'none';
    }, 5000);
});

function cambiarPlaceholdersAdmin(selector) {
    let fila = selector.closest('.row-catalogo');
    let tipo = selector.value;
    let inputNombreES = fila.querySelector('.input-nombre-admin');
    let inputNombreEN = fila.querySelector('.input-nombre-admin-en');
    let inputUnidad = fila.querySelector('.input-unidad-admin');

    if (tipo === 'machine') {
        inputNombreES.placeholder = "Ej: Impresora 3D Resina";
        inputNombreEN.placeholder = "Eg: Resin 3D Printer";
        inputUnidad.value = "hora";
    } else if (tipo === 'service') {
        inputNombreES.placeholder = "Ej: Consultoría Arduino";
        inputNombreEN.placeholder = "Eg: Arduino Consulting";
        inputUnidad.value = "hora";
    } else if (tipo === 'lab') {
        inputNombreES.placeholder = "Ej: Uso de Espacio Completo";
        inputNombreEN.placeholder = "Eg: Full Space Usage";
        inputUnidad.value = "hora";
    }
}

function agregarFilaAdmin() {
    let contenedor = document.getElementById('contenedor-filas-admin');
    let filaOriginal = contenedor.querySelector('.row-catalogo');
    let nuevaFila = filaOriginal.cloneNode(true);
    
    nuevaFila.querySelectorAll('input').forEach(input => input.value = '');
    nuevaFila.querySelector('.input-unidad-admin').value = 'hora';
    
    let espaciador = nuevaFila.querySelector('.espaciador-borrar');
    if (espaciador) {
        espaciador.innerHTML = '<button type="button" onclick="this.parentElement.parentElement.remove()" class="btn-delete-icon">&times;</button>';
    }
    contenedor.appendChild(nuevaFila);
}

function cargarDesglose(tipo, titulo, icono) {
    Swal.fire({
        title: icono + ' ' + titulo,
        html: '⏳ Consultando registros analíticos del libro mayor...',
        background: '#1c2230',
        color: '#fff',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch(`{{ route('superadmin.ajax_desglose') }}?ajax_desglose=${tipo}`)
        .then(response => response.text())
        .then(html => {
            Swal.fire({
                title: icono + ' ' + titulo,
                html: html,
                background: '#1c2230',
                color: '#fff',
                width: '680px',
                confirmButtonColor: '#3498db',
                confirmButtonText: 'Entendido'
            });
        });
}

function auditarUsuarioDirecto(userId, userName) {
    if(!userId) return;
    
    Swal.fire({
        title: '🔍 Compilando Historial de Red',
        html: 'Abriendo canales de auditoría contable para ' + userName + '...',
        background: '#1c2230',
        color: '#fff',
        showConfirmButton: false,
        allowOutsideClick: false,
        didOpen: () => { Swal.showLoading(); }
    });

    fetch(`{{ route('superadmin.ajax_desglose') }}?ajax_desglose=audit_user&user_id=${userId}`)
        .then(response => response.text())
        .then(html => {
            Swal.fire({
                title: '🛡️ Estado de Cuenta Administrativo',
                html: html,
                background: '#1c2230',
                color: '#fff',
                width: '820px',
                confirmButtonColor: '#3498db',
                confirmButtonText: 'Concluir Auditoría',
                didClose: () => {
                    document.getElementById('select-auditoria-usuario').value = '';
                }
            });
        });
}

function agregarFilaHabilidadGobernanza() {
    const contenedor = document.getElementById('contenedor-filas-habilidades');
    const nuevaFila = document.createElement('div');
    nuevaFila.className = 'row-token-enlistar';
    nuevaFila.style = 'display: grid; grid-template-columns: 2.5fr 2.5fr 2fr auto; gap: 10px; margin-bottom: 10px; background: transparent; padding: 0;';

    nuevaFila.innerHTML = `
        <div>
            <input type="text" name="name_es[]" placeholder="Competencia (ES)" class="premium-input" style="margin-bottom:0;" required>
        </div>
        <div>
            <input type="text" name="name_en[]" placeholder="Skill Name (EN)" class="premium-input" style="margin-bottom:0;" required>
        </div>
        <div>
            <select name="type[]" class="premium-select" style="margin-bottom:0;">
                <option value="hard">⚙️ {{ __('messages.lbl_hard_skill') }}</option>
                <option value="soft">🧠 {{ __('messages.lbl_soft_skill') }}</option>
            </select>
        </div>
        <div>
            <button type="button" onclick="this.closest('.row-token-enlistar').remove()" class="btn-delete-icon" style="padding: 4px 8px; font-size: 16px;">&times;</button>
        </div>
    `;
    contenedor.appendChild(nuevaFila);
}
</script>
@endsection
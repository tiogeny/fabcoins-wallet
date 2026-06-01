<h3 style="margin: 25px 0 15px 0; font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px;">💰 Macroeconomía y Circulación</h3>
<div class="grid-kpis-auto" style="margin-bottom: 25px;">
    
    <div class="kpi-card border-yellow" style="cursor: pointer; transition: 0.2s;" onclick="cargarDesglose('mint', 'Masa Monetaria (Emisión)', '🪙')" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
        <div class="kpi-label">Masa Monetaria (Emitido)</div>
        <div class="kpi-value">🪙 {{ number_format($total_fc, 0) }}</div>
        <div style="font-size: 10px; color: var(--text-muted); margin-top: 5px;">🔍 Click para desglose</div>
    </div>
    
    <div class="kpi-card border-dark-purple" style="cursor: pointer; transition: 0.2s;" onclick="cargarDesglose('bovedas', 'Reservas en Bóvedas', '🏦')" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
        <div class="kpi-label">En Bóvedas (Labs)</div>
        <div class="kpi-value">🏦 {{ number_format($total_bovedas, 0) }}</div>
        <div style="font-size: 10px; color: var(--text-muted); margin-top: 5px;">🔍 Click para desglose</div>
    </div>
    
    <div class="kpi-card border-green" style="cursor: pointer; transition: 0.2s;" onclick="cargarDesglose('circulante', 'Makers con Liquidez', '🥮')" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
        <div class="kpi-label">Circulante (Makers)</div>
        <div class="kpi-value">🥮 {{ number_format($total_circulando, 0) }}</div>
        <div style="font-size: 10px; color: var(--text-muted); margin-top: 5px;">🔍 Click para desglose</div>
    </div>
    
    <div class="kpi-card border-red" style="cursor: pointer; transition: 0.2s;" onclick="cargarDesglose('escrow', 'Fondos Congelados', '🔒')" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
        <div class="kpi-label">Total en Escrow</div>
        <div class="kpi-value">🔒 {{ number_format($total_escrow, 0) }}</div>
        <div style="font-size: 10px; color: var(--text-muted); margin-top: 5px;">🔍 Click para desglose</div>
    </div>
    
    <div class="kpi-card border-orange" style="cursor: pointer; transition: 0.2s;" onclick="cargarDesglose('burn', 'Registro de Quema', '🔥')" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
        <div class="kpi-label">Deflación (Burn FC)</div>
        <div class="kpi-value" style="color: var(--c-orange);">🔥 {{ number_format($total_quemado, 0) }}</div>
        <div style="font-size: 10px; color: var(--text-muted); margin-top: 5px;">🔍 Click para desglose</div>
    </div>
    
    <div class="kpi-card border-blue" style="cursor: pointer; transition: 0.2s;" onclick="cargarDesglose('pib', 'PIB de 30 Días', '⚡')" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
        <div class="kpi-label">PIB Red (30 Días)</div>
        <div class="kpi-value" style="color: var(--c-blue);">⚡ {{ number_format($volumen_30d, 0) }}</div>
        <div style="font-size: 10px; color: var(--text-muted); margin-top: 5px;">🔍 Click para desglose</div>
    </div>
    
    <div class="kpi-card border-yellow" style="background: rgba(241, 196, 15, 0.02); cursor: pointer; transition: 0.2s;" onclick="cargarDesglose('creditos', 'Créditos Activos', '🎓')" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
        <div class="kpi-label" style="color: var(--c-yellow);">Créditos Pendientes</div>
        <div class="kpi-value" style="color: var(--c-yellow);">🎓 {{ number_format($total_deuda_global, 0) }}</div>
        <div style="font-size: 10px; color: var(--text-muted); margin-top: 5px;">🔍 Click para desglose</div>
    </div>
    
    <div class="kpi-card border-gray" style="cursor: pointer; transition: 0.2s;" onclick="cargarDesglose('velocidad', 'Tasa de Absorción Global', '📉')" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
        <div class="kpi-label">Tasa Absorción (Velocidad)</div>
        <div class="kpi-value" style="color: #7f8c8d;">📉 {{ number_format($tasa_absorcion, 2) }}%</div>
        <div style="font-size: 10px; color: var(--text-muted); margin-top: 5px;">🔍 Click para desglose</div>
    </div>

    <div class="kpi-card border-gray" style="cursor: pointer; transition: 0.2s;" onclick="cargarDesglose('baja', 'Máquinas de Baja', '🗑️')" onmouseover="this.style.transform='scale(1.02)'" onmouseout="this.style.transform='scale(1)'">
        <div class="kpi-label">Dados de Baja (Sin Respaldo)</div>
        <div class="kpi-value" style="color: #e74c3c;">🗑️ {{ number_format($dados_de_baja, 0) }}</div>
        <div style="font-size: 10px; color: var(--text-muted); margin-top: 5px;">🔍 Click para desglose</div>
    </div>

</div>
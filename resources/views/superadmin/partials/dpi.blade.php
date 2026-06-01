<h3 style="margin: 30px 0 15px 0; font-size: 13px; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1.5px;">📊 Monitoreo de Capacidad Productiva (DPI)</h3>
<div class="grid-kpis-auto" style="margin-bottom: 30px;">
    <div class="kpi-card" style="border-top: 3px solid #2ecc71; background: rgba(46, 204, 113, 0.03);">
        <div class="kpi-label">Capacidad de Red</div>
        <div class="kpi-value" style="color: #2ecc71;">{{ number_format($capacidad_horas, 0) }} <span style="font-size: 14px;">Hrs</span></div>
    </div>
    <div class="kpi-card" style="border-top: 3px solid #3498db; background: rgba(52, 152, 219, 0.03);">
        <div class="kpi-label">Nodos Conectados</div>
        <div class="kpi-value" style="color: #3498db;">{{ $total_labs_count }} <span style="font-size:14px;">Labs</span> / {{ $total_makers_count }} <span style="font-size:14px;">Makers</span></div>
    </div>
    <div class="kpi-card" style="border-top: 3px solid #9b59b6; background: rgba(155, 89, 182, 0.03);">
        <div class="kpi-label">Mix de Activos</div>
        <div style="margin-top: 10px; font-size: 12px; line-height: 1.6;">
            ⚙️ Equipos: <strong>{{ $mix['machine'] }}</strong><br>
            🧠 Servicios: <strong>{{ $mix['service'] }}</strong>
        </div>
    </div>
    <div class="kpi-card" style="border-top: 3px solid #e67e22; background: rgba(230, 126, 34, 0.03);">
        <div class="kpi-label">Capacidad Activada</div>
        <div style="margin-top: 10px; font-size: 12px; line-height: 1.6;">
            Equipos: <strong>{{ number_format($val_equipos, 0) }} FC</strong><br>
            Misiones: <strong>{{ number_format($val_talento, 0) }} FC</strong>
        </div>
    </div>
</div>
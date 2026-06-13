<div class="card">
    <h2>🏭 Mercado de Capacidad Industrial Instalada</h2>
    <div class="filters mb-15" style="display: flex; gap: 10px; flex-wrap: wrap;">
        <select id="filter-cat" style="padding: 10px; border-radius: 6px; background: #1a1a1a; color: white; border: 1px solid #34495e; flex: 1;">
            <option value="all">Todas las tecnologías</option>
            <option value="machine">⚙️ Máquinas y Hardware</option>
            <option value="service">🧠 Servicios Especializados</option>
        </select>
        <input type="text" id="filter-text" placeholder="Buscar nodo por máquina, tecnología o ubicación..." style="padding: 10px; border-radius: 6px; background: #1a1a1a; color: white; border: 1px solid #34495e; flex: 2; margin-bottom:0;">
    </div>

    <div class="mission-grid">
        @foreach($recursosMercado as $r)
            <div class="mission-card" data-category="{{ $r->asset_type }}" data-search="{{ strtolower($r->lab_name . ' ' . $r->custom_name) }}">
                <div class="mission-header">
                    <strong>🏭 {{ strtoupper($r->lab_name) }}</strong>
                    <span style="color: #2ecc71; font-weight: bold;">{{ number_format($r->set_price_fc, 2) }} FC/h</span>
                </div>
                <h3 style="margin: 5px 0; font-size: 15px;">{{ $r->custom_name }}</h3>
                <span class="badge badge-gray">{{ $r->display_name }}</span>
                
                <form action="{{ route('maker.book_asset') }}" method="POST" style="margin-top: 15px;">
                    @csrf <input type="hidden" name="asset_id" value="{{ $r->id }}">
                    <div style="display: flex; gap: 5px;">
                        <input type="date" name="reservation_date" required min="{{ date('Y-m-d') }}" style="padding:6px; margin:0; font-size:11px;">
                        <input type="number" name="hours" step="1" min="1" placeholder="Hrs" required style="padding:6px; margin:0; width:60px; font-size:11px;">
                        <button type="submit" class="btn-apply">Reservar</button>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div>
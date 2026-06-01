<div class="card">
    <h2>📋 Catálogo de Precios Referenciales (Mercado Global)</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Tipo</th>
                    <th>Nomenclatura Bilingüe / Unidad</th>
                    <th>Precio de Referencia (FC)</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach($catalogo as $cat)
                    <tr>
                        <td>
                            @php 
                                $color = '#e67e22'; 
                                if($cat->asset_type == 'service') $color = '#3498db';
                                elseif($cat->asset_type == 'workshop') $color = '#9b59b6';
                                elseif($cat->asset_type == 'space') $color = '#27ae60';
                            @endphp
                            <span class="badge font-11 font-bold" style="background: {{ $color }}; color:white;">{{ strtoupper($cat->asset_type) }}</span>
                        </td>
                        <td><strong>{{ $cat->generic_name }}</strong> <small class="text-muted">(ES)</small><br><span class="text-muted">{{ $cat->generic_name_en }} (EN)</span><br><span class="font-11 text-blue">cobro por {{ $cat->measurement_unit }}</span></td>
                        <td>
                            <input type="number" step="0.01" name="nuevo_precio" value="{{ $cat->suggested_price_fc }}" form="form_cat_{{ $cat->id }}" style="width: 80px; margin: 0; padding: 5px; background: #1a1a1a; color: white; border: 1px solid #34495e;">
                        </td>
                        <td>
                            <div style="display:flex; gap:5px;">
                                <form id="form_cat_{{ $cat->id }}" action="{{ route('superadmin.catalog.update') }}" method="POST">
                                    @csrf <input type="hidden" name="cat_id" value="{{ $cat->id }}">
                                    <button type="submit" style="background:#3498db; padding:5px 10px;">💾</button>
                                </form>
                                <form action="{{ route('superadmin.catalog.destroy') }}" method="POST">
                                    @csrf <input type="hidden" name="cat_id" value="{{ $cat->id }}">
                                    <button type="submit" style="background:#e74c3c; padding:5px 10px;">🗑️</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
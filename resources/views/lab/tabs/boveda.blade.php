<div class="card" style="border: 1px dashed var(--c-green); background: rgba(46, 204, 113, 0.02);">
    <h2>🪙 {{ __('messages.vault_title') }}</h2>
    <form action="{{ route('lab.tokenize') }}" method="POST">
        @csrf
        <div id="contenedor-filas">
            <div class="row-token">
                <div>
                    <select class="select-macro-tipo" onchange="filtrarCatalogoDependiente(this)" required>
                        <option value="">---</option>
                        <option value="machine">⚙️ MÁQUINAS</option>
                        <option value="service">🧠 SERVICIOS</option>
                    </select>
                    <input type="hidden" name="asset_type[]" class="input-tipo-oculto">
                </div>
                <div>
                    <select name="catalog_id[]" class="select-catalogo" onchange="configurarValoresActivo(this)" disabled required>
                        <option value="">---</option>
                        @foreach(DB::table('global_catalog')->get() as $item)
                            <option value="{{ $item->id }}" data-type="{{ $item->asset_type }}" data-price="{{ $item->suggested_price_fc }}" style="display: none;">{{ $item->generic_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><input type="text" name="custom_name[]" placeholder="Ej: Ender 3 Pro" required></div>
                <div><input type="number" name="quantity_offered[]" value="4160" class="input-cantidad" readonly></div>
                <div><input type="number" name="set_price_fc[]" step="0.1" class="input-precio" oninput="calcularFila(this)" required></div>
                <div class="fc-preview"><span class="preview-monto">0.00 FC</span></div>
                <div><button type="button" class="btn-retire" onclick="eliminarFilaToken(this)">✕</button></div>
            </div>
        </div>
        <button type="submit" class="btn-mint" style="margin-top:15px;">🚀 {{ __('messages.btn_mint_confirm') }}</button>
    </form>
</div>

<div class="card">
    <h2>🛠️ {{ __('messages.inv_title') }}</h2>
    <div class="table-container">
        <table>
            <tbody>
                @foreach($misActivos as $e)
                    <tr>
                        <td><span class="badge badge-blue">{{ $e->asset_type }}</span></td>
                        <td><strong>{{ $e->custom_name }}</strong></td>
                        <td>{{ number_format($e->useful_life_hours - $e->consumed_hours, 0) }} h restantes</td>
                        <td><strong>{{ number_format($e->set_price_fc, 2) }} FC</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
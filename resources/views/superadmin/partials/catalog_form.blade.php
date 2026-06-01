<div class="card">
    <h2>📖 Inyección Masiva de Tecnologías al Catálogo</h2>
    <form action="{{ route('superadmin.catalog.store') }}" method="POST">
        @csrf
        <div id="contenedor-filas-admin">
            <div class="row-catalogo" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">
                <select name="asset_type[]" required style="flex: 1; padding: 8px;">
                    <option value="machine">⚙️ Máquina / Equipo</option>
                    <option value="service">🧠 Servicio Humano</option>
                    <option value="workshop">🎓 Taller / Curso</option>
                    <option value="space">🏢 Espacio Físico</option>
                </select>
                <input type="text" name="generic_name_es[]" placeholder="Nombre (Español)" required style="flex: 2; margin:0;">
                <input type="text" name="generic_name_en[]" placeholder="Nombre (Inglés)" required style="flex: 2; margin:0;">
                <input type="text" name="measurement_unit[]" class="input-unidad-admin" value="hora" required style="flex: 1; margin:0;">
                <input type="number" step="0.01" name="suggested_price_fc[]" placeholder="Precio FC" required style="flex: 1; margin:0;">
                <div class="espaciador-borrar" style="width: 20px;"></div>
            </div>
        </div>
        <div style="display: flex; gap: 10px; margin-top: 15px;">
            <button type="button" onclick="agregarFilaAdmin()" style="background: #34495e; width:auto;">+ Añadir Fila</button>
            <button type="submit" style="background: #f1c40f; color: #1a1a1a; flex-grow: 1; font-weight: bold;">💾 Confirmar Inyección al Catálogo</button>
        </div>
    </form>
</div>
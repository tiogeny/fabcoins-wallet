<div class="card" style="border-left: 4px solid #2ecc71;">
    <h2>➕ Invitar Nuevo Fab Lab</h2>
    <form action="{{ route('superadmin.lab.invite') }}" method="POST">
        @csrf
        <input type="text" name="name" placeholder="Nombre Institucional" required>
        <input type="email" name="email" placeholder="Correo Electrónico" required>
        <input type="password" name="password" placeholder="Contraseña Temporal" required>
        <select name="lab_lang" required style="width: 100%; padding: 10px; background: #1a1a1a; color: white; border: 1px solid #34495e; border-radius: 6px;">
            <option value="es">Español</option>
            <option value="en">English</option>
        </select>
        <button type="submit" style="width: 100%; margin-top: 15px;">Enviar Invitación Oficial</button>
    </form>
</div>

<div class="card" style="border-left: 4px solid #3498db;">
    <h2>⚙️ Política Monetaria</h2>
    <form action="{{ route('superadmin.policy.update') }}" method="POST">
        @csrf
        <label class="font-11 text-muted font-bold d-block mb-5">% RESPALDO GLOBAL (MÁQUINAS)</label>
        <input type="number" step="0.1" name="nuevo_pct" value="{{ $global_pct }}" required>
        <button type="submit" style="width: 100%; margin-top: 10px;">Actualizar Ratio de Emisión</button>
    </form>
</div>

<div class="card" style="border-top: 4px solid var(--c-yellow);">
    <h2>🎓 Auditoría de Créditos (ISA)</h2>
    <div style="max-height: 230px; overflow-y: auto;">
        <table style="width: 100%; font-size: 12px;">
            @forelse($makers_financiados as $mf)
                @php $p = round((($mf->amount_initial - $mf->amount_remaining) / $mf->amount_initial) * 100); @endphp
                <tr style="border-bottom: 1px solid #1a1a1a;">
                    <td style="padding: 5px 0;"><strong>👤 {{ $mf->maker_name }}</strong><br><span class="text-muted font-11">Acreedor: {{ $mf->lab_name }}</span></td>
                    <td style="text-align: right;"><strong>{{ number_format($mf->amount_remaining, 0) }} FC</strong><br><span class="font-10 text-muted">{{ $p }}% amortizado</span></td>
                </tr>
            @empty
                <tr><td class="text-muted text-center" style="padding:20px;">No registran financiamientos de honor activos.</td></tr>
            @endforelse
        </table>
    </div>
</div>
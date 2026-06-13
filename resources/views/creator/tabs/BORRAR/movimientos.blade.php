<div class="card">
    <div class="flex-between mb-15">
        <h2>💳 Libro Mayor Contable (Historial)</h2>
        <div class="text-orange font-bold font-18">{{ number_format($saldoTotal, 2) }} FC</div>
    </div>
    <div class="table-container">
        <table>
            <tbody>
                @forelse($misTransacciones as $tx)
                    <tr>
                        <td class="font-11 text-muted">{{ date('d M Y - H:i', strtotime($tx->created_at)) }}</td>
                        <td>{{ $tx->description }}</td>
                        <td style="text-align: right; font-weight: bold; color: {{ $tx->type == 'income' ? 'var(--c-green)' : 'var(--c-red)' }};">
                            {{ $tx->type == 'income' ? '+' : '-' }}{{ number_format($tx->amount, 2) }} FC
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-muted" style="text-align:center;">No registras movimientos en tu balance.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
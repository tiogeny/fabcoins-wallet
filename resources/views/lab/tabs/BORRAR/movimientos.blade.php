<div class="card">
    <div class="flex-between">
        <h2>💳 {{ __('messages.title_transactions') }}</h2>
        <a href="{{ route('lab.export_csv') }}" class="btn-apply" style="background:#27ae60; color:white; width:auto;">📊 Descargar CSV</a>
    </div>
    <table>
        <tbody>
            @foreach($misTransacciones as $tx)
                <tr>
                    <td>{{ date('d M - H:i', strtotime($tx->created_at)) }}</td>
                    <td>{{ $tx->description }}</td>
                    <td style="text-align:right; font-weight:bold;">{{ number_format($tx->amount, 2) }} FC</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
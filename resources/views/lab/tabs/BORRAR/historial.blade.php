<div class="card">
    <h2>📜 {{ __('messages.hist_title') }}</h2>
    <div class="table-container">
        <table>
            <tbody>
                @foreach($misReservas as $res)
                    <tr>
                        <td>{{ date('d M Y', strtotime($res->created_at)) }}</td>
                        <td><strong>👤 {{ $res->creador_name }}</strong></td>
                        <td>{{ $res->custom_name }}</td>
                        <td>{{ $res->hours_requested }} hrs</td>
                        <td class="text-yellow font-bold">{{ number_format($res->total_fc, 2) }} FC</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
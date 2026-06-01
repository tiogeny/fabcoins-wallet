<div class="grid-tablas">
    <div class="card">
        <h2>🏆 Top 5 Nodos Fab Labs</h2>
        <table>
            @foreach($top_labs as $l)
                <tr>
                    <td><strong>{{ $l->name }}</strong></td>
                    <td class="estrellas" style="text-align: right; color:#f1c40f;">⭐ {{ number_format($l->reputation_score, 1) }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="card">
        <h2>👷 Top 5 Co-Inventores Makers</h2>
        <table>
            @foreach($top_makers as $f)
                <tr>
                    <td><strong>{{ $f->name }}</strong></td>
                    <td class="estrellas" style="text-align: right; color:#f1c40f;">⭐ {{ number_format($f->reputation_score, 1) }}</td>
                </tr>
            @endforeach
        </table>
    </div>

    <div class="card">
        <h2>💸 Últimas Transacciones</h2>
        <table>
            @foreach($ultimas_tx as $tx)
                <tr>
                    <td><strong style="color: #3498db;">{{ number_format($tx->amount, 0) }} FC</strong><br><span class="font-11 text-muted">{{ $tx->user_name }}</span></td>
                    <td class="font-12" style="vertical-align: middle;">{{ $tx->description }}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>

<div class="card" style="border-left: 4px solid #9b59b6; margin-top: 25px;">
    <h2>📡 Radar de Misiones Globales Activas</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr style="background: #1a1a1a; color: #bdc3c7; font-size: 11px;">
                    <th>Laboratorio Emisor</th>
                    <th>Misión / Hito Técnico</th>
                    <th>Recompensa</th>
                    <th>Límite</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($radar_misiones as $rm)
                    <tr>
                        <td><strong>{{ $rm->lab_name }}</strong></td>
                        <td><strong>{{ $rm->title }}</strong></td>
                        <td class="text-yellow font-bold">{{ number_format($rm->reward_fc, 2) }} FC</td>
                        <td class="text-orange font-12">📅 {{ date('d M Y', strtotime($rm->deadline)) }}</td>
                        <td>
                            @php 
                                $bc = '#7f8c8d';
                                if($rm->status == 'open') $bc = '#3498db';
                                elseif($rm->status == 'assigned') $bc = '#f39c12';
                                elseif($rm->status == 'completed') $bc = '#2ecc71';
                            @endphp
                            <span class="badge font-11 font-bold" style="background: {{ $bc }}; color:white; text-transform:uppercase;">{{ $rm->status }}</span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
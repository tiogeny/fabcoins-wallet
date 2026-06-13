<div class="card">
    <h2>📋 Mis Postulaciones y Proyectos Activos</h2>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Laboratorio / Célula</th>
                    <th>Misión / Hito</th>
                    <th>Recompensa</th>
                    <th>Límite de Entrega</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($misPostulaciones as $p)
                    <tr>
                        <td>{{ date('d M', strtotime($p->applied_at)) }}</td>
                        <td><strong>{{ $p->lab_name }}</strong></td>
                        <td>{{ $p->title }}</td>
                        <td class="text-yellow font-bold">{{ number_format($p->reward_fc, 0) }} FC</td>
                        <td class="font-bold text-orange">📅 {{ date('d M Y', strtotime($p->deadline)) }}</td>
                        <td><span class="badge {{ $p->status == 'accepted' ? 'badge-green' : 'badge-blue' }}">{{ $p->status }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted" style="text-align:center;">Aún no tienes bitácoras ni postulaciones registradas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
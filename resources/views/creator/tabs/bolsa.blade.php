<div class="card">
    <h2>🎯 {{ __('messages.title_open_missions') ?? 'Misiones Abiertas en la Red' }}</h2>
    <div class="mission-grid">
        @forelse($misionesAbiertas as $m)
            <div class="mission-card">
                <div>
                    <div class="mission-header">
                        <span style="font-size: 11px; color: #3498db; font-weight: bold; text-transform: uppercase;">AULA: {{ $m->lab_name }}</span>
                        <span style="color: #f1c40f; font-weight: bold; font-size: 18px;">{{ number_format($m->reward_fc, 0) }} FC</span>
                    </div>
                    @if($m->target_maker_id == auth()->id())
                        <span style="background: #f1c40f; color: #1a1a1a; font-size: 10px; padding: 2px 8px; border-radius: 10px; font-weight: bold; margin-bottom: 5px; display: inline-block;">
                            🎯 MISIÓN DIRIGIDA (PARA TU CRÉDITO)
                        </span>
                    @endif
                    <h3 style="margin: 0 0 10px 0; font-size: 16px;">{{ $m->title }}</h3>
                    <span style="font-size: 11px; font-weight: bold; color: #3498db; display: block; margin-top: 5px;">👥 {{ $m->spots_filled }}/{{ $m->spots_total }} Cupos</span>
                    <p style="font-size: 13px; color: #bdc3c7; margin-bottom: 10px;">{{ $m->description }}</p>
                </div>
                <form action="{{ route('maker.apply_mission') }}" method="POST">
                    @csrf <input type="hidden" name="mission_id" value="{{ $m->id }}">
                    <textarea name="message" rows="2" placeholder="¿Por qué eres el co-inventor ideal para resolver este reto?" required></textarea>
                    <button type="submit" class="btn-apply" style="background:#3498db;">Enviar Postulación</button>
                </form>
            </div>
        @empty
            <p class="text-muted" style="text-align:center; padding:20px; width:100%;">No hay retos de co-creación abiertos en este momento.</p>
        @endforelse
    </div>
</div>
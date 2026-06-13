<div class="card">
    <h2>🧠 {{ __('messages.title_talent') }}</h2>
    <input type="text" id="search-talent" placeholder="🔍 {{ __('messages.ph_search_talent') }}" onkeyup="filtrarTalentosLive()">
    <div class="grid-kpis" style="grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); margin-top:20px;">
        @foreach($creadorsExplorador as $mk)
            <div class="creador-card" style="background:var(--bg-main); border:1px solid #34495e; padding:15px; border-radius:8px;">
                <strong>{{ $mk->name }}</strong>
                <div class="text-yellow">⭐ {{ number_format($mk->reputation_score, 1) }}</div>
                <div class="font-11 text-muted">📍 {{ $mk->address ?: 'Global' }}</div>
            </div>
        @endforeach
    </div>
</div>
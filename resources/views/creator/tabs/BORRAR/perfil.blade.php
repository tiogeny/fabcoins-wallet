<form action="{{ route('maker.update_profile') }}" method="POST">
    @csrf
    <div class="card">
        <h2>👤 Biografía, Enlaces y Rol de Co-Creación</h2>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div>
                <label class="font-11 text-muted text-uppercase font-bold mb-10 d-inline-block">Presentación Pública</label>
                <textarea id="bio-editor" name="bio" rows="10">{{ $maker->bio }}</textarea>
            </div>
            <div>
                <label class="font-11 text-muted text-uppercase font-bold mb-5 d-inline-block">Ciudad y Sede</label>
                <input type="text" name="address" value="{{ $maker->address }}" required class="mb-15">
                
                <label class="font-11 text-muted text-uppercase font-bold mb-5 d-inline-block">Enlaces Profesionales (Portafolios)</label>
                <input type="url" name="social_fabacademy" value="{{ $maker->social_fabacademy }}" placeholder="Link Fab Academy" class="mb-10" style="font-size:11px;">
                <input type="url" name="social_linkedin" value="{{ $maker->social_linkedin }}" placeholder="URL LinkedIn" class="mb-10" style="font-size:11px;">
                <input type="url" name="social_github" value="{{ $maker->social_github }}" placeholder="URL GitHub" class="mb-10" style="font-size:11px;">
            </div>
        </div>
    </div>

    <div class="card">
        <div class="flex-between mb-15">
            <h2>🛠️ Especialidades y Roles Técnicos Destacados</h2>
            <button type="submit" class="btn-apply" style="width: auto; padding: 10px 30px;">💾 Guardar Perfil Completo</button>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <div style="background: var(--bg-main); padding: 15px; border-radius: 8px; border-top: 3px solid var(--c-blue);">
                <div class="flex-between mb-10"><span class="font-12 font-bold text-blue">HABILIDADES TÉCNICAS (MÁX. 6)</span><span id="hard-counter" class="font-12 text-muted">0 / 6</span></div>
                <div class="skill-chip-container">
                    @foreach($catalogoSkills->where('type', 'hard') as $sk)
                        <label><input type="checkbox" name="skills[]" value="{{ $sk->id }}" class="skill-chip skill-chip-hard" onchange="checkLimit('hard', 6)" {{ in_array($sk->id, $misSkillsIds) ? 'checked' : '' }}><div class="skill-chip-label">{{ $sk->name }}</div></label>
                    @endforeach
                </div>
            </div>
            <div style="background: var(--bg-main); padding: 15px; border-radius: 8px; border-top: 3px solid var(--c-orange);">
                <div class="flex-between mb-10"><span class="font-12 font-bold text-orange">COMPETENCIAS TRANSVERSALES (MÁX. 4)</span><span id="soft-counter" class="font-12 text-muted">0 / 4</span></div>
                <div class="skill-chip-container">
                    @foreach($catalogoSkills->where('type', 'soft') as $sk)
                        <label><input type="checkbox" name="skills[]" value="{{ $sk->id }}" class="skill-chip skill-chip-soft" onchange="checkLimit('soft', 4)" {{ in_array($sk->id, $misSkillsIds) ? 'checked' : '' }}><div class="skill-chip-label">{{ $sk->name }}</div></label>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</form>
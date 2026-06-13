<form action="{{ route('lab.update_profile') }}" method="POST">
    @csrf
    <div class="card">
        <h2>👤 {{ __('messages.title_bio_links') }}</h2>
        <textarea name="bio" rows="6">{{ $lab->bio }}</textarea>
        <input type="text" name="address" id="address-input" value="{{ $lab->address }}">
        <input type="hidden" name="latitude" id="lat-input" value="{{ $lab->latitude }}">
        <input type="hidden" name="longitude" id="lng-input" value="{{ $lab->longitude }}">
        <button type="submit" class="btn-apply" style="margin-top:10px;">💾 Guardar Perfil</button>
    </div>
</form>
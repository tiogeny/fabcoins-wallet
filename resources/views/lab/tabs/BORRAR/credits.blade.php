<div class="card" style="border: 1px solid var(--c-yellow);">
    <h2>🎓 {{ __('messages.isa_title') }}</h2>
    <form action="{{ route('lab.propose_credit') }}" method="POST">
        @csrf
        <input type="email" name="email_creador" placeholder="Correo Creador" required>
        <input type="number" name="monto_fc" placeholder="Monto" required>
        <input type="text" name="motivo" placeholder="Motivo" required>
        <button type="submit" class="btn-apply" style="background:var(--c-yellow); color:#1a1a1a;">{{ __('messages.btn_send_proposal') }}</button>
    </form>
</div>
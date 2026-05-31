<div class="card" style="border-left: 4px solid #3498db;">
    <h2>📝 {{ __('messages.miss_create_title') }}</h2>
    <form action="{{ route('lab.create_mission') }}" method="POST">
        @csrf
        <input type="text" name="title" placeholder="{{ __('messages.ph_miss_title') }}" required>
        <textarea name="description" rows="3" placeholder="{{ __('messages.ph_miss_desc') }}" required></textarea>
        <input type="date" name="deadline" required>
        <input type="number" step="0.01" name="reward_fc" placeholder="Pago FC" required>
        <button type="submit" class="btn-mint" style="background:#3498db; margin-top:10px;">{{ __('messages.btn_publish_miss') }}</button>
    </form>
</div>
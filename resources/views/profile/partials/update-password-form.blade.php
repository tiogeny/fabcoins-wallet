<section>
    <header>
        <h2 class="text-lg font-medium" style="color: #ffffff; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600" style="font-size: 12.5px; color: #a0aec0; margin-bottom: 20px; line-height: 1.4;">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('creator.change_password') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div style="margin-bottom: 15px;">
            <x-input-label for="update_password_current_password" :value="__('Current Password')" class="premium-label" style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="premium-input m-0" style="width: 100%; background: #1c2230; border: 1px solid rgba(255, 255, 255, 0.06); color: #ffffff; height: 38px; border-radius: 6px; font-size: 12.5px; padding: 0 15px; box-sizing: border-box;" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" style="color: #e74c3c; font-size: 11px; margin-top: 5px;" />
        </div>

        <div style="margin-bottom: 15px;">
            <x-input-label for="update_password_password" :value="__('New Password')" class="premium-label" style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;" />
            <x-text-input id="update_password_password" name="password" type="password" class="premium-input m-0" style="width: 100%; background: #1c2230; border: 1px solid rgba(255, 255, 255, 0.06); color: #ffffff; height: 38px; border-radius: 6px; font-size: 12.5px; padding: 0 15px; box-sizing: border-box;" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" style="color: #e74c3c; font-size: 11px; margin-top: 5px;" />
        </div>

        <div style="margin-bottom: 20px;">
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" class="premium-label" style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="premium-input m-0" style="width: 100%; background: #1c2230; border: 1px solid rgba(255, 255, 255, 0.06); color: #ffffff; height: 38px; border-radius: 6px; font-size: 12.5px; padding: 0 15px; box-sizing: border-box;" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" style="color: #e74c3c; font-size: 11px; margin-top: 5px;" />
        </div>

        <div class="flex items-center gap-4" style="display: flex; align-items: center; gap: 15px;">
            <button type="submit" class="btn-premium btn-amarillo-hub m-0" style="background: #f1c40f; border: 1px solid #f1c40f; color: #ffffff !important; padding: 0 24px; height: 36px; font-size: 11.5px; font-weight: 700; border-radius: 6px; cursor: pointer;">{{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                    style="color: #2ecc71; font-size: 12px; font-weight: 600; margin: 0;"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
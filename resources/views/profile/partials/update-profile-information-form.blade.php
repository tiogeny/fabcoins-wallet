<section>
    <header>
        <h2 class="text-lg font-medium" style="color: #ffffff; font-family: 'Rajdhani', sans-serif; font-weight: 700; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-400" style="font-size: 12.5px; color: #a0aec0; margin-bottom: 20px; line-height: 1.4;">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('creator.update_profile') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div style="margin-bottom: 15px;">
            <x-input-label for="name" :value="__('Name')" class="premium-label" style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;" />
            <x-text-input id="name" name="name" type="text" class="premium-input m-0" style="width: 100%; background: #1c2230; border: 1px solid rgba(255, 255, 255, 0.06); color: #ffffff; height: 38px; border-radius: 6px; font-size: 12.5px; padding: 0 15px; box-sizing: border-box;" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" style="color: #e74c3c; font-size: 11px; margin-top: 5px;" />
        </div>

        <div style="margin-bottom: 15px;">
            <x-input-label for="email" :value="__('Email')" class="premium-label" style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;" />
            <x-text-input id="email" name="email" type="email" class="premium-input m-0" style="width: 100%; background: #1c2230; border: 1px solid rgba(255, 255, 255, 0.06); color: #ffffff; height: 38px; border-radius: 6px; font-size: 12.5px; padding: 0 15px; box-sizing: border-box;" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" style="color: #e74c3c; font-size: 11px; margin-top: 5px;" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800" style="color: #a0aec0; font-size: 12px; margin-top: 10px;">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" style="background: none; border: none; color: #3498db; cursor: pointer; padding: 0; font-weight: 600; text-decoration: underline;">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600" style="color: #2ecc71; font-size: 12px; margin-top: 5px;">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div style="margin-bottom: 15px;">
            <x-input-label for="fab_academy_url" value="Link de Fab Academy" class="premium-label" style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;" />
            <x-text-input id="fab_academy_url" name="fab_academy_url" type="url" class="premium-input m-0" style="width: 100%; background: #1c2230; border: 1px solid rgba(255, 255, 255, 0.06); color: #ffffff; height: 38px; border-radius: 6px; font-size: 12.5px; padding: 0 15px; box-sizing: border-box;" :value="old('fab_academy_url', $user->fab_academy_url)" placeholder="https://fabacademy.org/..." />
        </div>

        <div style="margin-bottom: 15px;">
            <x-input-label for="instagram_url" value="Instagram" class="premium-label" style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;" />
            <x-text-input id="instagram_url" name="instagram_url" type="url" class="premium-input m-0" style="width: 100%; background: #1c2230; border: 1px solid rgba(255, 255, 255, 0.06); color: #ffffff; height: 38px; border-radius: 6px; font-size: 12.5px; padding: 0 15px; box-sizing: border-box;" :value="old('instagram_url', $user->instagram_url)" placeholder="https://instagram.com/..." />
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 20px;">
            <div>
                <x-input-label for="city" value="Ciudad" class="premium-label" style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;" />
                <x-text-input id="city" name="city" type="text" class="premium-input m-0" style="width: 100%; background: #1c2230; border: 1px solid rgba(255, 255, 255, 0.06); color: #ffffff; height: 38px; border-radius: 6px; font-size: 12.5px; padding: 0 15px; box-sizing: border-box;" :value="old('city', $user->city)" placeholder="Ej. Lima" />
            </div>
            <div>
                <x-input-label for="country" value="País" class="premium-label" style="font-size: 10px; font-weight: 700; color: #7f8c8d; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 6px;" />
                <x-text-input id="country" name="country" type="text" class="premium-input m-0" style="width: 100%; background: #1c2230; border: 1px solid rgba(255, 255, 255, 0.06); color: #ffffff; height: 38px; border-radius: 6px; font-size: 12.5px; padding: 0 15px; box-sizing: border-box;" :value="old('country', $user->country)" placeholder="Ej. Perú" />
            </div>
        </div>

        <div class="flex items-center gap-4" style="display: flex; align-items: center; gap: 15px;">
            <button type="submit" class="btn-premium btn-amarillo-hub m-0" style="background: #f1c40f; border: 1px solid #f1c40f; color: #ffffff !important; padding: 0 24px; height: 36px; font-size: 11.5px; font-weight: 700; border-radius: 6px; cursor: pointer;">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
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
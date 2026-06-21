@extends('layouts.app')

@section('title', __('messages.tab_login') . ' | FabCoins')

<link rel="stylesheet" href="{{ asset('css/style.css') }}?v=2.0">

@section('content')
<div class="auth-card-v2">
    
    <!-- ENCABEZADO CON MARCA UNIFICADA -->
    <div class="auth-logo-area">
        <img src="{{ asset('images/logo-icon.webp') }}" alt="FabCoins Isotype">
        <h1>Fab<span>Coins</span></h1>
    </div>
    <p class="auth-subtitle">{{ __('messages.app_subtitle') }}</p>

    {{-- MANEJO DE ALERTAS NATIVAS DE LARAVEL --}}
    @if($errors->any())
        <div class="alert alert-danger" style="padding: 12px; font-size: 13px; margin-bottom: 15px;">
            {{ $errors->first() }}
        </div>
    @endif

    <!-- =======================================================================
         🛡️ FLUJO A: REGISTRO DE SEGURIDAD (ONBOARDING FORZADO)
         ======================================================================= -->
    @if(isset($require_onboarding) && $require_onboarding)
        <div style="text-align: center; margin-bottom: 20px;">
            <h2 style="color: var(--c-yellow); font-family: 'Rajdhani', sans-serif; font-size: 22px; margin: 0 0 8px 0; text-transform: uppercase;">
                {{ __('messages.onb_title') }}
            </h2>
            <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">
                {{ __('messages.onb_desc') }}
            </p>
        </div>
        
        <form method="POST" action="{{ route('onboarding.complete') }}">
            @csrf
            <input type="hidden" name="temp_user_id" value="{{ $temp_user_id }}">
            
            <div class="auth-input-group">
                <input type="password" name="new_password" placeholder="{{ __('messages.onb_ph_pass') }}" required>
            </div>
            
            <div class="auth-input-group">
                <input type="text" name="address" placeholder="{{ __('messages.onb_ph_loc') }}" required>
            </div>
            
            <div class="auth-input-group">
                <textarea name="bio" rows="3" placeholder="{{ __('messages.onb_ph_bio') }}" required></textarea>
            </div>
            
            <button type="submit" class="btn-auth-submit">{{ __('messages.onb_btn') }}</button>
        </form>

    @else
        <!-- =======================================================================
             🎛️ FLUJO B: ACCESO TRADICIONAL (TABS INTERACTIVOS LOGIN / REGISTRO)
             ======================================================================= -->
        <div class="auth-tabs-v2">
            <div class="auth-tab-btn active" onclick="switchAuthTab('login', this)">{{ __('messages.tab_login') }}</div>
            <div class="auth-tab-btn" onclick="switchAuthTab('register', this)">{{ __('messages.tab_register') }}</div>
        </div>

        {{-- FORMULARIO DE INGRESO (LOGIN) --}}
        <form id="login-form-block" class="auth-form-section active" method="POST" action="{{ route('login') }}">
            @csrf
            <div class="auth-input-group">
                <input type="email" name="email" placeholder="{{ __('messages.ph_email') }}" required autofocus>
            </div>
            
            <div class="auth-input-group password-toggle-wrapper">
                <input type="password" name="password" placeholder="{{ __('messages.ph_password') }}" required>
                <span class="password-toggle-eye" onclick="toggleAuthPassword(this)">
                    <!-- Icono Ojo Abierto SVG Moderno -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-open-icon">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </span>
            </div>
            
            <button type="submit" class="btn-auth-submit btn-login-theme">{{ __('messages.btn_login') }}</button>
            
            <div style="text-align: center; margin-top: 18px;">
                <a href="{{ route('password.request') }}" style="color: var(--c-blue); font-size: 13px; font-weight: 600; text-decoration: none;">
                    {{ __('messages.link_forgot') }}
                </a>
            </div>
        </form>

        {{-- FORMULARIO DE REGISTRO (CREATORS) --}}
        <form id="register-form-block" class="auth-form-section" method="POST" action="{{ route('register') }}">
            @csrf
            <p style="font-size: 13px; color: var(--text-muted); text-align: center; margin: 0 0 15px 0;">
                {{ __('messages.reg_desc') }}
            </p>

            <div class="auth-input-group">
                <input type="text" name="name" placeholder="{{ __('messages.ph_name') }}" required>
            </div>
            
            <div class="auth-input-group">
                <input type="email" name="email" placeholder="{{ __('messages.ph_email') }}" required>
            </div>
            
            <div class="auth-input-group password-toggle-wrapper">
                <input type="password" name="password" placeholder="{{ __('messages.ph_password') }}" required>
                <span class="password-toggle-eye" onclick="toggleAuthPassword(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-open-icon">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </span>
            </div>

            <div class="auth-input-group password-toggle-wrapper">
                <input type="password" name="password_confirmation" placeholder="{{ __('messages.ph_password_confirm') }}" required>
                <span class="password-toggle-eye" onclick="toggleAuthPassword(this)">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-open-icon">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                    </svg>
                </span>
            </div>
            
            <button type="submit" class="btn-auth-submit">{{ __('messages.btn_register') }}</button>
            
            <p style="font-size: 11px; color: var(--c-gray); text-align: center; margin-top: 15px; line-height: 1.3;">
                {{ __('messages.reg_note') }}
            </p>
        </form>

    @endif
</div>

<a href="{{ url('/') }}" class="auth-back-link">{{ __('messages.link_home') }}</a>

<script>
/**
 * 🎛️ CONTROLADOR INTERACTIVO DE PESTAÑAS (LOGIN / REGISTER)
 */
function switchAuthTab(targetForm, element) {
    document.querySelectorAll('.auth-tab-btn').forEach(btn => btn.classList.remove('active'));
    document.querySelectorAll('.auth-form-section').forEach(form => form.classList.remove('active'));
    
    element.classList.add('active');
    
    if (targetForm === 'login') {
        document.getElementById('login-form-block').classList.add('active');
    } else {
        document.getElementById('register-form-block').classList.add('active');
    }
}

/**
 * 👁️ DETONADOR VECTORIAL PARA MOSTRAR/OCULTAR CONTRASEÑAS (SVG VER.)
 */
function toggleAuthPassword(iconElement) {
    const inputField = iconElement.previousElementSibling;
    
    if (inputField.type === "password") {
        inputField.type = "text";
        // Cambia el diseño al icono de Ojo Cerrado con una diagonal cruzada
        iconElement.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
        `;
    } else {
        inputField.type = "password";
        // Restaura el Ojo Abierto original
        iconElement.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        `;
    }
}

document.addEventListener("DOMContentLoaded", function() {
        // Buscamos las pestañas nativas del archivo (el segundo elemento suele ser Register)
        const tabs = document.querySelectorAll('.auth-tab-btn');
        
        // 1. 🚀 CASO A: Si Laravel devuelve errores de registro o datos viejos (old)
        // Forzamos que se mantenga abierta la pestaña de Registro para que el usuario vea qué falló.
        @if($errors->has('name') || $errors->has('email') || old('name'))
            if (tabs.length > 1) {
                switchAuthTab('register', tabs[1]);
            }
        @else
            // 2. 🌐 CASO B: Si no hay errores, leemos si la URL viene desde la Landing con ?tab=register
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');

            if (tabParam === 'register' && tabs.length > 1) {
                switchAuthTab('register', tabs[1]);
            }
        @endif
    });
</script>
@endsection

@section('body-class', 'auth-page-body')
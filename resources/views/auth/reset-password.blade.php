@extends('layouts.app')

@section('title', 'Restablecer Contraseña | FabCoins')

<link rel="stylesheet" href="{{ asset('css/style.css') }}?v=2.0">

@section('content')
<div class="auth-card-v2">
    
    <!-- ENCABEZADO CON MARCA UNIFICADA -->
    <div class="auth-logo-area">
        <img src="{{ asset('images/logo-icon.webp') }}" alt="FabCoins Isotype">
        <h1>Fab<span>Coins</span></h1>
    </div>
    <p class="auth-subtitle">{{ __('messages.app_subtitle') }}</p>

    <div style="text-align: center; margin-bottom: 20px;">
        <h2 style="color: var(--c-yellow); font-family: 'Rajdhani', sans-serif; font-size: 22px; margin: 0 0 8px 0; text-transform: uppercase;">
            {{ __('messages.reset_title') }}
        </h2>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.4;">
            {{ __('messages.reset_descr') }}
        </p>
    </div>

    {{-- MANEJO DE ALERTAS NATIVAS DE LARAVEL --}}
    @if($errors->any())
        <div class="alert alert-danger" style="padding: 12px; font-size: 13px; margin-bottom: 15px; border-radius: 6px;">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- FORMULARIO SEGURO CONECTADO AL PROCESADOR DE CONTRASEÑAS --}}
    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <!-- Token oculto de validación criptográfica -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Correo Electrónico -->
        <div class="auth-input-group">
            <input type="email" name="email" value="{{ old('email', $request->email) }}" placeholder="{{ __('messages.ph_email') }}" required autofocus autocomplete="username">
        </div>

        <!-- Nueva Contraseña -->
        <div class="auth-input-group password-toggle-wrapper">
            <input type="password" id="password" name="password" placeholder="{{ __('messages.reset_title') }}" required autocomplete="new-password">
            <span class="password-toggle-eye" onclick="toggleAuthPassword('password')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-open-icon">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </span>
        </div>

        <!-- Confirmación de Contraseña -->
        <div class="auth-input-group password-toggle-wrapper">
            <input type="password" id="password_confirmation" name="password_confirmation" placeholder="{{ __('messages.reset_confirm') }}" required autocomplete="new-password">
            <span class="password-toggle-eye" onclick="toggleAuthPassword('password_confirmation')">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="eye-open-icon">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
            </span>
        </div>
        
        <button type="submit" class="btn-auth-submit" style="background-color: var(--c-yellow); color: #0b0c10;">
            {{ __('messages.btn_reset_up') }}
        </button>
    </form>
</div>

<a href="{{ route('login') }}" class="auth-back-link">⬅ {{ __('messages.tab_login') }}</a>

<script>
/**
 * 👁️ CONTROLADOR DINÁMICO DE VISIBILIDAD DE CONTRASEÑA
 */
function toggleAuthPassword(fieldId) {
    const inputField = document.getElementById(fieldId);
    const iconElement = inputField.nextElementSibling;
    
    if (inputField.type === "password") {
        inputField.type = "text";
        iconElement.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                <line x1="1" y1="1" x2="23" y2="23"></line>
            </svg>
        `;
    } else {
        inputField.type = "password";
        iconElement.innerHTML = `
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
            </svg>
        `;
    }
}
</script>
@endsection

@section('body-class', 'auth-page-body')
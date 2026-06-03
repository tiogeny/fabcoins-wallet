@extends('layouts.app')

@section('title', __('messages.link_forgot') . ' | FabCoins')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}?v=1.0">
@endpush

@section('content')
<div class="auth-card-v2">
    
    <div class="auth-logo-area">
        <img src="{{ asset('images/logo-icon.webp') }}" alt="FabCoins Isotype">
        <div class="logo-word">Fab<span>Coins</span></div>
    </div>
    <p class="auth-subtitle">{{ __('messages.app_subtitle') }}</p>

    <div style="text-align: center; margin-bottom: 25px;">
        <h2 style="color: var(--c-blue); font-family: 'Rajdhani', sans-serif; font-size: 22px; margin: 0 0 10px 0; text-transform: uppercase;">
            {{ __('messages.h1_forgot') }}
        </h2>
        <p style="font-size: 13px; color: var(--text-muted); line-height: 1.5; padding: 0 10px; text-transform: none !important;">
            {{ __('messages.desc_forgot') }}
        </p>
    </div>

    {{-- ALERTAS DE ÉXITO DE ENVÍO O ERRORES DE VALIDACIÓN --}}
    @if (session('status'))
        <div class="alert alert-success" style="padding: 12px; font-size: 13px; margin-bottom: 20px; text-align: left;">
            {{ session('status') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger" style="padding: 12px; font-size: 13px; margin-bottom: 20px; text-align: left;">
            {{ $errors->first() }}
        </div>
    @endif

    {{-- FORMULARIO SEGURO CONECTADO AL ENDPOINT DE BREEZE --}}
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        
        <div class="auth-input-group">
            <input type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('messages.ph_email') }}" required autofocus>
        </div>
        
        <button type="submit" class="btn-auth-submit btn-login-theme">
            {{ __('messages.btn_send_link') }}
        </button>
    </form>

    <div style="text-align: center; margin-top: 25px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 15px;">
        <a href="{{ route('login') }}" style="color: var(--text-muted); font-size: 13px; text-decoration: none; font-weight: 600; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='var(--text-muted)'">
            ⬅ {{ __('messages.tab_login') }}
        </a>
    </div>

</div>

<a href="{{ url('/') }}" class="auth-back-link">{{ __('messages.link_home') }}</a>
@endsection

@section('body-class', 'auth-page-body')
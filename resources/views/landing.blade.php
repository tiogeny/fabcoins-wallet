@extends('layouts.app')

@section('title', __('messages.landing_title'))

{{-- Inyectamos la hoja de estilos aislada del Landing --}}
@push('styles')
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}?v=1.1">
@endpush

@section('content')
<div class="container">
    
    <!-- NAVEGACIÓN -->
    <nav class="landing-nav">
        <div class="logo">
            <img src="{{ asset('images/logo-icon.webp') }}" alt="FabCoins Isotype">
            <div class="logo-word">Fab<span>Coins</span></div>
        </div>
        <div class="nav-links">
            <a href="#como-funciona">{{ __('messages.nav_how_it_works') }}</a>
            <a href="#beneficios">{{ __('messages.nav_benefits') }}</a>
            
            <a href="?lang=es" style="margin-left: 30px; text-decoration: none; font-size: 18px; opacity: {{ app()->getLocale() == 'es' ? '1' : '0.4' }};">🇪🇸</a>
            <a href="?lang=en" style="margin-left: 15px; text-decoration: none; font-size: 18px; opacity: {{ app()->getLocale() == 'en' ? '1' : '0.4' }};">🇺🇸</a>
            
            <a href="{{ route('login') }}" class="btn-login">
                {{ __('messages.nav_enter') }}
            </a> 
        </div>
    </nav>

    <!-- HERO HEADER -->
    <header class="landing-hero">
        <h1>{!! __('messages.hero_title') !!}</h1>
        <p>{{ __('messages.hero_subtitle') }}</p>
        <div class="hero-buttons">
            <a href="{{ route('login') }}?tab=register" class="btn-marketing btn-creador">
                {{ __('messages.btn_creador_hero') }}
            </a>
            <a href="#contacto" class="btn-marketing btn-lab">
                {{ __('messages.btn_lab_hero') }}
            </a>
        </div>
    </header>

    <!-- MATRIZ DE PROBLEMA & SOLUCIÓN -->
    <section class="prob-sol-grid">
        <div class="card card-problem">
            <h3 style="color: #e74c3c;">{{ __('messages.prob_title') }}</h3>
            <p>{{ __('messages.prob_desc') }}</p>
        </div>
        <div class="card card-solution">
            <h3 style="color: #2ecc71;">{{ __('messages.sol_title') }}</h3>
            <p>{{ __('messages.sol_desc') }}</p>
        </div>
    </section>

    <!-- CRONOGRAMA DE PASOS -->
    <section id="como-funciona" class="steps">
        <h2>{{ __('messages.steps_title') }}</h2>
        <div class="steps-grid">
            <div class="step-item">
                <div class="step-number">1</div>
                <h3>{{ __('messages.step1_title') }}</h3>
                <p>{{ __('messages.step1_desc') }}</p>
            </div>
            <div class="step-item">
                <div class="step-number">2</div>
                <h3>{{ __('messages.step2_title') }}</h3>
                <p>{{ __('messages.step2_desc') }}</p>
            </div>
            <div class="step-item">
                <div class="step-number">3</div>
                <h3>{{ __('messages.step3_title') }}</h3>
                <p>{{ __('messages.step3_desc') }}</p>
            </div>
        </div>
    </section>

    <!-- MATRIZ DE BENEFICIOS DUALES -->
    <section id="beneficios" class="benefits">
        <h2>{{ __('messages.ben_title') }}</h2>
        <div class="benefits-grid">
            <div class="b-card b-creador">
                <h3>{{ __('messages.ben_creador_title') }}</h3>
                <ul class="check-list">
                    <li>
                        {{ __('messages.ben_creador_1') }}
                        <span>{{ __('messages.ben_creador_1_desc') }}</span>
                    </li>
                    <li>
                        {{ __('messages.ben_creador_2') }}
                        <span>{{ __('messages.ben_creador_2_desc') }}</span>
                    </li>
                    <li>
                        {{ __('messages.ben_creador_3') }}
                        <span>{{ __('messages.ben_creador_3_desc') }}</span>
                    </li>
                </ul>
            </div>
            
            <div class="b-card b-lab">
                <h3>{{ __('messages.ben_lab_title') }}</h3>
                <ul class="check-list">
                    <li>
                        {{ __('messages.ben_lab_1') }}
                        <span>{{ __('messages.ben_lab_1_desc') }}</span>
                    </li>
                    <li>
                        {{ __('messages.ben_lab_2') }}
                        <span>{{ __('messages.ben_lab_2_desc') }}</span>
                    </li>
                    <li>
                        {{ __('messages.ben_lab_3') }}
                        <span>{{ __('messages.ben_lab_3_desc') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- RESPALDO CONTABLE -->
    <section class="token-promise">
        <h2>{{ __('messages.token_title') }}</h2>
        <p>{{ __('messages.token_desc_1') }} <strong>1 FC {{ __('messages.token_desc_2') }}</strong></p>
    </section>
</div>

<!-- PIE DE PÁGINA COMERCIAL -->
<footer id="contacto">
    <div class="container">
        <h2>{{ __('messages.footer_title') }}</h2>
        <a href="{{ route('login') }}?tab=register" class="btn-marketing btn-creador" style="padding: 15px 50px;">{{ __('messages.btn_start_now') }}</a>
        
        <div class="links">
            <a href="{{ route('login') }}">{{ __('messages.link_login_ecosystem') }}</a>
            <a href="#">{{ __('messages.link_terms') }}</a>
            <a href="#">{{ __('messages.link_contact') }}</a>
        </div>
        <p style="margin-top: 30px; font-size: 12px; color: #7f8c8d;">
            &copy; {{ date('Y') }} FabCoins - {{ __('messages.footer_rights') }}
        </p>
    </div>
</footer>
@endsection

@section('body-class', 'landing-page landing-page-body')
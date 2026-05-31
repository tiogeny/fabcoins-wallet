@extends('layouts.app')

@section('title', __('messages.landing_title'))

@section('content')
<div class="container">
    <nav style="display: flex; justify-content: space-between; align-items: center; padding: 20px 0; border-bottom: 1px solid rgba(255,255,255,0.05);">
        <div class="logo" style="font-size: 28px; font-weight: 700; font-family: 'Rajdhani', sans-serif; color: var(--text-light); letter-spacing: 1px;">
            ⚙️ Fab<span style="color: var(--accent-blue);">Coins</span>
        </div>
        <div class="nav-links">
            <a href="#como-funciona" style="color: var(--text-main); margin-left: 30px; font-weight: 600; font-size: 14px;">{{ __('messages.nav_how_it_works') }}</a>
            <a href="#beneficios" style="color: var(--text-main); margin-left: 30px; font-weight: 600; font-size: 14px;">{{ __('messages.nav_benefits') }}</a>
            
            <a href="?lang=es" style="margin-left: 30px; font-size: 18px; opacity: {{ app()->getLocale() == 'es' ? '1' : '0.4' }};">🇪🇸</a>
            <a href="?lang=en" style="margin-left: 15px; font-size: 18px; opacity: {{ app()->getLocale() == 'en' ? '1' : '0.4' }};">🇺🇸</a>
            
            <a href="{{ route('login') }}" class="btn-login" style="color: var(--bg-dark) !important; background: var(--text-light); padding: 8px 20px; border-radius: 4px; margin-left: 30px; font-weight: 600; font-size: 14px;">
                {{ __('messages.nav_enter') }}
            </a> 
        </div>
    </nav>

    <header class="hero" style="padding: 100px 0 80px; text-align: left;">
        <h1 style="font-size: 4rem; line-height: 1.1; margin-bottom: 20px; letter-spacing: -1px;">
            {!! __('messages.hero_title') !!}
        </h1>
        <p style="font-size: 1.2rem; max-width: 600px; margin-bottom: 40px; color: #95a5a6;">
            {{ __('messages.hero_subtitle') }}
        </p>
        <div class="hero-buttons" style="display: flex; gap: 20px; flex-wrap: wrap;">
            <a href="{{ route('register') }}" class="btn btn-maker">
                {{ __('messages.btn_maker_hero') }}
            </a>
            <a href="#contacto" class="btn btn-lab">
                {{ __('messages.btn_lab_hero') }}
            </a>
        </div>
    </header>

    <section class="prob-sol-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px; margin: 60px 0;">
        <div class="card card-problem" style="border-left: 4px solid var(--accent-red);">
            <h3 style="color: var(--accent-red); font-size: 1.5rem; margin-bottom: 10px;">{{ __('messages.prob_title') }}</h3>
            <p>{{ __('messages.prob_desc') }}</p>
        </div>
        <div class="card card-solution" style="border-left: 4px solid var(--accent-green);">
            <h3 style="color: var(--accent-green); font-size: 1.5rem; margin-bottom: 10px;">{{ __('messages.sol_title') }}</h3>
            <p>{{ __('messages.sol_desc') }}</p>
        </div>
    </section>

    <section id="como-funciona" class="steps" style="text-align: center; margin: 100px 0;">
        <h2 style="font-size: 2.5rem; margin-bottom: 50px;">{{ __('messages.steps_title') }}</h2>
        <div class="steps-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 40px;">
            <div class="step-item">
                <div class="step-number" style="font-family: 'Rajdhani', sans-serif; font-size: 4rem; font-weight: 700; color: var(--accent-blue); line-height: 1; margin-bottom: 10px;">1</div>
                <h3 style="margin-bottom: 15px;">{{ __('messages.step1_title') }}</h3>
                <p style="font-size: 0.95rem; color: #95a5a6;">{{ __('messages.step1_desc') }}</p>
            </div>
            <div class="step-item">
                <div class="step-number" style="font-family: 'Rajdhani', sans-serif; font-size: 4rem; font-weight: 700; color: var(--accent-blue); line-height: 1; margin-bottom: 10px;">2</div>
                <h3 style="margin-bottom: 15px;">{{ __('messages.step2_title') }}</h3>
                <p style="font-size: 0.95rem; color: #95a5a6;">{{ __('messages.step2_desc') }}</p>
            </div>
            <div class="step-item">
                <div class="step-number" style="font-family: 'Rajdhani', sans-serif; font-size: 4rem; font-weight: 700; color: var(--accent-blue); line-height: 1; margin-bottom: 10px;">3</div>
                <h3 style="margin-bottom: 15px;">{{ __('messages.step3_title') }}</h3>
                <p style="font-size: 0.95rem; color: #95a5a6;">{{ __('messages.step3_desc') }}</p>
            </div>
        </div>
    </section>

    <section id="beneficios" class="benefits" style="margin: 100px 0;">
        <h2 style="text-align: center; font-size: 2.5rem; margin-bottom: 50px;">{{ __('messages.ben_title') }}</h2>
        <div class="benefits-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
            <div class="b-card b-maker" style="padding: 40px; border-radius: 8px; border: 1px solid rgba(155, 89, 182, 0.3); box-shadow: inset 0 0 50px rgba(155, 89, 182, 0.05);">
                <h3 style="color: var(--accent-purple); font-size: 2rem; margin-bottom: 20px; border-bottom: 1px solid rgba(155,89,182,0.2); padding-bottom: 10px;">{{ __('messages.ben_maker_title') }}</h3>
                <ul class="check-list" style="list-style: none;">
                    <li style="margin-bottom: 15px; padding-left: 30px; position: relative; color: var(--text-light); font-weight: 600;">
                        {{ __('messages.ben_maker_1') }}
                        <span style="display: block; font-weight: 400; color: #95a5a6; font-size: 0.9rem; margin-top: 4px;">{{ __('messages.ben_maker_1_desc') }}</span>
                    </li>
                    <li style="margin-bottom: 15px; padding-left: 30px; position: relative; color: var(--text-light); font-weight: 600;">
                        {{ __('messages.ben_maker_2') }}
                        <span style="display: block; font-weight: 400; color: #95a5a6; font-size: 0.9rem; margin-top: 4px;">{{ __('messages.ben_maker_2_desc') }}</span>
                    </li>
                    <li style="margin-bottom: 15px; padding-left: 30px; position: relative; color: var(--text-light); font-weight: 600;">
                        {{ __('messages.ben_maker_3') }}
                        <span style="display: block; font-weight: 400; color: #95a5a6; font-size: 0.9rem; margin-top: 4px;">{{ __('messages.ben_maker_3_desc') }}</span>
                    </li>
                </ul>
            </div>
            
            <div class="b-card b-lab" style="padding: 40px; border-radius: 8px; border: 1px solid rgba(243, 156, 18, 0.3); box-shadow: inset 0 0 50px rgba(243, 156, 18, 0.05); ">
                <h3 style="color: var(--accent-orange); font-size: 2rem; margin-bottom: 20px; border-bottom: 1px solid rgba(243,156,18,0.2); padding-bottom: 10px;">{{ __('messages.ben_lab_title') }}</h3>
                <ul class="check-list" style="list-style: none;">
                    <li style="margin-bottom: 15px; padding-left: 30px; position: relative; color: var(--text-light); font-weight: 600;">
                        {{ __('messages.ben_lab_1') }}
                        <span style="display: block; font-weight: 400; color: #95a5a6; font-size: 0.9rem; margin-top: 4px;">{{ __('messages.ben_lab_1_desc') }}</span>
                    </li>
                    <li style="margin-bottom: 15px; padding-left: 30px; position: relative; color: var(--text-light); font-weight: 600;">
                        {{ __('messages.ben_lab_2') }}
                        <span style="display: block; font-weight: 400; color: #95a5a6; font-size: 0.9rem; margin-top: 4px;">{{ __('messages.ben_lab_2_desc') }}</span>
                    </li>
                    <li style="margin-bottom: 15px; padding-left: 30px; position: relative; color: var(--text-light); font-weight: 600;">
                        {{ __('messages.ben_lab_3') }}
                        <span style="display: block; font-weight: 400; color: #95a5a6; font-size: 0.9rem; margin-top: 4px;">{{ __('messages.ben_lab_3_desc') }}</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <section class="token-promise" style="text-align: center; margin: 100px auto; max-width: 800px; padding: 50px; border: 2px solid var(--accent-green); border-radius: 8px; background: rgba(46, 204, 113, 0.05); box-shadow: 0 0 40px rgba(46, 204, 113, 0.1);">
        <h2 style="font-size: 2.5rem; color: var(--accent-green); margin-bottom: 20px;">{{ __('messages.token_title') }}</h2>
        <p style="font-size: 1.1rem; color: var(--text-light);">
            {{ __('messages.token_desc_1') }} <strong>1 FC {{ __('messages.token_desc_2') }}</strong>
        </p>
    </section>
</div>

<footer id="contacto" style="background: #07080a; padding: 60px 0; text-align: center; border-top: 1px solid rgba(255,255,255,0.05);">
    <div class="container">
        <h2 style="margin-bottom: 30px; font-size: 2rem;">{{ __('messages.footer_title') }}</h2>
        <a href="{{ route('register') }}" class="btn btn-maker" style="padding: 15px 50px;">{{ __('messages.btn_start_now') }}</a>
        
        <div class="links" style="margin-top: 40px; display: flex; justify-content: center; gap: 30px;">
            <a href="{{ route('login') }}" style="color: #95a5a6; font-size: 0.9rem;">{{ __('messages.link_login_ecosystem') }}</a>
            <a href="#" style="color: #95a5a6; font-size: 0.9rem;">{{ __('messages.link_terms') }}</a>
            <a href="#" style="color: #95a5a6; font-size: 0.9rem;">{{ __('messages.link_contact') }}</a>
        </div>
        <p style="margin-top: 30px; font-size: 12px; color: #7f8c8d;">
            &copy; {{ date('Y') }} FabCoins - {{ __('messages.footer_rights') }}
        </p>
    </div>
</footer>
@endsection

@section('body-class', 'landing-page')
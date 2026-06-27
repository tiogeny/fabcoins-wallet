<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'FabCoins | Distributed Economy')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&family=Rajdhani:wght@500;700&display=swap" rel="stylesheet">
    
    <link rel="icon" type="image/webp" href="{{ asset('images/logo-icon.webp') }}">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}?v=6.2">
    @stack('styles')

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css"/>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="@yield('body-class')">

    @yield('content')

    @if(session('msg'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    background: '#1a252f', color: '#fff', confirmButtonColor: '#3498db',
                    timer: 4000, timerProgressBar: true, icon: 'success',
                    title: '{{ __("messages.msg_" . session("msg")) }}'
                });
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    background: '#1a252f', color: '#fff', confirmButtonColor: '#e74c3c',
                    icon: 'error', title: 'Attention', text: '{{ session("error") }}'
                });
            });
        </script>
    @endif

    @stack('scripts')
    @yield('scripts')
</body>
</html>
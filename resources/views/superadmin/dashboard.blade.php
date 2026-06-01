@extends('layouts.app')

@section('title', 'Consola Global SuperAdmin')

@section('content')
<div class="container" style="margin-top: 20px; margin-bottom: 40px;">
    
    <header class="header">
        <h1 style="margin: 0; font-size: 22px;">🌐 Dashboard Global FabCoins</h1>
        <div>
            <span style="font-size: 14px; margin-right: 15px;">Usuario: {{ $superadmin->name }}</span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn-logout" style="background:none; border:none; color:var(--c-red); cursor:pointer;">Cerrar Sesión</button>
            </form>
        </div>
    </header>

    <div id="global-alert" style="margin-top:15px;">
        @if(session('msg') == 'lab_ok') <div class="alert alert-success">✅ Lab registrado de forma oficial. Invitación despachada al correo.</div> @endif
        @if(session('msg') == 'cat_ok') <div class="alert alert-success">✅ Ítems tecnológicos inyectados al Catálogo Global con éxito.</div> @endif
        @if(session('msg') == 'pct_updated') <div class="alert alert-info">✅ Coeficientes de Política Monetaria actualizados en la red.</div> @endif
        @if(session('msg') == 'precio_ok') <div class="alert alert-info">✅ Precio comercial de referencia actualizado correctamente.</div> @endif
        @if(session('msg') == 'borrado_ok') <div class="alert alert-danger">🗑️ Ítem tecnológico removido de la base del mercado.</div> @endif
    </div>

    @include('superadmin.partials.kpis')
    @include('superadmin.partials.dpi')

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 25px;">
        @include('superadmin.partials.forms')
    </div>

    @include('superadmin.partials.catalog_form')
    @include('superadmin.partials.catalog_table')
    @include('superadmin.partials.rankings')

</div>
@endsection

@section('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(() => {
            let box = document.getElementById('global-alert');
            if(box) box.style.display = 'none';
        }, 5000);
    });

    function cargarDesglose(tipo, titulo, icono) {
        Swal.fire({
            title: icono + ' Cargando...',
            html: '⏳ Consultando registros analíticos del libro mayor...',
            background: '#1a252f',
            color: '#fff',
            showConfirmButton: false,
            allowOutsideClick: false
        });

        fetch(`{{ route('superadmin.ajax_desglose') }}?ajax_desglose=${tipo}`)
            .then(r => r.text())
            .then(html => {
                Swal.fire({
                    title: icono + ' ' + titulo,
                    html: html,
                    background: '#1a252f',
                    color: '#fff',
                    width: '650px',
                    confirmButtonColor: '#3498db',
                    confirmButtonText: 'Cerrar'
                });
            });
    }

    function agregarFilaAdmin() {
        let container = document.getElementById('contenedor-filas-admin');
        let newRow = container.querySelector('.row-catalogo').cloneNode(true);
        newRow.querySelectorAll('input').forEach(i => i.value = '');
        newRow.querySelector('.input-unidad-admin').value = 'hora';
        
        let espaciador = newRow.querySelector('.espaciador-borrar');
        if (espaciador) {
            espaciador.outerHTML = '<button type="button" onclick="this.parentElement.remove()" style="background:transparent; color:#e74c3c; border:none; font-size:22px; cursor:pointer;">&times;</button>';
        }
        container.appendChild(newRow);
    }
</script>
@endsection
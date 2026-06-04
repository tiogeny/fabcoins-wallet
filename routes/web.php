<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Lab\DashboardController as LabDashboard;
use App\Http\Controllers\Lab\AssetController;

// --- 🌐 RUTA DE TU LANDING PAGE ---
Route::get('/', function () {
    return view('landing');
})->middleware('locale')->name('home');

// --- 🔐 RUTAS PROTEGIDAS DEL ECOSISTEMA ---
Route::middleware(['auth', 'locale'])->group(function () {

    /**
     * 🎛️ CONTROLADOR DE TRÁFICO DE ROLES
     * Recibe el login de Breeze y redirige al panel correcto
     */
    Route::get('/dashboard', function () {
        $role = auth()->user()->role;
        
        if ($role === 'lab') {
            return redirect()->route('lab.dashboard');
        } elseif ($role === 'maker') {
            return redirect()->route('maker.dashboard');
        } elseif ($role === 'superadmin') {
            return redirect()->route('admin.dashboard');
        }
        
        return redirect()->route('home');
    })->name('dashboard');

    // 🏢 PORTAL DE OPERACIONES DEL LABORATORIO (Rol 'lab')
    Route::middleware(['role:lab'])->prefix('lab')->group(function () {
        // Vista Principal Orquestadora
        Route::get('/dashboard', [\App\Http\Controllers\Lab\DashboardController::class, 'index'])->name('lab.dashboard');
        
        // Navegación Estética del Header
        Route::get('/profile/edit', [\App\Http\Controllers\Lab\DashboardController::class, 'index'])->name('lab.profile.edit');
        Route::get('/notifications/read', [\App\Http\Controllers\Lab\DashboardController::class, 'readNotifications'])->name('lab.read_notifs');

        // 🛡️ FUSIBLES DE ACCIÓN: Inmunidad total contra RouteNotFoundException
        Route::post('/tokenize', function() { return redirect()->back(); })->name('lab.tokenize');
        Route::post('/mission/create', function() { return redirect()->back(); })->name('lab.create_mission');
        Route::post('/mission/complete', function() { return redirect()->back(); })->name('lab.complete_mission');
        Route::post('/order/reschedule', function() { return redirect()->back(); })->name('lab.reschedule');
        Route::post('/profile/update', function() { return redirect()->back(); })->name('lab.update_profile');
        Route::post('/profile/password', function() { return redirect()->back(); })->name('lab.change_password');

        Route::post('/asset/store', [AssetController::class, 'store'])->name('lab.asset.store');

        Route::delete('/asset/destroy/{id}', [AssetController::class, 'destroy'])->name('lab.asset.destroy');

        Route::post('/asset/tokenise', [App\Http\Controllers\Lab\AssetController::class, 'tokenise'])->name('lab.asset.tokenise');

        Route::post('/asset/update-price', [App\Http\Controllers\Lab\AssetController::class, 'updatePrice'])->name('lab.asset.updatePrice');
    });

    // 🎨 PORTAL DE OPERACIONES DEL MAKER (Rol 'maker')
    Route::middleware(['role:maker'])->prefix('maker')->group(function () {
        // Dashboard Principal y Notificaciones
        Route::get('/dashboard', [\App\Http\Controllers\Maker\DashboardController::class, 'index'])->name('maker.dashboard');
        Route::get('/notificaciones/leer', [\App\Http\Controllers\Maker\DashboardController::class, 'readNotifications'])->name('maker.read_notifs');
        
        // Gestión de Portafolio e Identidad Técnica
        Route::post('/perfil/actualizar', [\App\Http\Controllers\Maker\ProfileController::class, 'update'])->name('maker.update_profile');
        Route::post('/perfil/seguridad', [\App\Http\Controllers\Maker\ProfileController::class, 'security'])->name('maker.change_password');

        // Lógica de Contratación, Créditos e Inyecciones P2P
        Route::post('/mision/postular', [\App\Http\Controllers\Maker\JobController::class, 'apply'])->name('maker.apply_mission');
        Route::post('/credito/firmar', [\App\Http\Controllers\Maker\JobController::class, 'signCredit'])->name('maker.sign_credit');
        Route::post('/transferencia/p2p', [\App\Http\Controllers\Maker\JobController::class, 'transferP2P'])->name('maker.transfer_p2p');
        Route::get('/p2p/validar-correo', [\App\Http\Controllers\Maker\JobController::class, 'checkEmailP2P'])->name('maker.check_email_p2p');

        // Alquileres de Hardware, Reprogramaciones y Reseñas
        Route::post('/mercado/reservar', [\App\Http\Controllers\Maker\ReservationController::class, 'book'])->name('maker.book_asset');
        Route::post('/reserva/aceptar-fecha', [\App\Http\Controllers\Maker\ReservationController::class, 'acceptDate'])->name('maker.accept_date');
        Route::post('/reserva/cancelar-fecha', [\App\Http\Controllers\Maker\ReservationController::class, 'rejectDate'])->name('maker.reject_date');
        Route::post('/reserva/calificar', [\App\Http\Controllers\Maker\ReservationController::class, 'rateLab'])->name('maker.rate_lab');
    });

    // 🌐 CONSOLA MACROECONÓMICA DEL SUPERADMIN (Rol 'superadmin')
    Route::middleware(['role:superadmin'])->prefix('superadmin')->group(function () {
        // Dashboard Central (Soporta ambos nombres para evitar conflictos con el Auth del Framework)
        Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('superadmin.dashboard');
        Route::get('/admin-dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('admin.dashboard'); // 👈 ¡FUSIBLE DE SEGURIDAD AGREGADO!

        Route::get('/desglose', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'getAjaxDesglose'])->name('superadmin.ajax_desglose');

        // Módulo Regulatorio del Catálogo Global
        Route::post('/catalogo/guardar', [\App\Http\Controllers\SuperAdmin\CatalogController::class, 'storeMultiple'])->name('superadmin.catalog.store');
        Route::post('/catalogo/precio', [\App\Http\Controllers\SuperAdmin\CatalogController::class, 'updatePrice'])->name('superadmin.catalog.update');
        Route::post('/catalogo/eliminar', [\App\Http\Controllers\SuperAdmin\CatalogController::class, 'destroy'])->name('superadmin.catalog.destroy');

        // Módulo de Control de Red y Política Monetaria
        Route::post('/lab/invitar', [\App\Http\Controllers\SuperAdmin\SystemController::class, 'createLab'])->name('superadmin.lab.invite');
        Route::post('/politica/actualizar', [\App\Http\Controllers\SuperAdmin\SystemController::class, 'updatePolicy'])->name('superadmin.policy.update');
    });

    // 🌐 EXPEDIENTES PÚBLICOS Y MOTOR DE RECLUTAMIENTO GLOBAL
    Route::get('/profile/{slugOrId}', [\App\Http\Controllers\PublicProfileController::class, 'show'])->name('public.profile');
    Route::post('/profile/{slugOrId}/invite', [\App\Http\Controllers\PublicProfileController::class, 'invite'])->name('public.profile.invite');


});

// --- 🔐 RUTAS DE AUTENTICACIÓN PROTEGIDAS POR LOCALE ---
Route::middleware(['locale'])->group(function () {
    require __DIR__.'/auth.php';
});
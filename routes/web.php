<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Lab\DashboardController as LabDashboard;

// --- 🌐 RUTA DE TU LANDING PAGE ---
Route::get('/', function () {
    return view('index');
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

    // 🏢 PORTAL DE CONTROL DEL LABORATORIO (Arquitectura Limpia)
    Route::middleware(['role:lab'])->prefix('lab')->group(function () {
        // Dashboard General, Notificaciones y Finanzas
        Route::get('/dashboard', [\App\Http\Controllers\Lab\DashboardController::class, 'index'])->name('lab.dashboard');
        Route::get('/notificaciones/leer', [\App\Http\Controllers\Lab\DashboardController::class, 'readNotifications'])->name('lab.read_notifs');
        Route::get('/exportar-cuenta', [\App\Http\Controllers\Lab\DashboardController::class, 'exportCsv'])->name('lab.export_csv');
        Route::post('/perfil/actualizar', [\App\Http\Controllers\Lab\DashboardController::class, 'updateProfile'])->name('lab.update_profile');
        Route::post('/perfil/seguridad', [\App\Http\Controllers\Lab\DashboardController::class, 'changePassword'])->name('lab.change_password');

        // Módulo de Activos e Inventario (Bóveda)
        Route::post('/tokenizar', [\App\Http\Controllers\Lab\AssetController::class, 'tokenize'])->name('lab.tokenize');
        Route::post('/activo/retirar', [\App\Http\Controllers\Lab\AssetController::class, 'retireAsset'])->name('lab.retire_asset');
        Route::post('/activo/precio', [\App\Http\Controllers\Lab\AssetController::class, 'updatePrice'])->name('lab.update_price');

        // Módulo de Empleo y Misiones
        Route::post('/mision/crear', [\App\Http\Controllers\Lab\MissionController::class, 'createMission'])->name('lab.create_mission');
        Route::post('/mision/asignar', [\App\Http\Controllers\Lab\MissionController::class, 'assignMaker'])->name('lab.assign_maker');
        Route::post('/mision/rechazar', [\App\Http\Controllers\Lab\MissionController::class, 'rejectMaker'])->name('lab.reject_maker');
        Route::post('/mision/evaluar', [\App\Http\Controllers\Lab\MissionController::class, 'completeMission'])->name('lab.complete_mission');

        // Módulo de Alquileres de Máquinas y Créditos ISA
        Route::post('/reserva/procesar', [\App\Http\Controllers\Lab\CreditController::class, 'processReservation'])->name('lab.process_reservation');
        Route::post('/reserva/reprogramar', [\App\Http\Controllers\Lab\CreditController::class, 'reschedule'])->name('lab.reschedule');
        Route::post('/credito/proponer', [\App\Http\Controllers\Lab\CreditController::class, 'proposeCredit'])->name('lab.propose_credit');
        Route::post('/credito/cancelar', [\App\Http\Controllers\Lab\CreditController::class, 'cancelCredit'])->name('lab.cancel_credit');
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

require __DIR__.'/auth.php';
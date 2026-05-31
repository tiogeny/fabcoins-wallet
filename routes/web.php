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

    // 🛠️ PANEL DE CONTROL DEL MAKER (Rol 'maker')
    Route::middleware(['role:maker'])->prefix('maker')->group(function () {
        Route::get('/dashboard', function() { return "Aquí irá tu maker.php"; })->name('maker.dashboard');
    });

    // 🌐 PANEL DE CONTROL DEL SUPERADMIN (Rol 'superadmin')
    Route::middleware(['role:superadmin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function() { return "Aquí irá tu superadmin.php"; })->name('admin.dashboard');
    });
    
});

require __DIR__.'/auth.php';
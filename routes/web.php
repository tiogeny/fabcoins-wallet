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
        $user = auth()->user();
        
        // 🔒 CANDADO DE SEGURIDAD: Si es su primer ingreso con clave temporal
        if ($user->force_password_change) {
            $userId = $user->id;
            auth()->logout(); // Lo sacamos para que complete el proceso de forma segura
            
            return view('auth.login', [
                'require_onboarding' => true,
                'temp_user_id' => $userId
            ]);
        }

        // Flujo normal si ya cambió su clave anteriormente:
        $role = $user->role;
        if ($role === 'lab') {
            return redirect()->route('lab.dashboard');
        } elseif ($role === 'creator') {
            return redirect()->route('creator.dashboard');
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
        
        Route::post('/profile/update', [\App\Http\Controllers\Lab\DashboardController::class, 'updateProfile'])->name('lab.update_profile');
        Route::post('/profile/export-csv', [\App\Http\Controllers\Lab\DashboardController::class, 'exportCSV'])->name('lab.profile.export_csv');
        Route::post('/profile/password', function() { return redirect()->back(); })->name('lab.change_password');

        Route::post('/asset/store', [AssetController::class, 'store'])->name('lab.asset.store');

        Route::delete('/asset/destroy/{id}', [AssetController::class, 'destroy'])->name('lab.asset.destroy');

        Route::post('/asset/tokenise', [App\Http\Controllers\Lab\AssetController::class, 'tokenise'])->name('lab.asset.tokenise');

        Route::post('/asset/update-price', [App\Http\Controllers\Lab\AssetController::class, 'updatePrice'])->name('lab.asset.updatePrice');

        // 🎯 CONTROL DE RESERVAS Y ALQUILERES (ÓRDENES)
        Route::post('/order/approve', [App\Http\Controllers\Lab\OrderController::class, 'approve'])->name('lab.order.approve');
        Route::post('/order/reject', [App\Http\Controllers\Lab\OrderController::class, 'reject'])->name('lab.order.reject');
        Route::post('/order/reschedule', [App\Http\Controllers\Lab\OrderController::class, 'reschedule'])->name('lab.order.reschedule');

        // 🎯 CONTROL DE CONTRATACIÓN Y ESCROW (WORKSPACE 3)
        Route::post('/mission/store', [App\Http\Controllers\Lab\MissionController::class, 'store'])->name('lab.mission.store');
        Route::post('/mission/assign', [App\Http\Controllers\Lab\MissionController::class, 'assignCreator'])->name('lab.mission.assign');
        Route::post('/mission/reject', [App\Http\Controllers\Lab\MissionController::class, 'rejectCreator'])->name('lab.mission.reject');
        Route::post('/mission/complete', [App\Http\Controllers\Lab\MissionController::class, 'completeMission'])->name('lab.mission.complete');

        // 🎓 CONTROL DE CRÉDITOS ISA
        Route::post('/credit/approve', [\App\Http\Controllers\Lab\CreditController::class, 'approve'])->name('lab.credit.approve');
        Route::post('/credit/reject', [\App\Http\Controllers\Lab\CreditController::class, 'reject'])->name('lab.credit.reject');

    });

    // 🎨 PORTAL DE OPERACIONES DEL CREATOR (Rol 'creator')
    Route::middleware(['role:creator'])->prefix('creator')->group(function () {
        // Dashboard Principal y Notificaciones
        Route::get('/dashboard', [\App\Http\Controllers\Creator\DashboardController::class, 'index'])->name('creator.dashboard');
        Route::get('/notificaciones/leer', [\App\Http\Controllers\Creator\DashboardController::class, 'readNotifications'])->name('creator.read_notifs');
        
        // Gestión de Portafolio e Identidad Técnica
        Route::post('/perfil/actualizar', [\App\Http\Controllers\Creator\ProfileController::class, 'update'])->name('creator.update_profile');
        Route::post('/perfil/seguridad', [\App\Http\Controllers\Creator\ProfileController::class, 'security'])->name('creator.change_password');

        // Lógica de Contratación, Créditos e Inyecciones P2P
        Route::post('/mision/postular', [\App\Http\Controllers\Creator\JobController::class, 'apply'])->name('creator.apply_mission');
        Route::post('/credito/firmar', [\App\Http\Controllers\Creator\JobController::class, 'signCredit'])->name('creator.sign_credit');
        Route::post('/transferencia/p2p', [\App\Http\Controllers\Creator\JobController::class, 'transferP2P'])->name('creator.transfer_p2p');
        Route::get('/p2p/validar-correo', [\App\Http\Controllers\Creator\JobController::class, 'checkEmailP2P'])->name('creator.check_email_p2p');

        // Alquileres de Hardware, Reprogramaciones y Reseñas
        Route::post('/mercado/reservar', [\App\Http\Controllers\Creator\ReservationController::class, 'book'])->name('creator.book_asset');
        Route::post('/reserva/aceptar-fecha', [\App\Http\Controllers\Creator\ReservationController::class, 'acceptDate'])->name('creator.accept_date');
        Route::post('/reserva/cancelar-fecha', [\App\Http\Controllers\Creator\ReservationController::class, 'rejectDate'])->name('creator.reject_date');
        Route::post('/reserva/calificar', [\App\Http\Controllers\Creator\ReservationController::class, 'rateLab'])->name('creator.rate_lab');

        Route::post('/mission/accept-invite', [\App\Http\Controllers\Creator\JobController::class, 'acceptInvite'])->name('creator.mission.accept_invite');
        Route::post('/mission/reject-invite', [\App\Http\Controllers\Creator\JobController::class, 'rejectInvite'])->name('creator.mission.reject_invite');

        Route::post('/pay-debt', [\App\Http\Controllers\Creator\JobController::class, 'payDebt'])->name('creator.pay_debt');
    });

    // 🌐 CONSOLA MACROECONÓMICA DEL SUPERADMIN (Rol 'superadmin')
    Route::middleware(['role:superadmin'])->prefix('superadmin')->group(function () {
        // Ambas rutas apuntan al controlador principal pero con URIs únicas para no pisarse las firmas
        Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('superadmin.dashboard');
        Route::get('/console', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('admin.dashboard'); // 👈 Cambiado a /console

        Route::get('/desglose', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'getAjaxDesglose'])->name('superadmin.ajax_desglose');

        // Módulo Regulatorio del Catálogo Global
        Route::post('/catalogo/guardar', [\App\Http\Controllers\SuperAdmin\CatalogController::class, 'storeMultiple'])->name('superadmin.catalog.store');
        Route::post('/catalogo/precio', [\App\Http\Controllers\SuperAdmin\CatalogController::class, 'updatePrice'])->name('superadmin.catalog.update');
        Route::post('/catalogo/eliminar', [\App\Http\Controllers\SuperAdmin\CatalogController::class, 'destroy'])->name('superadmin.catalog.destroy');

        // Módulo de Control de Red y Política Monetaria
        Route::post('/lab/invitar', [\App\Http\Controllers\SuperAdmin\SystemController::class, 'createLab'])->name('superadmin.lab.invite');
        Route::post('/politica/actualizar', [\App\Http\Controllers\SuperAdmin\SystemController::class, 'updatePolicy'])->name('superadmin.policy.update');

        Route::post('/habilidades/guardar-multiple', [\App\Http\Controllers\SuperAdmin\CatalogController::class, 'storeMultipleSkills'])->name('superadmin.skills.store_multiple');
    }); // <-- Cierra superadmin

    // 📨 Reclutamiento (dentro de auth, requiere login)
    Route::post('/profile/{slugOrId}/invite', [\App\Http\Controllers\PublicProfileController::class, 'invite'])->name('public.profile.invite');

}); // <-- Cierre del grupo principal de auth y locale

// --- 🔐 RUTAS DE AUTENTICACIÓN PROTEGIDAS POR LOCALE ---
Route::middleware(['locale'])->group(function () {
    // 🔓 RUTA LIBRE: Permite procesar el formulario de clave nueva sin estar logueado aún
    Route::post('/onboarding/complete', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'completeOnboarding'])->name('onboarding.complete');
        
    require __DIR__.'/auth.php';
});

// =========================================================================
// 🌐 EXPEDIENTES TOTALMENTE PÚBLICOS
// =========================================================================
Route::middleware(['locale'])->group(function () {
    Route::get('/profile/{slugOrId}', [\App\Http\Controllers\PublicProfileController::class, 'show'])->name('public.profile');
});

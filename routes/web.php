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

    // 🏢 PANEL DE CONTROL DEL LABORATORIO (Rol 'lab')
    Route::middleware(['role:lab'])->prefix('lab')->group(function () {
        Route::get('/dashboard', [LabDashboard::class, 'index'])->name('lab.dashboard');
        Route::post('/tokenizar', [LabDashboard::class, 'tokenize'])->name('lab.tokenize');
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
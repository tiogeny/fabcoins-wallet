<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Lab\DashboardController as LabDashboard;

// --- 🌐 RUTA DE TU LANDING PAGE (fabcoins.co) ---
// Aplicamos el middleware 'locale' para que las banderas cambien el idioma de la landing
Route::get('/', function () {
    return view('index'); // Aquí se mostrará tu landing original
})->middleware('locale')->name('home');

// --- 🔐 RUTAS PROTEGIDAS DEL ECOSISTEMA (fabcoins.co/wallet) ---
Route::middleware(['auth', 'locale'])->group(function () {

    // 🏢 PANEL DE CONTROL DEL LABORATORIO (Exclusivo para rol 'lab')
    Route::middleware(['role:lab'])->prefix('lab')->group(function () {
        Route::get('/dashboard', [LabDashboard::class, 'index'])->name('lab.dashboard');
        Route::post('/tokenizar', [LabDashboard::class, 'tokenize'])->name('lab.tokenize');
    });

    // 🛠️ PANEL DE CONTROL DEL MAKER (Exclusivo para rol 'maker')
    Route::middleware(['role:maker'])->prefix('maker')->group(function () {
        Route::get('/dashboard', function() { return "Aquí irá tu maker.php"; })->name('maker.dashboard');
    });

    // 🌐 PANEL DE CONTROL DEL SUPERADMIN (Exclusivo para rol 'superadmin')
    Route::middleware(['role:superadmin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', function() { return "Aquí irá tu superadmin.php"; })->name('admin.dashboard');
    });
    
});

// Trae las rutas por defecto de Laravel Breeze (Login, Registro, Password Reset)
require __DIR__.'/auth.php';
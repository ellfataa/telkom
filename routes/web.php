<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// 1. Halaman Depan (Welcome)
Route::get('/', function () {
    if (auth('web')->check()) {
        return redirect()->route('home');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

// 2. Group Middleware
Route::middleware(['auth', 'verified'])->group(function () {

    // Menu Dashboard / Home
    Route::get('/home', function () {
        return Inertia::render('Home');
    })->name('home');

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Menu Kalkulator
    Route::get('/kalkulator', function () {
        return Inertia::render('Kalkulator');
    })->name('kalkulator');

    // Menu Produk
    Route::get('/produk', function () {
        return Inertia::render('Produk');
    })->name('produk');

    // Menu Laporan
    Route::get('/laporan', function () {
        return Inertia::render('Laporan');
    })->name('laporan');

    // Menu Edit Laporan
    Route::get('/laporan/{id}/edit', function ($id) {
        return Inertia::render('LaporanEdit', ['id' => $id]);
    })->name('laporan.edit');

    // Profile Routes (Mengarah ke Controller)
    // Note: Logika database ada di DALAM file ProfileController, bukan di sini.
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Logout
    Route::post('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout');

});

// Load route auth bawaan Breeze (Login, Register, Logout)
require __DIR__.'/auth.php';

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\LaporanController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// =================================================================
// PRODUK ROUTES
// =================================================================
// Custom routes produk harus diatas resource agar tidak dianggap ID
Route::delete('/produk/delete-all-produk', [ProdukController::class, 'deleteAllProduk']);
Route::post('/produk/import', [ProdukController::class, 'import']);

// Resource route (index, store, show, update, destroy)
Route::resource('produk', ProdukController::class);


// =================================================================
// LAPORAN / TRANSAKSI ROUTES
// =================================================================

// 1. Hapus Semua Laporan
// PENTING: Wajib ditaruh SEBELUM Route::resource agar 'delete-all-laporan'
// tidak dianggap sebagai parameter {id}
Route::delete('/laporan/delete-all-laporan', [LaporanController::class, 'deleteAllLaporan']);

// 2. Route untuk mengambil data detail edit (sesuai method showEdit di controller)
Route::get('/laporan/edit/{id}', [LaporanController::class, 'showEdit']);

// 3. Resource Route Laporan
// Ini akan otomatis menghandle:
// GET    /api/laporan          -> index()
// POST   /api/laporan          -> store()
// GET    /api/laporan/{id}     -> show() (jika ada)
// PUT    /api/laporan/{id}     -> update()
// DELETE /api/laporan/{id}     -> destroy()
Route::resource('laporan', LaporanController::class);

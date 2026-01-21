<?php

use App\Http\Controllers\TransaksiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\coba;
use App\Http\Controllers\ProdukController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Produk Routes
Route::delete('/produk/delete-all-produk', [ProdukController::class, 'deleteAllProduk']);
Route::post('/produk/import', [ProdukController::class, 'import']);
// Route Resources Produk
Route::resource('produk', ProdukController::class);



// Transaksi Routes
// Mendapatkan detail satu transaksi untuk diedit
Route::get('/laporan/edit/{id}', [TransaksiController::class, 'showEdit']);
// Route::get('/transaksi/{id}', [TransaksiController::class, 'show']);
Route::resource('transaksi', TransaksiController::class);


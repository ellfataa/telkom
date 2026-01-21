<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransaksiController extends Controller
{

    public function index()
    {
        try {
            // Mengambil data transaksi beserta children-nya menggunakan Join
            // Kita gunakan Raw SQL untuk performa maksimal
            $data = DB::select("
                SELECT 
                    t.id_transaksi,
                    t.netdiskon,
                    t.total_harga,
                    t.created_at,
                    t.pelanggan,
                    tc.id_produk,
                    tc.nama_produk,
                    tc.harga_produk,
                    tc.jumlah,
                    tc.durasi,
                    tc.harga_otc,
                    tc.diskon_produk,
                    tc.diskon_otc,
                    tc.nominal_diskon_produk,
                    tc.nominal_diskon_otc,
                    tc.ppn_produk,
                    tc.ppn_otc,
                    tc.produk_final,
                    tc.otc_final
                FROM transaksi t
                LEFT JOIN transaksi_children tc ON t.id_transaksi = tc.id_transaksi
                ORDER BY t.created_at DESC
            ");

            DB::commit();
            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack(); // Batalkan jika ada error
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Memulai Database Transaction untuk keamanan data
        DB::beginTransaction();

        try {
            $id_transaksi = 'TRX-' . strtoupper(Str::random(8));
            $now = now();

            // 1. Insert ke tabel 'transaksi' menggunakan Raw SQL
            DB::insert("
                INSERT INTO transaksi (id_transaksi, total_harga, pelanggan, netdiskon, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ", [
                $id_transaksi,
                $request->total_harga,
                strtolower($request->nama_pelanggan),
                $request->netDiskon,
                $now,
                $now
            ]);

            // 2. Insert ke tabel 'transaksi_children' menggunakan Raw SQL
            // Kita gunakan loop untuk menyiapkan data
            foreach ($request->items as $item) {
                DB::insert("
                    INSERT INTO transaksi_children (
                        id_transaksi, id_produk, nama_produk, harga_produk, bandwidth, 
                        jumlah, durasi, harga_otc, diskon_produk, diskon_otc, 
                        nominal_diskon_produk, nominal_diskon_otc, ppn_produk, 
                        ppn_otc, produk_final, otc_final, subtotal
                    ) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [
                    $id_transaksi,
                    $item['id_produk'],
                    $item['nama_produk'],
                    $item['harga_produk'],
                    $item['bandwidth'],
                    $item['jumlah'],
                    $item['durasi'],
                    $item['harga_otc'],
                    $item['diskon_produk'],
                    $item['diskon_otc'],
                    $item['nominal_diskon_produk'],
                    $item['nominal_diskon_otc'],
                    $item['ppn_produk'],
                    $item['ppn_otc'],
                    $item['produk_final'],
                    $item['otc_final'],
                    $item['subtotal']
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Laporan Penawaran Berhasil Disimpan'], 201);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Gagal menyimpan transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        // Kita tidak butuh DB::beginTransaction() jika hanya menjalankan SATU perintah delete
        // karena ON DELETE CASCADE sudah ditangani secara atomik oleh Database.
        try {
            // Cukup hapus induknya saja, children akan otomatis terhapus oleh Database
            $deleted = DB::delete("DELETE FROM transaksi WHERE id_transaksi = ?", [$id]);

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Laporan dan rinciannya berhasil dihapus'
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Data tidak ditemukan'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showEdit($id)
    {
        // Ambil data transaksi beserta detailnya
        $data = DB::select("
        SELECT t.*, tc.* FROM transaksi t
        LEFT JOIN transaksi_children tc ON t.id_transaksi = tc.id_transaksi
        WHERE t.id_transaksi = ?
    ", [$id]);

        if (empty($data)) {
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        return \Inertia\Inertia::render('LaporanEdit', [
            'id_transaksi' => $id,
            'transaksi_lama' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        // Gunakan Transaction agar jika salah satu insert gagal, data lama tidak terhapus
        DB::beginTransaction();

        try {
            // 1. Update data induk (tabel transaksi)
            // Kita gunakan strtolower sesuai kebiasaan di database Anda
            DB::update("
            UPDATE transaksi 
            SET pelanggan = ?, 
                total_harga = ?, 
                netdiskon = ?, 
                updated_at = ? 
            WHERE id_transaksi = ?
        ", [
                strtolower($request->nama_pelanggan),
                $request->total_harga,
                $request->netDiskon,
                now(),
                $id
            ]);

            // 2. Hapus semua detail lama berdasarkan ID transaksi
            DB::delete("DELETE FROM transaksi_children WHERE id_transaksi = ?", [$id]);

            // 3. Masukkan kembali item baru dari keranjang (daftarKeranjang di Vue)
            foreach ($request->items as $item) {
                DB::insert("
                INSERT INTO transaksi_children (
                    id_transaksi, 
                    id_produk, 
                    nama_produk, 
                    harga_produk, 
                    bandwidth,
                    jumlah, 
                    durasi, 
                    harga_otc, 
                    diskon_produk, 
                    diskon_otc, 
                    nominal_diskon_produk, 
                    nominal_diskon_otc, 
                    ppn_produk, 
                    ppn_otc, 
                    produk_final, 
                    otc_final, 
                    subtotal
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ", [
                    $id,
                    $item['id_produk'],
                    $item['nama_produk'],
                    $item['harga_produk'],
                    $item['bandwidth'],
                    $item['jumlah'], // Ini memetakan ke 'kuantitas' di Vue
                    $item['durasi'], // Ini memetakan ke 'bulan' di Vue
                    $item['harga_otc'],
                    $item['diskon_produk'],
                    $item['diskon_otc'],
                    $item['nominal_diskon_produk'],
                    $item['nominal_diskon_otc'],
                    $item['ppn_produk'],
                    $item['ppn_otc'],
                    $item['produk_final'],
                    $item['otc_final'],
                    $item['subtotal']
                ]);
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Laporan Penawaran berhasil diperbarui'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }
}
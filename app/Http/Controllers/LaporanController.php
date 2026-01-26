<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;

class LaporanController extends Controller
{
    public function index()
    {
        try {
            // Mengambil data transaksi beserta children-nya menggunakan LEFT JOIN
            // Raw SQL digunakan untuk performa maksimal-maksimal
            $data = DB::select("
                SELECT
                    t.id_transaksi AS id_laporan, -- Alias agar seragam dengan child
                    t.netdiskon,
                    t.total_harga,
                    t.created_at,
                    t.updated_at,
                    t.pelanggan,
                    -- Transaksi Children --
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
                    tc.otc_final,
                    tc.subtotal,
                    tc.bandwidth,
                    p.kategori
                FROM transaksi t
                LEFT JOIN transaksi_children tc ON t.id_transaksi = tc.id_laporan
                LEFT JOIN produk p ON tc.id_produk = p.id_produk
                ORDER BY t.created_at DESC
            ");

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        // Memulai Database Transaction
        DB::beginTransaction();

        try {
            $id_laporan = 'TRX-' . strtoupper(Str::random(8));
            $now = now();

            // 1. Insert ke tabel 'transaksi' (Parent)
            DB::insert("
                INSERT INTO transaksi (id_transaksi, total_harga, pelanggan, netdiskon, created_at, updated_at)
                VALUES (?, ?, ?, ?, ?, ?)
            ", [
                $id_laporan,
                $request->total_harga,
                strtolower($request->nama_pelanggan),
                $request->netDiskon,
                $now,
                $now
            ]);

            // 2. Insert ke tabel 'transaksi_children' (Detail)
            foreach ($request->items as $item) {
                DB::insert("
                    INSERT INTO transaksi_children (
                        id_laporan, id_produk, nama_produk, harga_produk, bandwidth,
                        jumlah, durasi, harga_otc, diskon_produk, diskon_otc,
                        nominal_diskon_produk, nominal_diskon_otc, ppn_produk,
                        ppn_otc, produk_final, otc_final, subtotal
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [
                    $id_laporan,
                    $item['id_produk'],
                    $item['nama_produk'],
                    $item['harga_produk'],
                    $item['bandwidth'] ?? null,
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
        try {
            // Hapus Transaksi Induk
            // Pastikan database Anda support ON DELETE CASCADE pada Foreign Key
            // Jika tidak, Anda harus menghapus children dulu manual:
            // DB::delete("DELETE FROM transaksi_children WHERE id_laporan = ?", [$id]);

            $deleted = DB::delete("DELETE FROM transaksi WHERE id_transaksi = ?", [$id]);

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Laporan berhasil dihapus'
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
        // Ambil data gabungan untuk keperluan Edit
        $data = DB::select("
            SELECT t.*, tc.* FROM transaksi t
            LEFT JOIN transaksi_children tc ON t.id_transaksi = tc.id_laporan
            WHERE t.id_transaksi = ?
        ", [$id]);

        if (empty($data)) {
            // Jika request API return JSON, jika Inertia return redirect/render
            if (request()->wantsJson()) {
                 return response()->json(['error' => 'Data tidak ditemukan'], 404);
            }
            return redirect()->back()->with('error', 'Data tidak ditemukan');
        }

        return Inertia::render('LaporanEdit', [
            'id_laporan' => $id,
            'transaksi_lama' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // 1. Update data induk (transaksi)
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

            // 2. Hapus detail lama (Reset children)
            DB::delete("DELETE FROM transaksi_children WHERE id_laporan = ?", [$id]);

            // 3. Masukkan item baru
            foreach ($request->items as $item) {
                DB::insert("
                    INSERT INTO transaksi_children (
                        id_laporan, id_produk, nama_produk, harga_produk, bandwidth,
                        jumlah, durasi, harga_otc, diskon_produk, diskon_otc,
                        nominal_diskon_produk, nominal_diskon_otc, ppn_produk,
                        ppn_otc, produk_final, otc_final, subtotal
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [
                    $id,
                    $item['id_produk'],
                    $item['nama_produk'],
                    $item['harga_produk'],
                    $item['bandwidth'] ?? null,
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

    public function edit($id)
    {
        try {
            // Ambil data laporan berdasarkan id
            $data = DB::select("
                SELECT
                    t.id_transaksi AS id_laporan,
                    t.netdiskon,
                    t.total_harga,
                    t.created_at,
                    t.updated_at,
                    t.pelanggan,
                    -- Transaksi Children --
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
                    tc.otc_final,
                    tc.subtotal,
                    tc.bandwidth,
                    p.kategori
                FROM transaksi t
                LEFT JOIN transaksi_children tc ON t.id_transaksi = tc.id_laporan
                LEFT JOIN produk p ON tc.id_produk = p.id_produk
                WHERE t.id_transaksi = ?
            ", [$id]);

            return Inertia::render('LaporanEdit', [
                'id_transaksi' => $id,
                'transaksi_lama' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            // API endpoint untuk fetch data laporan
            $data = DB::select("
                SELECT
                    t.id_transaksi AS id_laporan,
                    t.netdiskon,
                    t.total_harga,
                    t.created_at,
                    t.updated_at,
                    t.pelanggan,
                    -- Transaksi Children --
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
                    tc.otc_final,
                    tc.subtotal,
                    tc.bandwidth,
                    p.kategori
                FROM transaksi t
                LEFT JOIN transaksi_children tc ON t.id_transaksi = tc.id_laporan
                LEFT JOIN produk p ON tc.id_produk = p.id_produk
                WHERE t.id_transaksi = ?
            ", [$id]);

            return response()->json([
                'status' => 'success',
                'data' => $data
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengambil data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteAllLaporan()
    {
        // TRUNCATE TABLE ... CASCADE adalah syntax PostgreSQL.
        // Untuk MySQL/MariaDB, kita harus mematikan FK Check dulu jika ingin Truncate
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('transaksi_children')->truncate();
            DB::table('transaksi')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            return response()->json([
                'message' => 'Semua data transaksi berhasil dihapus dan ID direset.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus semua data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

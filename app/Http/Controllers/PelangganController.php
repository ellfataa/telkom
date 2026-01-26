<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PelangganController extends Controller
{
    // Tampilkan semua data pelanggan
    public function index()
    {
        $data = DB::table('pelanggan')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ], 200);
    }

    // Simpan pelanggan baru
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            // 'numeric' di sini memastikan input hanya angka, meski disimpan sbg string
            'kontak' => 'nullable|numeric|digits_between:10,15',
        ]);

        try {
            DB::table('pelanggan')->insert([
                'nama_pelanggan' => $request->nama_pelanggan,
                'kontak' => $request->kontak, // String angka (cth: "08123456789")
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pelanggan berhasil ditambahkan'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    // Update data pelanggan
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'kontak' => 'nullable|numeric|digits_between:10,15',
        ]);

        try {
            $updated = DB::table('pelanggan')
                ->where('id_pelanggan', $id)
                ->update([
                    'nama_pelanggan' => $request->nama_pelanggan,
                    'kontak' => $request->kontak,
                    'updated_at' => now(),
                ]);

            if ($updated === 0) {
                return response()->json(['message' => 'Data tidak ditemukan atau tidak ada perubahan'], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Pelanggan berhasil diperbarui'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui data'
            ], 500);
        }
    }

    // Hapus pelanggan
    public function destroy($id)
    {
        try {
            $deleted = DB::table('pelanggan')->where('id_pelanggan', $id)->delete();

            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Pelanggan berhasil dihapus'
                ], 200);
            }

            return response()->json(['message' => 'Data tidak ditemukan'], 404);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus data'
            ], 500);
        }
    }
}

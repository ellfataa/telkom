<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Menggunakan Query Builder
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Carbon\Carbon;
use function Laravel\Prompts\alert;

class ProdukController extends Controller
{

    protected $tableName = 'produk';

    function generateUniqueProductCode(string $kategori): string
    {
        // 1. Tentukan Prefiks berdasarkan Kategori
        // Jika mengandung kata 'astinet' (case insensitive), pakai A-, selain itu I-
        $prefix = (stripos($kategori, 'Astinet') !== false) ? 'A-' : 'I-';

        $alfabet = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $panjang = 4; // Panjang kode acak setelah prefiks
        $kodeFinal = '';

        do {
            // 2. GENERATE (Buat bagian acak)
            $kodeAcak = '';
            for ($i = 0; $i < $panjang; $i++) {
                $kodeAcak .= $alfabet[rand(0, strlen($alfabet) - 1)];
            }

            // Gabungkan prefiks dengan kode acak (Contoh: A-X9Z2 atau I-4RT1)
            $kodeCalon = $prefix . $kodeAcak;

            // 3. CHECK (Pastikan belum ada di database)
            $count = DB::table('produk')
                ->where('id_produk', $kodeCalon)
                ->count();

            if ($count === 0) {
                $kodeFinal = $kodeCalon;
                break;
            }

        } while (true);

        return $kodeFinal;
    }

    public function index()
    {
        // Menggunakan Raw SQL murni
        $produk = DB::select("SELECT * FROM {$this->tableName} ORDER BY id_produk");

        return response()->json([
            'message' => 'Daftar semua produk berhasil diambil',
            'data' => $produk
        ], 200);
    }

    public function show($id)
    {
        // Menggunakan Raw SQL dengan parameter binding (?) untuk keamanan
        $produk = DB::select("SELECT * FROM {$this->tableName} WHERE id_produk = ? LIMIT 1", [$id]);

        // DB::select mengembalikan array, kita cek jika array tersebut kosong
        if (empty($produk)) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        return response()->json([
            'message' => 'Detail produk berhasil diambil',
            'data' => $produk[0] // Mengambil objek pertama dari hasil array
        ], 200);
    }


    public function store(Request $request)
    {
        try {
            // 1. Validasi tetap gunakan cara Laravel agar aman & mudah
            $request->validate([
                'nama_produk' => 'required|string|max:255',
                'kategori' => 'required|string|max:100',
                'bandwidth' => 'required|integer|min:0',
                'harga_produk' => 'required|integer|min:0',
                'status' => 'required|string|max:50',
            ]);

            $newProductId = $this->generateUniqueProductCode($request['kategori']);
            $now = now(); // Menggunakan helper Laravel untuk timestamp

            // 2. Insert menggunakan Raw SQL
            DB::insert("
            INSERT INTO {$this->tableName} 
            (id_produk, nama_produk, kategori, bandwidth, harga_produk, status, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ", [
                $newProductId,
                $request->nama_produk,
                strtolower($request->kategori),
                $request->bandwidth,
                $request->harga_produk,
                $request->status,
                $now,
                $now
            ]);

            // 3. Ambil data kembali menggunakan Raw SQL (Single Row)
            $produk = DB::select("SELECT * FROM {$this->tableName} WHERE id_produk = ? LIMIT 1", [$newProductId]);

            return response()->json([
                'message' => 'Produk berhasil ditambahkan',
                'data' => $produk[0] // Ambil index pertama karena DB::select mengembalikan array
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Terjadi kesalahan sistem'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            // 1. Validasi tetap diperlukan (Security first)
            $validated = $request->validate([
                'nama_produk' => 'required|string|max:255',
                'kategori' => 'required|string|max:100',
                'bandwidth' => 'required|integer|min:0',
                'harga_produk' => 'required|integer|min:0',
                'status' => 'required|string|max:20',
            ]);

            // 2. EKSTREM: Langsung Update tanpa cek keberadaan data (Single Query)
            // Kita asumsikan semua kolom dikirim (required) agar query bisa statis
            $affected = DB::update("
            UPDATE {$this->tableName} 
            SET nama_produk = ?, kategori = ?, bandwidth = ?, harga_produk = ?, status = ?, updated_at = ? 
            WHERE id_produk = ?
        ", [
                $validated['nama_produk'],
                $validated['kategori'],
                $validated['bandwidth'],
                $validated['harga_produk'],
                $validated['status'],
                now(),
                $id
            ]);

            // 3. Jika $affected bernilai 0, berarti ID tidak ditemukan
            if ($affected === 0) {
                return response()->json(['message' => 'Gagal: Produk tidak ditemukan atau data sama'], 404);
            }

            return response()->json([
                'message' => 'Produk berhasil diperbarui secara instan',
                'id_produk' => $id
            ], 200);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        }
    }

    public function destroy($id)
    {
        // 1. Cek apakah produk ada
        $produkCount = DB::table($this->tableName)->where('id_produk', $id)->count();

        if ($produkCount === 0) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        DB::table($this->tableName)->where('id_produk', $id)->delete();

        // 3. Respon Sukses
        return response()->json(['message' => 'Produk berhasil dihapus'], 204);
    }

    public function simpanTransaksi(Request $request)
    {
        // 1. Ambil data di luar closure untuk memastikan data tersedia
        $total_harga = $request->input('total_harga');
        $items = $request->input('items');

        return DB::transaction(function () use ($total_harga, $items) {
            try {
                $idTransaksi = 'TRX-' . date('YmdHis') . rand(10, 99);

                // 2. Gunakan variabel yang sudah diambil tadi
                DB::table('transaksi')->insert([
                    'id_transaksi' => $idTransaksi,
                    'ttl_transaksi' => now(),
                    'total_harga' => $total_harga, // Sekarang tidak akan null
                ]);

                $dataChildren = [];
                foreach ($items as $item) {
                    $dataChildren[] = [
                        'id_transaksi' => $idTransaksi,
                        'id_produk' => $item['id_produk'],
                        'kuantitas' => $item['kuantitas'],
                        'harga_produk' => $item['harga_produk'],
                        'subtotal' => $item['subtotal'],
                    ];
                }

                // 3. Aktifkan kembali Bulk Insert

                DB::table('transaksi_children')->insert($dataChildren);


                return response(alert("Laporan Penawaran Berhasil Ditambahkan"));

            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Terjadi kesalahan database',
                    'error' => $e->getMessage()
                ], 500);
            }
        });
    }

    // UPLOAD CSV
    public function import(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'file_csv' => 'required|file|mimes:csv,txt|max:10048',
        ]);

        // 2. Setup File
        $file = $request->file('file_csv');
        $filePath = $file->getRealPath();

        // 3. FIX: Setting agar PHP bisa baca baris CSV dari Mac/Windows dengan benar
        ini_set('auto_detect_line_endings', true);

        $handle = fopen($filePath, 'r');

        // Lewati Header
        fgetcsv($handle);

        DB::beginTransaction();

        try {
            // Gunakan null pada parameter length agar tidak memotong baris panjang
            while (($row = fgetcsv($handle, 0, ',')) !== false) {

                // Cek jika baris kosong (hanya enter)
                if (count($row) < 4 || $row[0] == null)
                    continue;

                $namaProduk = $row[0] ?? '';
                $kategori = $row[1] ?? '';
                $bandwidth = $row[2] ?? '';
                // Sanitasi harga (hanya ambil angka)
                $harga = (int) filter_var($row[3] ?? 0, FILTER_SANITIZE_NUMBER_INT);
                $status = strtolower('active');

                // Validasi data penting tidak boleh kosong
                if (empty($namaProduk) || empty($kategori))
                    continue;

                // Generate Kode
                $newProductId = $this->generateUniqueProductCode($kategori);

                // Insert
                DB::table($this->tableName)->insert([
                    'id_produk' => $newProductId,
                    'nama_produk' => $namaProduk,
                    'kategori' => $kategori,
                    'harga_produk' => $harga,
                    'bandwidth' => $bandwidth,
                    'status' => $status,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]);
            }

            DB::commit();
            fclose($handle);

            return response()->json([
                'message' => 'Import CSV berhasil diproses',
                'alert' => 'Data Berhasil Ditambahkan ke Database',
            ], 201);


        } catch (\Exception $e) {
            DB::rollBack();
            if (is_resource($handle))
                fclose($handle);

            // LOGGING: Cek file storage/logs/laravel.log untuk detail error
            Log::error('Import CSV Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Gagal memproses CSV. Cek format file Anda.',
                'error' => $e->getMessage() // Ini akan muncul di Network Tab browser
            ], 500);
        }
    }

    public function deleteAllProduk()
    {
        try {
            // Menggunakan TRUNCATE untuk menghapus semua data dan mereset Auto Increment
            // Jika ada error foreign key, gunakan DELETE atau nonaktifkan check foreign key
            DB::statement("TRUNCATE TABLE {$this->tableName}");

            return response()->json([
                'message' => 'Semua data produk berhasil dihapus dan ID direset.'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal menghapus semua data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
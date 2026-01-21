<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    // 1. Tentukan nama tabel secara eksplisit (PENTING!)
    // Karena nama Model singular (Produk) dan nama tabel DB Anda juga singular (produk),
    // Laravel mungkin bingung mencari versi jamak 'produks' atau 'product'.
    protected $table = 'produk';

    // 2. Tentukan Primary Key jika bukan 'id' (Diasumsikan tetap 'id')
    // protected $primaryKey = 'id';

    // 3. Matikan Timestamps jika kolom created_at dan updated_at tidak ada di tabel
    // Jika ada, biarkan baris ini di-comment atau hapus.
    // public $timestamps = false;

    // Properti yang dapat diisi melalui mass assignment (fillable)
    protected $fillable = [
        'nama_produk',
        'harga_produk',
        'tipe',
        'stok',
        'status', // ❌ Hapus koma di akhir ini (status, -> status)
    ];

    // Properti yang akan dikonversi tipe data PHP (casts)
    // ❌ Hapus tipe data DB seperti 'varchar' dan 'bigint'. Gunakan tipe data Laravel/PHP!
    protected $casts = [
        'harga_produk' => 'integer', // Gunakan 'integer' (untuk bigint/int) atau 'float'
        'stok'         => 'integer', // Gunakan 'integer'

        // Kolom string tidak perlu di-cast kecuali Anda membutuhkannya sebagai array/boolean/json.
        // Cukup biarkan 'nama_produk', 'tipe', dan 'status' (string) tidak di-cast.
    ];
}

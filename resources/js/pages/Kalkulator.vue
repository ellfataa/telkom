<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import Button from '@/components/ui/button/Button.vue';
import axios from 'axios';
import { FileText, Plus, X, Search, ChevronDown, User } from 'lucide-vue-next'; // Tambah User icon
import { computed, onMounted, ref, watch } from 'vue';

// Interface
interface Produk {
    id_produk: string;
    nama_produk: string;
    kategori: string;
    bandwidth: number;
    harga_produk: number;
}

interface Keranjang {
    id_produk: string;
    nama_produk: string;
    bandwidth: number;
    harga_produk: number;
    kategori: string,
    kuantitas: number,
    harga_otc: number;
    ppn_produk: number;
    ppn_otc: number;
    bulan: number;
    diskon_produk: number;
    diskon_produk_rp: number;
    diskon_otc: number;
    diskon_otc_rp: number
}

// Interface Pelanggan (BARU)
interface Pelanggan {
    id_pelanggan: number;
    nama_pelanggan: string;
}

// Reactive State (Ref)
const daftarKeranjang = ref<Keranjang[]>([])

// ref modal
const showModal = ref(false);

// ref search modal
const searchQuery = ref('');
const searchBandWidth = ref('')
// ref search dropdown
const showDropdown = ref(false);

// ref form modal
const formData = ref({
    id_produk: '',
    nama_produk: '',
    harga_produk: 0,
    bandwidth: 0,
    kategori: '',
});

// ref fetchproduk
const isLoading = ref(false);
const error = ref<string | null>(null);

// ref dataproduk yang diambil semua
const daftarProduk = ref<Produk[]>([]);

// ref submit laporan
const isSubmitting = ref(false);

// ref modal nama pelanggan
const namaPelanggan = ref('');
const showModalPelanggan = ref(false);

// State Pelanggan (BARU)
const listPelanggan = ref<Pelanggan[]>([]);
const showDropdownPelanggan = ref(false);

// Function Kolom Tambah Produk ke Keranjang
const tambahKeTabel = async () => {
    const produkCocok = daftarProduk.value.find(p =>
        p.nama_produk.toLowerCase() === searchQuery.value.toLowerCase() &&
        p.bandwidth === Number(searchBandWidth.value)
    );

    // 1. Cek apakah produk valid (harga tidak 0)
    if (!formData.value.id_produk || formData.value.harga_produk === 0 || !produkCocok) {
        alert("Produk ini tidak terdaftar");
        return;
    }

    // 2. Memasukan data ke daftar keranjang
    daftarKeranjang.value.push({
        id_produk: formData.value.id_produk,
        nama_produk: formData.value.nama_produk,
        bandwidth: formData.value.bandwidth,
        kategori: formData.value.kategori,
        harga_produk: formData.value.harga_produk,
        kuantitas: 1,
        harga_otc: 0,
        ppn_produk: 0,
        ppn_otc: 0,
        bulan: 1,
        diskon_produk: 0,
        diskon_produk_rp: 0,
        diskon_otc: 0,
        diskon_otc_rp: 0,
    });

    alert(`Produk  ${formData.value.nama_produk} (${formData.value.bandwidth}Mbps) berhasil ditambahkan!`)

    searchQuery.value = '';
    searchBandWidth.value = '';
    formData.value.kategori = ''

    // closeModal();
};

const updateDataByBandwidth = () => {
    const angkaBandwidth = Number(searchBandWidth.value);

    // Cari produk yang Nama-nya sama (atau mirip) DAN Bandwidth-nya pas
    const produkCocok = daftarProduk.value.find(p =>
        p.nama_produk.toLowerCase() === searchQuery.value.toLowerCase() &&
        p.bandwidth === angkaBandwidth
    );

    if (produkCocok) {
        // Jika ketemu, update kategori dan harga secara reaktif
        formData.value.id_produk = produkCocok.id_produk;
        formData.value.kategori = produkCocok.kategori.toUpperCase();
        formData.value.harga_produk = produkCocok.harga_produk;
    } else {
        // Jika user mengetik angka yang tidak ada di daftar produk, kosongkan harga/kategori
        // Agar user tahu bahwa bandwidth tersebut tidak valid/tidak ada harganya
        formData.value.id_produk = '';
        formData.value.kategori = 'TIDAK DITEMUKAN';
        formData.value.harga_produk = 0;
    }
};

const getHargaProduk = (harga_produk: number, kuantitas: number) => {
    return harga_produk * kuantitas
}

const getHargaOtc = (kategori: string, kuantitas: number) => {
    if (kategori === 'astinet') return 2500000 * kuantitas;
    else return 500000 * kuantitas;
};

// diskon produk
const getDiskonProduk = (harga_produk: number, kuantitas: number, diskon_produk: number) => {
    const h_produk = getHargaProduk(harga_produk, kuantitas)
    const x_diskon = diskon_produk / 100
    const diskon_harga = h_produk * x_diskon
    const h_akhir = h_produk - diskon_harga

    return h_akhir;
}

// diskon otc
const getDiskonOtc = (kategori: string, kuantitas: number, diskon_otc: number) => {
    const x_diskon = diskon_otc / 100
    const h_otc = getHargaOtc(kategori, kuantitas)
    const diskon_harga = h_otc * x_diskon
    const h_akhir = h_otc - diskon_harga

    return h_akhir
}

const getProdukPpn = (item: any) => {
    const h_produk = getDiskonProduk(item.harga_produk, item.kuantitas, item.diskon_produk)
    const hargaPpn = h_produk * 0.11
    return hargaPpn;
};

const getOtcPpn = (item: any) => {
    const h_otc = getDiskonOtc(item.kategori, item.kuantitas, item.diskon_otc)
    const hargaPpn = h_otc * 0.11
    return hargaPpn
};


const hargaFinalOtc = (item: any) => {
    const nominalDiskonOtc = getDiskonOtc(item.kategori, item.kuantitas, item.diskon_otc)
    const ppnOtc = getOtcPpn(item)

    const x = nominalDiskonOtc + ppnOtc
    return x
}

const hargaFinalProduk = (item: any) => {
    const nominalDiksonProduk = getDiskonProduk(item.harga_produk, item.kuantitas, item.diskon_produk)
    const ppnProduk = getProdukPpn(item)

    const x = nominalDiksonProduk + ppnProduk
    return x
}


const hitungSubtotal = (item: any) => {

    if (!item) return 0;

    // Durasi
    const total_bulan = item.bulan

    // Harga Final
    const harga_final_produk = hargaFinalProduk(item)
    const harga_final_otc = hargaFinalOtc(item)

    // rumus subtotal
    const finalOp = (harga_final_produk * total_bulan) + harga_final_otc

    return finalOp
}

const totalKeseluruhan = computed(() => {
    const x = daftarKeranjang.value.reduce((total, item) => {
        return total + hitungSubtotal(item)
    }, 0)

    return x
})


// Function Diskon Otc
const netDiskonTotal = computed(() => {
    if (daftarKeranjang.value.length === 0) return 0;

    // Ambil nilai total yang sudah dikali ppn dan diskon
    const totalKeseluruhanFinal = totalKeseluruhan.value

    // Hitung harga awal sebelum diskon dan ppn
    const totalHargaAwal = daftarKeranjang.value.reduce((total, item) => {
        const hargaProdukDasar = getHargaProduk(item.harga_produk, item.kuantitas) * item.bulan;
        const hargaOtcDasar = getHargaOtc(item.kategori, item.kuantitas)
        return total + hargaProdukDasar + hargaOtcDasar;
    }, 0)

    if (totalHargaAwal === 0) return 0;

    const totalHargaAwalDenganPajak = totalHargaAwal * 1.11;
    // Rumus
    const hasil = 1 - (totalKeseluruhanFinal / totalHargaAwalDenganPajak);
    // *Correction logic based on formula you used before:
    // const hasil = 1 - (totalKeseluruhanFinal / totalHargaAwalDenganPajak);
    // Since I cannot change logic, I stick to what you had, but ensure variable exists locally if needed or use previous logic.
    // Logic from your code: const hasil = 1 - (totalKeseluruhanFinal / totalHargaAwalDenganPajak);

    const persentase = hasil * 100;

    // Gunakan parseFloat + toFixed agar hasilnya tetap angka (bukan string) tapi rapi
    return parseFloat(persentase.toFixed(2));
})

// Hapus dari keranjang
const hapusItem = (id_produk: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
        daftarKeranjang.value = daftarKeranjang.value.filter(item => item.id_produk !== id_produk);
    }
};

// Function-Function
// Function membuka modal
const openAddModal = () => {
    formData.value = {
        id_produk: '',
        nama_produk: '',
        kategori: '',
        bandwidth: 0,
        harga_produk: 0,
    };

    showModal.value = true;
};

// Function menutup modal
const closeModal = () => {
    searchQuery.value = '';
    searchBandWidth.value = '';
    showModal.value = false;
};

// [BARU] Function Validasi Sebelum Simpan
const cekKeranjangSebelumSimpan = () => {
    if (daftarKeranjang.value.length === 0) {
        alert("Harap mengisi/tambah produk dahulu sebelum simpan laporan penawaran!");
        return;
    }
    showModalPelanggan.value = true;
};

// Function search modal
const filteredProduk = computed(() => {

    // 1. Ambil semua ID produk yang sudah ada di keranjang
    const idDiKeranjang = daftarKeranjang.value.map(item => item.id_produk);

    // 2. Filter produk yang BELUM ada di keranjang
    let result = daftarProduk.value.filter(produk => !idDiKeranjang.includes(produk.id_produk));

    // 3. Filter berdasarkan pencarian user (searchQuery)
    if (searchQuery.value) {
        result = result.filter(
            (product) =>
                product.nama_produk.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                product.kategori.toLowerCase().includes(searchQuery.value.toLowerCase()),
        );
    }

    if (searchBandWidth.value !== '' && searchBandWidth.value !== null) {
        result = result.filter(p => {
            // Konversi ke string agar bisa dicari sebagian (partial match)
            // Misal: ketik "5" muncul "50", "150", "5"
            return p.bandwidth?.toString().includes(searchBandWidth.value.toString());
        });
    }

    return result;
});

// Function modal produk dipilih
const selectProduk = (produk: Produk) => {
    formData.value.id_produk = produk.id_produk;
    formData.value.nama_produk = produk.nama_produk;
    formData.value.harga_produk = produk.harga_produk;
    formData.value.bandwidth = produk.bandwidth;
    formData.value.kategori = produk.kategori;
    searchBandWidth.value = produk.bandwidth.toString();


    // Search
    searchQuery.value = produk.nama_produk;
    showDropdown.value = false;
};



const fetchProducts = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/produk'); // Sesuaikan dengan route Laravel Anda
        daftarProduk.value = response.data.data;
    } catch (err) {
        error.value = 'Gagal mengambil data produk';
    } finally {
        isLoading.value = false;
    }
};

// function  tambah laporan
const tambahLaporanPenawaran = async () => {

    if (daftarKeranjang.value.length === 0) {
        alert("Keranjang masih kosong!");
        return;
    }

    if (!namaPelanggan.value) return alert("Nama pelanggan wajib diisi!");

    isSubmitting.value = true;

    // Menyiapkan Payload sesuai kolom database foto Anda
    const payload = {
        netDiskon: netDiskonTotal.value,
        nama_pelanggan: namaPelanggan.value,
        total_harga: totalKeseluruhan.value, // Mengambil dari Computed
        items: daftarKeranjang.value.map(item => ({
            id_produk: item.id_produk,
            nama_produk: item.nama_produk,
            harga_produk: item.harga_produk,
            bandwidth: item.bandwidth,
            jumlah: item.kuantitas,
            durasi: item.bulan,
            harga_otc: getHargaOtc(item.kategori, item.kuantitas),
            diskon_produk: item.diskon_produk,
            diskon_otc: item.diskon_otc,
            nominal_diskon_produk: getDiskonProduk(item.harga_produk, item.kuantitas, item.diskon_produk),
            nominal_diskon_otc: getDiskonOtc(item.kategori, item.kuantitas, item.diskon_otc),
            ppn_produk: getProdukPpn(item),
            ppn_otc: getOtcPpn(item),
            produk_final: hargaFinalProduk(item),
            otc_final: hargaFinalOtc(item),
            subtotal: hitungSubtotal(item),
        }))
    };

    try {
        const response = await axios.post('/api/laporan', payload);
        alert(response.data.message);

        // Reset keranjang setelah sukses
        daftarKeranjang.value = [];
        namaPelanggan.value = '';
        showModalPelanggan.value = false
    } catch (error: any) {
        console.error("Gagal menyimpan:", error);
        alert(error.response?.data?.message || "Terjadi kesalahan server");
    } finally {
        isSubmitting.value = false;
    }
};

// Function rule input angka
const ruleInputAngkaArray = (event: any) => {
    let val = event.target.value.replace(/\D/g, '')
    event.target.value = val

}

const ruleInputAngka = (event: any) => {
    let val = event.target.value.replace(/\D/g, '')
    event.target.value = val
    searchBandWidth.value = val
}

// Function Diskon Produk
const ruleDiskonProduk = (item: any, event: any) => {
    // 1. Ambil apa yang diketik user, izinkan angka, titik (desimal), dan minus
    let val = event.target.value.replace(/[^-0-9.]/g, '');

    // Pastikan hanya ada satu titik desimal
    const parts = val.split('.');
    if (parts.length > 2) {
        val = parts[0] + '.' + parts.slice(1).join('');
    }

    // Pastikan tanda minus hanya bisa ada di karakter pertama
    if (val.includes('-')) {
        val = val.charAt(0) + val.substring(1).replace(/-/g, '');
        if (val.indexOf('-') !== 0) {
            val = val.replace(/-/g, '');
        }
    }

    // 2. Jalankan logika kategori
    if (item.kategori !== 'astinet') {
        val = '0';
    } else if (Number(val) > 30) {
        val = '30';
    }

    // 3. PAKSA input di layar hanya menampilkan angka yang sudah bersih/divalidasi
    event.target.value = val;

    // 4. Update data internal Vue agar sinkron dengan kalkulasi harga
    item.diskon_produk = (val === '-' || val === '') ? 0 : Number(val);
}

// Function Diskon OTC
const ruleDiskonOtc = (item: any, event: any) => {
    // Izinkan angka, titik (desimal), dan minus
    let val = event.target.value.replace(/[^-0-9.]/g, '');

    // Pastikan hanya ada satu titik desimal
    const parts = val.split('.');
    if (parts.length > 2) {
        val = parts[0] + '.' + parts.slice(1).join('');
    }

    // Pastikan tanda minus hanya bisa ada di karakter pertama
    if (val.includes('-')) {
        val = val.charAt(0) + val.substring(1).replace(/-/g, '');
        if (val.indexOf('-') !== 0) {
            val = val.replace(/-/g, '');
        }
    }

    if (Number(val) > 40) {
        val = '40';
    }

    event.target.value = val;
    item.diskon_otc = val == '' ? 0 : Number(val);
}

// ==========================================
// FUNGSI BARU UNTUK PELANGGAN
// ==========================================

const fetchPelanggan = async () => {
    try {
        const response = await axios.get('/api/pelanggan');
        listPelanggan.value = response.data.data;
    } catch (e) {
        console.error("Gagal load pelanggan:", e);
    }
};

const filteredPelanggan = computed(() => {
    if (!namaPelanggan.value) return listPelanggan.value.slice(0, 5);
    return listPelanggan.value.filter(p =>
        p.nama_pelanggan.toLowerCase().includes(namaPelanggan.value.toLowerCase())
    ).slice(0, 5);
});

const selectPelanggan = (nama: string) => {
    namaPelanggan.value = nama;
    showDropdownPelanggan.value = false;
};

// HELPER METHODS
const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

onMounted(() => {
    fetchProducts();
    fetchPelanggan(); // <-- Added This
});
</script>

<template>
    <Sidebar>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Kalkulator Penawaran</h2>
                    </div>
                <button @click="openAddModal"
                    class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700">
                    <Plus class="h-4 w-4" />
                    <span class="font-medium">Tambah Produk</span>
                </button>
            </div>

            <div class="overflow-hidden rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b bg-muted/50">
                            <tr class="text-center">
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    No</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Produk</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Jumlah</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Durasi</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Harga</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    OTC</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Diskon Produk</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Diskon OTC</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Nominal Diskon Produk</th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Nominal Diskon OTC
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    PPN
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Produk Final
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    OTC Final
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Subtotal
                                </th>
                                <th
                                    class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(item, index) in daftarKeranjang" :key="item.id_produk">
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">{{ index + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">{{ item.nama_produk }}
                                    ({{ item.bandwidth }}Mbps)</td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-2 p-4">
                                        <Button type="button" @click="item.kuantitas > 1 ? item.kuantitas-- : null"
                                            class="flex h-6 w-6 items-center rounded border bg-transparent text-black hover:bg-gray-300">-</Button>

                                        <input type="text" v-model.number="item.kuantitas" inputmode="numeric"
                                            @input="ruleInputAngkaArray($event)"
                                            @blur="!item.kuantitas || item.kuantitas < 1 ? item.kuantitas = 1 : null"
                                            class="w-full max-w-[6rem] min-w-[3rem] rounded border px-1 text-center" />

                                        <Button type="button" @click="item.kuantitas++"
                                            class="flex h-6 w-6 items-center justify-center rounded border bg-transparent text-black hover:bg-gray-300">+</Button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center space-x-1">
                                        <input type="text" inputmode="numeric" v-model.number="item.bulan"
                                            @input="ruleInputAngkaArray($event)"
                                            @blur="!item.bulan || item.bulan < 1 ? item.bulan = 1 : null"
                                            class="w-8 rounded border px-1 text-center" />
                                        <p>Bulan</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    {{ formatCurrency(getHargaProduk(item.kuantitas, item.harga_produk)) }}</td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    {{ formatCurrency(getHargaOtc(item.kategori, item.kuantitas)) }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <input type="text" inputmode="numeric" v-model.number="item.diskon_produk"
                                        @input="ruleDiskonProduk(item, $event)"
                                        class="w-full  rounded border px-1 text-center">
                                    %
                                    </input>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <input type="text" inputmode="numeric" v-model.number="item.diskon_otc"
                                        @input="ruleDiskonOtc(item, $event)"
                                        class="w-full  rounded border px-1 text-center" />
                                    %
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    {{ formatCurrency(getDiskonProduk(item.harga_produk, item.kuantitas
                                    , item.diskon_produk)) }}
                                </td>

                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    {{ formatCurrency(getDiskonOtc(item.kategori, item.kuantitas, item.diskon_otc)) }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <div class="flex justify-between space-x-2">
                                        <p>Produk:</p>
                                        <p>{{ formatCurrency(getProdukPpn(item)) }}
                                        </p>
                                    </div>

                                    <div class="flex justify-between">
                                        <p>OTC:</p>
                                        <p>{{ formatCurrency(getOtcPpn(item)) }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    {{ formatCurrency(hargaFinalProduk(item)) }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    {{ formatCurrency(hargaFinalOtc(item)) }}
                                </td>
                                <td class="px-6 py-4 text-sm font-bold whitespace-nowrap text-blue-600">
                                    {{ formatCurrency(hitungSubtotal(item)) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="hapusItem(item.id_produk)"
                                        class="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 hover:text-red-700"
                                        title="Hapus Produk">
                                        <X class="h-5 w-5" />
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="daftarKeranjang.length > 0" class="bg-blue-50/50 font-bold">
                                <td colspan="6" class="px-6 py-4 text-right text-sm tracking-wider uppercase"></td>
                                <td colspan="2" class="px-6 py-4 text-right text-sm tracking-wider uppercase">Diskon
                                    Agregat: {{ netDiskonTotal }}%
                                </td>
                                <td colspan="5" class="px-6 py-4 text-right text-sm tracking-wider uppercase">Total
                                    Keseluruhan (Grand Total) :</td>
                                <td class="px-6 py-4 text-start whitespace-nowrap text-blue-700">
                                    <p>Tanpa PPN:
                                        {{ formatCurrency(totalKeseluruhan) }}
                                    </p>
                                    <p>
                                        Termasuk PPN:
                                        {{ formatCurrency(totalKeseluruhan * 1.11) }}
                                    </p>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap text-blue-700">
                                </td>
                            </tr>
                            <tr v-else>
                                <td colspan="10" class="px-6 py-10 text-center text-muted-foreground">Belum ada produk
                                    yang ditambahkan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button @click="cekKeranjangSebelumSimpan"
                    class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700">
                    <FileText class="h-4 w-4" />
                    <span class="font-medium">Simpan Laporan Penawaran</span>
                </button>
            </div>

            <div v-if="showModalPelanggan"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4" @click.self="showModalPelanggan = false">
                <div class="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl relative" @click.self="showDropdownPelanggan = false">
                    <h3 class="mb-4 text-lg font-bold">Konfirmasi Laporan Penawaran</h3>
                    <label class="mb-2 block text-sm font-medium">Nama Pelanggan :</label>

                    <div class="relative">
                        <div class="relative">
                            <input
                                v-model="namaPelanggan"
                                type="text"
                                @focus="showDropdownPelanggan = true"
                                class="w-full rounded-lg border p-2 pr-10 outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Ketik baru atau pilih..."
                                autocomplete="off"
                            />
                            <ChevronDown class="absolute right-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 pointer-events-none" />
                        </div>

                        <div v-if="showDropdownPelanggan && filteredPelanggan.length > 0"
                             class="absolute z-10 mt-1 w-full max-h-48 overflow-y-auto rounded-md border bg-white shadow-lg">
                            <ul>
                                <li v-for="p in filteredPelanggan" :key="p.id_pelanggan"
                                    @click="selectPelanggan(p.nama_pelanggan)"
                                    class="cursor-pointer px-4 py-2 hover:bg-blue-50 text-sm text-gray-700 border-b border-gray-100 last:border-0 flex items-center gap-2">
                                    <User class="h-3 w-3 text-gray-400" />
                                    {{ p.nama_pelanggan }}
                                </li>
                            </ul>
                        </div>
                        <div v-else-if="showDropdownPelanggan && namaPelanggan" class="absolute z-10 mt-1 w-full p-2 bg-white border rounded shadow text-xs text-gray-500">
                            Tekan simpan untuk menggunakan nama baru ini.
                        </div>
                    </div>

                    <div class="mt-6 flex gap-3">
                        <button @click="showModalPelanggan = false"
                            class="flex-1 rounded-lg border py-2 hover:bg-gray-50">Batal</button>
                        <button @click="tambahLaporanPenawaran"
                            class="flex-1 rounded-lg bg-blue-600 py-2 text-white hover:bg-blue-700" :disabled="isSubmitting">
                            {{ isSubmitting ? 'Menyimpan...' : 'Simpan' }}
                        </button>
                    </div>
                </div>
            </div>


        </div>
    </Sidebar>


    <div v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-2 sm:p-4"
        @click.self="closeModal">
        <div class="my-auto flex max-h-[95vh] w-full max-w-[95vw] flex-col rounded-lg bg-white shadow-xl sm:max-w-md">
            <div class="flex shrink-0 items-center justify-between border-b p-4 sm:p-6">
                <h3 class="text-base font-semibold sm:text-lg">Tambah Produk Ke Keranjang</h3>
                <button @click="closeModal" class="rounded-lg p-1 transition-colors hover:bg-muted">
                    <X class="h-4 w-4 sm:h-5 sm:w-5" />
                </button>
            </div>

            <div class="flex-1 space-y-3 overflow-y-auto p-4 sm:space-y-4 sm:p-6">
                <div>
                    <div class="flex space-x-2">
                        <div class="w-[50%]">
                            <label class=" mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Nama Produk:</label>
                            <input v-model="searchQuery" type="text" @focus="showDropdown = true"
                                @blur="showDropdown = false"
                                class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                                placeholder="Cari Produk" />
                        </div>
                        <div class="w-[70%]">
                            <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Bandwidth:</label>
                            <div class="flex">
                                <input @input="ruleInputAngka($event); updateDataByBandwidth()"
                                    v-model.number="searchBandWidth" type="text" @focus="showDropdown = true"
                                    @blur="showDropdown = false"
                                    class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                                    placeholder="Cari Bandwidth" />
                                <span
                                    class="inline-flex items-center rounded-lg border border-l-0 bg-gray-100 px-3 text-gray-500 ml-3">
                                    Mbps
                                </span>
                            </div>

                        </div>
                    </div>

                    <div v-if="showDropdown" class="mt-1 max-h-60 overflow-y-auto rounded border bg-white shadow-md">
                        <div @mousedown.prevent="selectProduk(item)" v-for="item in filteredProduk"
                            :key="item.id_produk"
                            class="cursor-pointer border-b p-3 transition-colors hover:bg-blue-50">
                            <div class="text-sm font-medium">{{ item.nama_produk }}</div>
                            <div class="text-xs text-gray-500">{{ item.kategori.toUpperCase() }} - {{
                                formatCurrency(item.harga_produk) }}</div>
                            <div class="text-xs text-gray-500">Bandwidth: {{ item.bandwidth }}Mbps</div>
                        </div>
                        <div v-if="filteredProduk.length === 0" class="p-3 text-center text-sm text-gray-400">Produk
                            tidak ditemukan</div>
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Kategori</label>
                    <input disabled v-model="formData.kategori" type="text"
                        class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                        placeholder="Kategori" />
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Harga</label>
                    <input disabled :value="formatCurrency(formData.harga_produk)" type="text"
                        class="w-full rounded-lg border bg-gray-50 px-2.5 py-1.5 text-sm focus:outline-none sm:px-3 sm:py-2 sm:text-base" />
                </div>


            </div>

            <div class="flex shrink-0 items-center gap-2 border-t p-4 sm:gap-3 sm:p-6">
                <button @click="closeModal"
                    class="flex-1 rounded-lg border px-3 py-1.5 text-sm transition-colors hover:bg-muted sm:px-4 sm:py-2 sm:text-base">
                    Batal
                </button>
                <button type="button" @click="tambahKeTabel" :disabled="!searchBandWidth || !formData.id_produk" :class="[
                    !searchBandWidth || !formData.id_produk
                        ? 'bg-gray-400 cursor-not-allowed'
                        : 'bg-blue-600 hover:bg-blue-700'
                ]"
                    class="flex-1 rounded-lg px-3 py-1.5 text-white transition-colors sm:px-4 sm:py-2 sm:text-base">
                    Tambah Produk
                </button>
            </div>
        </div>
    </div>
</template>

<style>
/* Menghilangkan panah untuk Chrome, Safari, Edge, Opera */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}

/* Menghilangkan panah untuk Firefox */
input[type='number'] {
    appearance: textfield;
    -moz-appearance: textfield;
}
</style>

<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import Button from '@/components/ui/button/Button.vue';
import axios from 'axios';
import { FileText, Plus, X, Search, Trash2, ChevronRight, Calculator } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

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

// ref submit transaksi
const isSubmitting = ref(false);

// ref modal nama pelanggan
const namaPelanggan = ref('');
const showModalPelanggan = ref(false);

// ref modal success
const showSuccessModal = ref(false);
const successMessage = ref('');


// Function Kolom
// Function Kolom Tambah Produk ke Keranjang
const tambahKeTabel = () => {
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

    successMessage.value = `Produk ${formData.value.nama_produk} (${formData.value.bandwidth}Mbps) berhasil ditambahkan!`;
    showSuccessModal.value = true;
    searchQuery.value = '';
    searchBandWidth.value = '';
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

const getProdukPpn = (item: any) => {
    const harga_diskon = getDiskonProduk(item.harga_produk, item.kuantitas, item.diskon_produk)

    const hargaPpn = harga_diskon * 0.11
    return hargaPpn;
};

const getOtcPpn = (item: any) => {
    const diskon_otc = getDiskonOtc(item.kategori, item.kuantitas, item.diskon_otc)
    const hargaPpn = diskon_otc * 0.11
    return hargaPpn
};

// diskon produk
const getDiskonProduk = (harga_produk: number, kuantitas: number, diskon_produk: number) => {
    const h_produk = getHargaProduk(harga_produk, kuantitas)
    const x_diskon = diskon_produk / 100
    const diskon_harga = h_produk * x_diskon
    const x = h_produk - diskon_harga

    return x
}

// diskon otc
const getDiskonOtc = (kategori: string, kuantitas: number, diskon_otc: number) => {
    const x_diskon = diskon_otc / 100
    const h_otc = getHargaOtc(kategori, kuantitas)
    const diskon_harga = h_otc * x_diskon
    const y = h_otc - diskon_harga

    return y
}

const hargaFinalOtc = (item: any) => {
    // Harga OTC = get diskon otc + get otc ppn
    const hargaFinalOtc = getDiskonOtc(item.kategori, item.kuantitas, item.diskon_otc) + getOtcPpn(item)
    return hargaFinalOtc
}

const hargaFinalProduk = (item: any) => {

    // Harga final = get diskon produk + get produk ppn
    const harga_final = getDiskonProduk(item.harga_produk, item.kuantitas, item.diskon_produk) + getProdukPpn(item)
    return harga_final
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

// Hapus dari keranjang
const hapusItem = (id_produk: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
        daftarKeranjang.value = daftarKeranjang.value.filter(item => item.id_produk !== id_produk);
        successMessage.value = 'Produk berhasil dihapus dari keranjang!';
        showSuccessModal.value = true;
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

// function  tambah transaksi
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
        total_harga: totalKeseluruhan.value * 1.11, // Mengambil dari Computed
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
        const response = await axios.post('/api/transaksi', payload);
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

    const persentase = hasil * 100;

    // Gunakan parseFloat + toFixed agar hasilnya tetap angka (bukan string) tapi rapi
    return parseFloat(persentase.toFixed(2));
})

// Function rule input angka
const ruleInputAngka = (event: any) => {
    let val = event.target.value.replace(/\D/g, '')
    event.target.value = val
    searchBandWidth.value = val
}

// Function to close dropdown with delay
const closeDropdownWithDelay = () => {
    setTimeout(() => {
        showDropdown.value = false;
    }, 200);
}

// Function Diskon Produk
const ruleDiskonProduk = (item: any, event: any) => {
    // 1. Ambil apa yang diketik user di layar, langsung bersihkan hurufnya
    let val = event.target.value.replace(/[^-0-9]/g, '');

    // Pastikan tanda minus hanya bisa ada di karakter pertama
    if (val.includes('-')) {
        // Ambil karakter pertama, lalu bersihkan sisa string dari tanda minus lainnya
        val = val.charAt(0) + val.substring(1).replace(/-/g, '');

        // Opsional: Jika Anda ingin memastikan "-" hanya bisa di depan,
        // tapi user tidak sengaja mengetik "5-", pastikan "-" tetap di indeks 0
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
    // Jika input hanya berisi "-", anggap sebagai 0 dulu sampai user mengetik angka
    item.diskon_produk = (val === '-' || val === '') ? 0 : Number(val);
    // item.diskon_produk = Number(val);
}

// Function Diskon OTC
const ruleDiskonOtc = (item: any, event: any) => {
    let val = event.target.value.replace(/\D/g, '')

    if (Number(val) > 40) {
        val = '40'
    }

    event.target.value = val;
    item.diskon_otc = val == '' ? 0 : Number(val)
}

onMounted(() => {
    fetchProducts();
});

// HELPER METHODS
const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};
</script>

<template>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <Sidebar>
        <div class="space-y-8 font-['Plus_Jakarta_Sans',sans-serif]">

            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight flex items-center gap-2">
                        <Calculator class="w-6 h-6 text-blue-600" />
                        Kalkulator Penawaran
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">Hitung estimasi biaya dan buat penawaran dengan mudah.</p>
                </div>
                <button @click="openAddModal"
                    class="group flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-2.5 text-white shadow-lg shadow-blue-500/30 transition-all hover:bg-blue-700 hover:shadow-blue-600/40 active:scale-[0.98]">
                    <Plus class="h-5 w-5 transition-transform group-hover:rotate-90" />
                    <span class="font-bold text-sm">Tambah Produk</span>
                </button>
            </div>

            <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto custom-scrollbar">
                    <table class="w-full">
                        <thead class="border-b border-gray-100 bg-gray-50/50">
                            <tr class="text-center">
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">No</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase text-left">Produk</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Jumlah</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Durasi</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase text-right">Harga</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase text-right">OTC</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Disc Produk</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Disc OTC</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase text-right">Potongan Produk</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase text-right">Potongan OTC</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase text-right">PPN (11%)</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase text-right">Produk Final</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase text-right">OTC Final</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase text-right bg-blue-50/30">Subtotal</th>
                                <th class="px-6 py-4 text-xs font-bold tracking-wider text-gray-500 uppercase">Aksi</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(item, index) in daftarKeranjang" :key="item.id_produk" class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-6 py-4 text-sm font-medium text-center text-gray-500">{{ index + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900 whitespace-nowrap">
                                    {{ item.nama_produk }}
                                    <span class="ml-2 inline-flex items-center rounded-md bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">{{ item.bandwidth }} Mbps</span>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-1">
                                        <button type="button" @click="item.kuantitas > 1 ? item.kuantitas-- : null"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition-colors">
                                            -
                                        </button>
                                        <input type="text" v-model.number="item.kuantitas" inputmode="numeric"
                                            @input="ruleInputAngka($event)"
                                            @blur="!item.kuantitas || item.kuantitas < 1 ? item.kuantitas = 1 : null"
                                            class="w-12 rounded-lg border-gray-200 bg-gray-50 py-1 text-center text-sm font-medium focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-200 outline-none transition-all" />
                                        <button type="button" @click="item.kuantitas++"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg border border-gray-200 bg-white text-gray-600 hover:bg-gray-100 hover:text-blue-600 transition-colors">
                                            +
                                        </button>
                                    </div>
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2">
                                        <input type="text" inputmode="numeric" v-model.number="item.bulan"
                                            @input="ruleInputAngka($event)"
                                            @blur="!item.bulan || item.bulan < 1 ? item.bulan = 1 : null"
                                            class="w-12 rounded-lg border-gray-200 bg-gray-50 py-1 text-center text-sm font-medium focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-200 outline-none transition-all" />
                                        <span class="text-xs text-gray-500 font-medium">Bulan</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm whitespace-nowrap text-right font-medium text-gray-600">
                                    {{ formatCurrency(getHargaProduk(item.kuantitas, item.harga_produk)) }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-right font-medium text-gray-600">
                                    {{ formatCurrency(getHargaOtc(item.kategori, item.kuantitas)) }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="relative flex items-center justify-center">
                                        <input type="text" inputmode="numeric" v-model.number="item.diskon_produk"
                                            @input="ruleDiskonProduk(item, $event)"
                                            class="w-16 rounded-lg border-gray-200 bg-white py-1 pr-6 text-center text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all" />
                                        <span class="absolute right-3 pointer-events-none text-gray-400 text-xs">%</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="relative flex items-center justify-center">
                                        <input type="text" inputmode="numeric" v-model.number="item.diskon_otc"
                                            @input="ruleDiskonOtc(item, $event)"
                                            class="w-16 rounded-lg border-gray-200 bg-white py-1 pr-6 text-center text-sm font-medium focus:border-blue-500 focus:ring-2 focus:ring-blue-200 outline-none transition-all" />
                                        <span class="absolute right-3 pointer-events-none text-gray-400 text-xs">%</span>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm whitespace-nowrap text-right text-red-500">
                                    {{ formatCurrency(getDiskonProduk(item.harga_produk, item.kuantitas, item.diskon_produk)) }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-right text-red-500">
                                    {{ formatCurrency(getDiskonOtc(item.kategori, item.kuantitas, item.diskon_otc)) }}
                                </td>

                                <td class="px-6 py-4 text-sm whitespace-nowrap text-right text-gray-500">
                                    <div class="flex flex-col gap-1">
                                        <div class="flex justify-between gap-2">
                                            <span class="text-[10px] uppercase">Prod</span>
                                            <span>{{ formatCurrency(getProdukPpn(item)) }}</span>
                                        </div>
                                        <div class="flex justify-between gap-2">
                                            <span class="text-[10px] uppercase">OTC</span>
                                            <span>{{ formatCurrency(getOtcPpn(item)) }}</span>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm whitespace-nowrap text-right font-medium text-gray-900">
                                    {{ formatCurrency(hargaFinalProduk(item)) }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-right font-medium text-gray-900">
                                    {{ formatCurrency(hargaFinalOtc(item)) }}
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-right font-bold text-blue-700 bg-blue-50/30">
                                    {{ formatCurrency(hitungSubtotal(item)) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <button @click="hapusItem(item.id_produk)"
                                        class="rounded-lg p-2 text-gray-400 transition-all hover:bg-red-50 hover:text-red-600 hover:shadow-sm"
                                        title="Hapus Produk">
                                        <Trash2 class="h-5 w-5" />
                                    </button>
                                </td>
                            </tr>

                            <tr v-if="daftarKeranjang.length === 0">
                                <td colspan="15" class="px-6 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center text-gray-400">
                                        <div class="rounded-full bg-gray-50 p-4 mb-3">
                                            <Calculator class="h-8 w-8 text-gray-300" />
                                        </div>
                                        <p class="font-medium text-gray-500">Keranjang Penawaran Kosong</p>
                                        <p class="text-sm mt-1">Silakan tambah produk untuk memulai perhitungan.</p>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="daftarKeranjang.length > 0" class="bg-gray-50/80 font-bold border-t-2 border-gray-100">
                                <td colspan="9"></td>
                                <td colspan="3" class="px-6 py-6 text-right">
                                    <div class="flex flex-col gap-1 items-end">
                                        <span class="text-l text-green-600 uppercase tracking-wider">Diskon Agregat</span>
                                        <span class="text-green-600 text-xl">{{ netDiskonTotal }}%</span>
                                    </div>
                                </td>
                                <td colspan="3" class="px-6 py-8">
                                    <div class="flex flex-col gap-4 rounded-xl bg-white border border-gray-200 p-6 shadow-sm">
                                        <div class="flex justify-between items-center gap-8 text-base font-medium text-gray-600">
                                            <span>Total (Excl. PPN)</span>
                                            <span>{{ formatCurrency(totalKeseluruhan) }}</span>
                                        </div>
                                        <div class="h-px bg-gray-100 w-full"></div>
                                        <div class="flex justify-between items-center gap-8 text-l font-bold text-blue-700">
                                            <span>Grand Total</span>
                                            <span>{{ formatCurrency(totalKeseluruhan * 1.11) }}</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-end pt-4" v-if="daftarKeranjang.length > 0">
                <button @click="showModalPelanggan = true"
                    class="group flex items-center gap-3 rounded-xl bg-green-500 px-8 py-4 text-white shadow-xl shadow-gray-200 transition-all hover:bg-gray-800 hover:-translate-y-1 active:scale-[0.98]">
                    <FileText class="h-5 w-5 group-hover:text-white transition-colors" />
                    <div class="text-left">
                        <p class="text-[10px] font-medium uppercase tracking-wider">Langkah Terakhir</p>
                        <p class="font-bold text-base leading-none">Simpan Laporan Penawaran</p>
                    </div>
                    <ChevronRight class="h-5 w-5 ml-2 text-gray-500 group-hover:text-white transition-colors" />
                </button>
            </div>

            <div v-if="showModalPelanggan"
                class="fixed inset-0 z-[60] flex items-center justify-center transition-all">
                <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl scale-100 transition-all">
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900">Konfirmasi Transaksi</h3>
                        <p class="text-sm text-gray-500 mt-1">Masukkan identitas pelanggan untuk menyimpan laporan.</p>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500 ml-1">Nama Pelanggan</label>
                            <input v-model="namaPelanggan" type="text"
                                class="w-full rounded-xl border border-gray-200 bg-gray-50 p-3.5 text-gray-900 outline-none focus:bg-white focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all placeholder:text-gray-400"
                                placeholder="Contoh: Pemkab Banyumas" />
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button @click="showModalPelanggan = false"
                            class="flex-1 rounded-xl border border-gray-200 bg-white py-3 text-sm font-bold text-gray-700 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                            Batal
                        </button>
                        <button @click="tambahLaporanPenawaran"
                            class="flex-1 rounded-xl bg-blue-600 py-3 text-sm font-bold text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700 hover:shadow-blue-600/40 transition-all">
                            Simpan Data
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </Sidebar>

    <div v-if="showModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 transition-opacity"
        @click.self="closeModal">
        <div class="flex max-h-[90vh] w-full max-w-lg flex-col rounded-3xl bg-white shadow-2xl overflow-hidden">
            <div class="flex shrink-0 items-center justify-between border-b border-gray-100 p-6 bg-white">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Tambah Produk</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Cari dan masukkan produk ke keranjang</p>
                </div>
                <button @click="closeModal" class="rounded-full p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                    <X class="h-5 w-5" />
                </button>
            </div>

            <div class="flex-1 space-y-5 overflow-y-auto p-6 bg-gray-50/30">

                <div class="grid grid-cols-12 gap-4">
                    <div class="col-span-7 relative">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500">Nama Produk</label>
                        <div class="relative">
                            <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                            <input v-model="searchQuery" type="text" @focus="showDropdown = true" @blur="closeDropdownWithDelay()"
                                class="w-full rounded-xl border border-gray-200 py-2.5 pl-10 pr-4 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                                placeholder="Ketik nama produk..." />
                        </div>

                        <div v-if="showDropdown" class="absolute left-0 right-0 top-full mt-2 max-h-60 overflow-y-auto rounded-xl border border-gray-100 bg-white shadow-xl z-20 custom-scrollbar">
                            <div v-if="filteredProduk.length === 0" class="p-4 text-center text-sm text-gray-400">
                                Produk tidak ditemukan
                            </div>
                            <div v-else @mousedown.prevent="selectProduk(item)" v-for="item in filteredProduk" :key="item.id_produk"
                                class="cursor-pointer border-b border-gray-50 p-3 hover:bg-blue-50 transition-colors last:border-0 group">
                                <div class="font-bold text-sm text-gray-800 group-hover:text-blue-700">{{ item.nama_produk }}</div>
                                <div class="flex items-center gap-2 mt-1">
                                    <span class="inline-flex items-center rounded-md bg-gray-100 px-1.5 py-0.5 text-[10px] font-medium text-gray-600 uppercase">{{ item.kategori }}</span>
                                    <span class="text-xs text-gray-500">{{ item.bandwidth }} Mbps</span>
                                    <span class="text-xs font-medium text-blue-600 ml-auto">{{ formatCurrency(item.harga_produk) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-span-5">
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500">Bandwidth</label>
                        <div class="relative">
                            <input @input="ruleInputAngka($event); updateDataByBandwidth()"
                                v-model.number="searchBandWidth" type="text"
                                class="w-full rounded-xl border border-gray-200 py-2.5 pl-4 pr-12 text-sm outline-none focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 transition-all"
                                placeholder="Cari..." />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-bold text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-md">Mbps</span>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500">Kategori</label>
                        <input disabled v-model="formData.kategori" type="text"
                            class="w-full rounded-xl border border-gray-200 bg-gray-100 py-2.5 px-4 text-sm text-gray-500 cursor-not-allowed"
                            placeholder="-" />
                    </div>
                    <div>
                        <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-gray-500">Harga Satuan</label>
                        <input disabled :value="formatCurrency(formData.harga_produk)" type="text"
                            class="w-full rounded-xl border border-gray-200 bg-gray-100 py-2.5 px-4 text-sm text-gray-500 cursor-not-allowed font-medium" />
                    </div>
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-3 border-t border-gray-100 p-6 bg-white">
                <button @click="closeModal"
                    class="flex-1 rounded-xl border border-gray-200 py-2.5 text-sm font-bold text-gray-700 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button type="button" @click="tambahKeTabel" :disabled="!searchBandWidth || !formData.id_produk"
                    :class="[
                        !searchBandWidth || !formData.id_produk
                            ? 'bg-gray-200 text-gray-400 cursor-not-allowed'
                            : 'bg-blue-600 text-white shadow-lg shadow-blue-500/30 hover:bg-blue-700'
                    ]"
                    class="flex-1 rounded-xl py-2.5 text-sm font-bold transition-all">
                    + Masukkan Keranjang
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Success -->
    <div v-if="showSuccessModal"
        class="fixed inset-0 z-[70] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4 transition-all">
        <div class="w-full max-w-md rounded-3xl bg-white p-8 shadow-2xl scale-100 transition-all animate-in">
            <div class="text-center">
                <div class="mx-auto w-16 h-16 bg-green-50 rounded-full flex items-center justify-center mb-4">
                    <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Berhasil!</h3>
                <p class="text-sm text-gray-600">{{ successMessage }}</p>
            </div>

            <button @click="showSuccessModal = false"
                class="w-full mt-6 rounded-xl bg-green-600 py-3 text-sm font-bold text-white shadow-lg shadow-green-500/30 hover:bg-green-700 hover:shadow-green-600/40 transition-all">
                Tutup
            </button>
        </div>
    </div>
</template>

<style>
/* Utilities */
.custom-scrollbar::-webkit-scrollbar {
    height: 6px;
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f5f9;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Remove Arrows from Input Number */
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
input[type='number'] {
    -moz-appearance: textfield;
    appearance: textfield;
}
</style>

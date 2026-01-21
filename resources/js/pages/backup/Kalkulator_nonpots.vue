<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import Button from '@/components/ui/button/Button.vue';
import axios from 'axios';
import { FileText, Plus, X } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

// Interface
interface Produk {
    id_produk: string;
    nama_produk: string;
    kategori: string;
    harga_produk: number;
    stok: number;
}

interface Keranjang {
    id_produk: string;
    nama_produk: string;
    harga_produk: number;
    kuantitas: 0;
    otcKategori: '0';
    durasiAngka: 0;
    bulanTahun: '0';
}

// Function Kolom
const getHargaOtc = (item: any) => {
    if (item.otcKategori === '1') return 0;
    if (item.otcKategori === '2') return 150000;
    if (item.otcKategori === '3') return 500000;
    return 0;
};

const getHargaPpn = (harga: number) => {
    return harga + harga * 0.11;
};

const getHargaDurasi = (item: any) => {
    const ppn = getHargaPpn(item.harga_produk);
    if (item.bulanTahun === '1') return ppn * (item.durasiAngka || 0);
    if (item.bulanTahun === '2') return ppn * ((item.durasiAngka || 0) * 12);
    return 0;
};

const getTotalAkhir = (item: any) => {
    const hrgOtc = getHargaOtc(item);
    const hrgDurasi = getHargaDurasi(item);
    return (hrgDurasi + hrgOtc) * (item.kuantitas || 0);
};

// function modal
const closeModal = () => {
    showModal.value = false;
};

const formData = ref({
    id_produk: '',
    nama_produk: '',
    harga_produk: 0,
    kategori: '',
    stok: 0,
});
const showModal = ref(false);
const openAddModal = () => {
    formData.value = {
        id_produk: '',
        nama_produk: '',
        kategori: '',
        harga_produk: 0,
        stok: 0,
    };
    showModal.value = true;
};

// search modal
const showSearchModal = ref(false);
const searchQuery = ref('');
const showDropdown = ref(false);

// Fungsi untuk menyaring produk berdasarkan ketikan di modal pencarian
const filteredProduk = computed(() => {
    let result = daftarProduk.value;
    // Filter berdasarkan search
    if (searchQuery.value) {
        result = result.filter(
            (product) =>
                product.nama_produk.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                product.kategori.toLowerCase().includes(searchQuery.value.toLowerCase()),
        );
    }
    return result;
    // return daftarProduk.value.filter((p) => p.nama_produk.toLowerCase().includes(searchQuery.value.toLowerCase()));
});

// Function modal produk dipilih
const selectProduk = (produk: Produk) => {
    formData.value.id_produk = produk.id_produk;
    formData.value.nama_produk = produk.nama_produk;
    formData.value.harga_produk = produk.harga_produk;
    formData.value.kategori = produk.kategori;
    formData.value.stok = produk.stok;

    searchQuery.value = produk.nama_produk;
    showDropdown.value = false;
};

const dropdown = () => {
    showDropdown.value = true;
};

// Fetch Produk
const isLoading = ref(false); // Tambahkan ini untuk memperbaiki error
const error = ref<string | null>(null);
const daftarProduk = ref<Produk[]>([]);
const keranjang = ref<Keranjang[]>([]);
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

const tambahKeTabel = () => {
    if (!formData.value.nama_produk) {
        alert('Silakan pilih produk terlebih dahulu');
        return;
    }

    keranjang.value.push({
        id_produk: formData.value.id_produk,
        nama_produk: formData.value.nama_produk,
        harga_produk: formData.value.harga_produk,
        kuantitas: 0,
        otcKategori: '0',
        durasiAngka: 0,
        bulanTahun: '0',
    });

    searchQuery.value = '';
    closeModal();
};

const grandTotal = computed(() => {
    return keranjang.value.reduce((sum, item) => {
        return sum + getTotalAkhir(item);
    }, 0);
});

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
    <Sidebar>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Kalkulator Non Pots</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Kelola semua produk Anda di sini</p>
                </div>
                <button
                    @click="openAddModal"
                    class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                >
                    <Plus class="h-4 w-4" />
                    <span class="font-medium">Tambah Produk</span>
                </button>
            </div>

            <!-- Table -->
            <div class="overflow-hidden rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b bg-muted/50">
                            <tr class="text-center">
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">No</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">Produk Indibiz</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">Harga Produk</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">Jumlah</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">Kategori OTC</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">Harga OTC</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">Harga+PPN</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">Durasi Kontrak</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">Harga x Durasi</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Harga Akhir + OTC + Jumlah
                                </th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr v-for="(item, index) in keranjang" :key="item.id_produk">
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">{{ index + 1 }}</td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">{{ item.nama_produk }}</td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">{{ formatCurrency(item.harga_produk) }}</td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    <div class="flex items-center justify-center space-x-2 p-4">
                                        <Button
                                            type="button"
                                            @click="item.kuantitas > 1 ? item.kuantitas-- : null"
                                            class="flex h-6 w-6 items-center rounded border bg-transparent text-black hover:bg-gray-300"
                                            >-</Button
                                        >

                                        <input
                                            type="number"
                                            v-model="item.kuantitas"
                                            class="w-full max-w-[6rem] min-w-[3rem] rounded border px-1 text-center"
                                        />

                                        <Button
                                            type="button"
                                            @click="item.kuantitas++"
                                            class="flex h-6 w-6 items-center justify-center rounded border bg-transparent text-black hover:bg-gray-300"
                                            >+</Button
                                        >
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <select v-model="item.otcKategori" class="w-auto rounded border p-2">
                                        <option value="0" disabled>Pilih Kategori</option>
                                        <option value="1">FREE/MO</option>
                                        <option value="2">AO DISC</option>
                                        <option value="3">AO NORMAL</option>
                                    </select>
                                </td>

                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    {{ formatCurrency(getHargaOtc(item)) }}
                                </td>

                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    {{ formatCurrency(getHargaPpn(item.harga_produk)) }}
                                </td>

                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center space-x-1">
                                        <input type="number" v-model="item.durasiAngka" class="w-8 rounded border px-1 text-center" />
                                        <select class="w-auto rounded border px-1 text-left" v-model="item.bulanTahun">
                                            <option value="0" disabled>Pilih durasi</option>
                                            <option value="1">Bulan</option>
                                            <option value="2">Tahun</option>
                                        </select>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    {{ formatCurrency(getHargaDurasi(item)) }}
                                </td>

                                <td class="px-6 py-4 text-sm font-bold whitespace-nowrap text-blue-600">
                                    {{ formatCurrency(getTotalAkhir(item)) }}
                                </td>
                            </tr>
                            <tr v-if="keranjang.length > 0" class="bg-blue-50/50 font-bold">
                                <td colspan="9" class="px-6 py-4 text-right text-sm tracking-wider uppercase">Total Keseluruhan (Grand Total) :</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap text-blue-700">
                                    {{ formatCurrency(grandTotal) }}
                                </td>
                            </tr>
                            <tr v-else>
                                <td colspan="10" class="px-6 py-10 text-center text-muted-foreground">Belum ada produk yang ditambahkan.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button
                    
                    class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                >
                    <FileText class="h-4 w-4" />
                    <span class="font-medium">Tambah Transaksi</span>
                </button>
            </div>
        </div>
    </Sidebar>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-2 sm:p-4" @click.self="closeModal">
        <div class="my-auto flex max-h-[95vh] w-full max-w-[95vw] flex-col rounded-lg bg-white shadow-xl sm:max-w-md">
            <div class="flex shrink-0 items-center justify-between border-b p-4 sm:p-6">
                <h3 class="text-base font-semibold sm:text-lg">Tambah Produk Ke Keranjang</h3>
                <button @click="closeModal" class="rounded-lg p-1 transition-colors hover:bg-muted">
                    <X class="h-4 w-4 sm:h-5 sm:w-5" />
                </button>
            </div>

            <div class="flex-1 space-y-3 overflow-y-auto p-4 sm:space-y-4 sm:p-6">
                <!-- Info Timestamp - HANYA MUNCUL SAAT EDIT -->

                <!-- <div>
                    <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Nama Produk</label>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                        placeholder="Cari Produk"
                    />

                    <div class="max-h-60 overflow-y-auto rounded border">
                        <div
                            v-for="item in filteredProduk"
                            :key="item.id_produk"
                            @click="selectProduk(item)"
                            class="cursor-pointer border-b p-3 transition-colors hover:bg-blue-50"
                        >
                            <div class="text-sm font-medium">{{ item.nama_produk }}</div>
                            <div class="text-xs text-gray-500">{{ item.kategori }} - {{ formatCurrency(item.harga_produk) }}</div>
                        </div>
                        <div v-if="filteredProduk.length === 0" class="p-3 text-center text-sm text-gray-400">Produk tidak ditemukan</div>
                    </div>
                </div> -->

                <div>
                    <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Nama Produk</label>
                    <input
                        v-model="searchQuery"
                        type="text"
                        @focus="showDropdown = true"
                        @blur="showDropdown = false"
                        class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                        placeholder="Cari Produk"
                    />

                    <div v-if="showDropdown" class="mt-1 max-h-60 overflow-y-auto rounded border bg-white shadow-md">
                        <div
                            @mousedown.prevent="selectProduk(item)"
                            v-for="item in filteredProduk"
                            :key="item.id_produk"
                            class="cursor-pointer border-b p-3 transition-colors hover:bg-blue-50"
                        >
                            <div class="text-sm font-medium">{{ item.nama_produk }}</div>
                            <div class="text-xs text-gray-500">{{ item.kategori }} - {{ formatCurrency(item.harga_produk) }}</div>
                        </div>
                        <div v-if="filteredProduk.length === 0" class="p-3 text-center text-sm text-gray-400">Produk tidak ditemukan</div>
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Kategori</label>
                    <input
                        disabled
                        v-model="formData.kategori"
                        type="text"
                        class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                        placeholder="Kategori"
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Harga</label>
                    <input
                        disabled
                        v-model.number="formData.harga_produk"
                        type="number"
                        class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                        placeholder="Masukkan harga"
                    />
                </div>

                <div>
                    <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Stok</label>
                    <input
                        disabled
                        v-model.number="formData.stok"
                        type="number"
                        class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                        placeholder="Masukkan jumlah stok"
                    />
                </div>
            </div>

            <div class="flex shrink-0 items-center gap-2 border-t p-4 sm:gap-3 sm:p-6">
                <button
                    @click="closeModal"
                    class="flex-1 rounded-lg border px-3 py-1.5 text-sm transition-colors hover:bg-muted sm:px-4 sm:py-2 sm:text-base"
                >
                    Batal
                </button>
                <button
                    @click="tambahKeTabel"
                    class="flex-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm text-white transition-colors hover:bg-blue-700 sm:px-4 sm:py-2 sm:text-base"
                >
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
    -moz-appearance: textfield;
}
</style>

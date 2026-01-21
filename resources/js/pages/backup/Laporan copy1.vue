<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import axios from 'axios';
import { Search, Eye, Calendar, ArrowUpDown, ArrowUp, ArrowDown, X, Download } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

// Interface untuk header (sesuai tabel transaksi)
interface Transaksi {
    id_transaksi: string;
    created_at: string;
    updated_at: string;
    total_harga: number;
    pelanggan: string;
}

// Interface untuk rincian yang datang dari API show($id)
interface Transaksi_Children {
    id: number;
    id_transaksi: string;
    $id_produk: string;
    $nama_produk: string;
    $harga_produk: number;
    $jumlah: number;
    $durasi: number;
    $harga_otc: number;
    $diskon_produk: number;
    $diskon_otc: number;
    $nominal_diskon_produk: number;
    $nominal_diskon_otc: number;
    $ppn_produk: number;
    $ppn_otc: number;
    $produk_final: number;
    $otc_final: number;
    $subtotal: number;
}

// State
const semua_transaksi = ref<Transaksi[]>([]);
const searchQuery = ref('');
const isLoading = ref(false);
const showDetailModal = ref(false);
const selectedDetail = ref<Transaksi_Children | null>(null);

// State Sorting
const sortBy = ref<'created_at' | 'total_harga'>('created_at');
const sortOrder = ref<'asc' | 'desc'>('desc');

const fetchTransactions = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/transaksi');
        semua_transaksi.value = response.data;
    } catch (err) {
        console.error('Gagal mengambil laporan:', err);
    } finally {
        isLoading.value = false;
    }
};


// Fetch Detail Asli
// const fetchDetail = async (id: string) => {
//     try {
//         const response = await axios.get(`/api/transaksi/${id}`);
//         console.log("Data dari API:", response.data); // <--- CEK INI DI KONSOL BROWSER
//         selectedDetail.value = response.data;
//         showDetailModal.value = true;
//     } catch (err) {
//         alert('Gagal memuat detail transaksi');
//     }
// };


onMounted(fetchTransactions);

// Logika Filter & Sort
const filteredTransactions = computed(() => {
    let result = [...semua_transaksi.value];

    if (searchQuery.value) {
        result = result.filter(t =>
            t.id_transaksi.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    return result.sort((a, b) => {
        if (sortBy.value === 'total_harga') {
            return sortOrder.value === 'asc' ? a.total_harga - b.total_harga : b.total_harga - a.total_harga;
        } else {
            const dateA = new Date(a.created_at).getTime();
            const dateB = new Date(b.created_at).getTime();
            return sortOrder.value === 'asc' ? dateA - dateB : dateB - dateA;
        }
    });
});

const toggleSort = (field: 'created_at' | 'total_harga') => {
    if (sortBy.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortOrder.value = 'desc';
    }
};

// Helpers
const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
const formatDate = (date: string) => new Date(date).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });


const dummyDetailItems = [
    {
        nama_produk: "Astinet 1:2 (100 Mbps)",
        harga_produk: 4876000,
        jumlah: 2,
        durasi: 12, // dalam bulan
        harga_otc: 5000000,
        diskon_produk: 10, // persentase
        diskon_otc: 20, // persentase
        nominal_diskon_produk: 975200,
        nominal_diskon_otc: 1000000,
        ppn_produk: 1072720,
        ppn_otc: 550000,
        produk_final: 9849520,
        otc_final: 4550000,
        subtotal: 122744240 // (produk_final * durasi) + otc_final
    },
    {
        nama_produk: "Internet Dedicated 50 Mbps",
        harga_produk: 3500000,
        jumlah: 1,
        durasi: 1,
        harga_otc: 1000000,
        diskon_produk: 0,
        diskon_otc: 0,
        nominal_diskon_produk: 0,
        nominal_diskon_otc: 0,
        ppn_produk: 385000,
        ppn_otc: 110000,
        produk_final: 3885000,
        otc_final: 1110000,
        subtotal: 4995000
    }
];
</script>

<template>
    <Sidebar>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold uppercase tracking-tight">Laporan Transaksi</h2>
                    <p class="text-sm text-muted-foreground">Pantau riwayat penjualan dan aktivitas transaksi</p>
                </div>
                <button
                    class="flex items-center gap-2 rounded-lg border bg-white px-4 py-2 text-sm font-medium shadow-sm hover:bg-gray-50">
                    <Download class="h-4 w-4" /> Export CSV
                </button>
            </div>

            <div class="grid gap-4 rounded-xl border bg-card p-4 shadow-sm sm:flex sm:items-center sm:justify-between">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="searchQuery" type="text" placeholder="Cari ID Transaksi..."
                        class="w-full rounded-lg border py-2 pl-10 pr-4 focus:ring-2 focus:ring-blue-500 outline-none" />
                </div>

                <div class="flex items-center gap-2">
                    <button @click="toggleSort('created_at')"
                        :class="['flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-all', sortBy === 'created_at' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white hover:bg-gray-50']">
                        <Calendar class="h-4 w-4" /> Tanggal
                    </button>
                    <button @click="toggleSort('total_harga')"
                        :class="['flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-all', sortBy === 'total_harga' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white hover:bg-gray-50']">
                        <ArrowUpDown class="h-4 w-4" /> Nominal
                    </button>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="px-6 py-4 text-left font-semibold text-gray-600">No</th>
                            <th class="px-6 py-4 text-left font-semibold text-gray-600">ID Transaksi</th>
                            <th class="px-6 py-4 text-left font-semibold text-gray-600">Waktu</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-600">Total Item</th>
                            <th class="px-6 py-4 text-right font-semibold text-gray-600">Total Harga</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-for="(trx, index) in filteredTransactions" :key="trx.id_transaksi"
                            class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-center">{{ index + 1 }}</td>
                            <td class="px-6 py-4 font-mono font-medium text-blue-600">{{ trx.id_transaksi }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ formatDate(trx.created_at) }}</td>
                            <td class="px-6 py-4 text-center">{{ trx.pelanggan }} </td>
                            <td class="px-6 py-4 text-right font-bold">{{ formatCurrency(trx.total_harga) }}</td>
                            <td class="px-6 py-4 text-center">
                                <button @click="fetchDetail(trx.id_transaksi)"
                                    class="inline-flex items-center gap-1.5 text-blue-600 hover:text-blue-800 font-medium">
                                    <Eye class="h-4 w-4" /> Detail
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="filteredTransactions.length === 0" class="p-10 text-center text-muted-foreground">
                    Belum ada data transaksi yang ditemukan.
                </div>
            </div>
        </div>

        <div v-if="showDetailModal && selectedDetail"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b p-6">
                    <div>
                        <h3 class="text-lg font-bold">Rincian Transaksi</h3>
                        <p class="text-sm text-gray-500">{{ selectedDetail.$nama_produk }}</p>
                    </div>
                    <button @click="showDetailModal = false" class="rounded-full p-2 hover:bg-gray-100">
                        <X />
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4 flex justify-between rounded-lg bg-gray-50 p-4 text-sm">
                        <span>Waktu: {{ formatDate(selectedDetail.harga_produk) }}</span>
                        <span class="font-bold">Total Pembayaran: {{
                            formatCurrency(selectedDetail.transaksi.total_harga) }}</span>
                    </div>

                    <div class="max-h-[40vh] overflow-y-auto border rounded-lg">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="p-3 text-left">Produk</th>
                                    <th class="p-3 text-center">Qty</th>
                                    <th class="p-3 text-right">Harga</th>
                                    <th class="p-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="item in selectedDetail.items" :key="item.id_produk">
                                    <td class="p-3">
                                        <div class="flex flex-col text-left">
                                            <span class="font-medium text-gray-900">
                                                {{ item.nama_produk || 'Produk Tidak Dikenal' }}
                                            </span>
                                            <span class="text-xs text-gray-500 font-mono">
                                                {{ item.id_produk }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="p-3 text-center">{{ item.kuantitas }}</td>
                                    <td class="p-3 text-right">{{ formatCurrency(item.harga_produk) }}</td>
                                    <td class="p-3 text-right font-semibold text-blue-600">
                                        {{ formatCurrency(item.subtotal) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="border-t p-6 flex justify-end">
                    <button @click="showDetailModal = false"
                        class="rounded-lg bg-gray-900 px-6 py-2 text-white hover:bg-gray-800">Tutup</button>
                </div>
            </div>
        </div>
    </Sidebar>
</template>
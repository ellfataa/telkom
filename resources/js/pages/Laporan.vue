<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import axios from 'axios';
import { Search, Eye, Calendar, ArrowUpDown, ArrowUp, ArrowDown, X, Download, Trash2Icon, Edit2, Edit2Icon, Edit } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';


// Interface
// Interface Raw Transaksi
interface Raw_Transaksi {
    id_transaksi: string;
    netdiskon: number;
    total_harga: number;
    created_at: string;
    pelanggan: string;
    id_produk: string;
    nama_produk: string;
    harga_produk: number;
    jumlah: number;
    durasi: number;
    harga_otc: number;
    diskon_produk: number;
    diskon_otc: number;
    nominal_diskon_produk: number;
    nominal_diskon_otc: number;
    ppn_produk: number;
    ppn_otc: number;
    produk_final: number;
    otc_final: number;
}

// Interface Transaksi
interface Transaksi {
    id_transaksi: string;
    created_at: string;
    updated_at: string;
    total_harga: number;
    pelanggan: string;
    netdiskon: number;
}

// Interface Detail Transaksi
interface Transaksi_Children {
    id: number;
    id_transaksi: string;
    id_produk: string;
    nama_produk: string;
    harga_produk: number;
    jumlah: number;
    durasi: number;
    harga_otc: number;
    diskon_produk: number;
    diskon_otc: number;
    nominal_diskon_produk: number;
    nominal_diskon_otc: number;
    ppn_produk: number;
    ppn_otc: number;
    produk_final: number;
    otc_final: number;
    subtotal: number;
}

interface Selected_Transaksi {
    id_transaksi: string;
    pelanggan: string;
    total_harga: number;
    netdiskon: number;
    created_at: string;
    // items adalah array yang berisi baris-baris rincian produk
    items: Transaksi_Children[];
}

// ref (Reactive State)
// ref data dari database
const rawData = ref<Raw_Transaksi[]>([]);
const searchQuery = ref('');
const isLoading = ref(false);
const showDetailModal = ref(false);

// ref modal
const selectedTransaksi = ref<Selected_Transaksi | null>(null);
// const selectedDetail = ref<DetailResponse | null>(null);

// ref State Sorting
const sortBy = ref<'created_at' | 'total_harga'>('created_at');
const sortOrder = ref<'asc' | 'desc'>('desc');




// Semua Function / Fungsi
// Fungsi menampilkan semua transaksi
const fetchSemuaTransaksi = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/transaksi');
        // Simpan data mentah (flat array) dari backend
        rawData.value = response.data.data;

    } catch (err) {
        console.error('Gagal mengambil laporan:', err);
    } finally {
        isLoading.value = false;
    }
};

// Function menampilkan interface transaksi
const daftarLaporan = computed<Transaksi[]>(() => {
    const uniqueMap = new Map();

    rawData.value.forEach((item) => {
        if (!uniqueMap.has(item.id_transaksi)) {
            uniqueMap.set(item.id_transaksi, {
                id_transaksi: item.id_transaksi,
                created_at: item.created_at,
                // Tambahkan updated_at jika ada di data asli,
                // jika tidak, gunakan created_at sebagai fallback
                updated_at: item.created_at,
                total_harga: item.total_harga,
                pelanggan: item.pelanggan,
                netdiskon: item.netdiskon,
            });
        }
    });

    return Array.from(uniqueMap.values());
});

// Fungsi grup transaksi
const grupTransaksi = computed(() => {
    const grup = rawData.value.reduce((acc: any, item) => {
        const id = item.id_transaksi;
        if (!acc[id]) {
            // Jika ID belum ada di grup, buat objek induknya
            acc[id] = {
                id_transaksi: item.id_transaksi,
                pelanggan: item.pelanggan,
                total_harga: item.total_harga,
                netdiskon: item.netdiskon,
                created_at: item.created_at,
                items: [] // Tempat menampung banyak produk
            };
        }
        // Masukkan detail produk ke dalam array items
        acc[id].items.push({
            id_produk: item.id_produk,
            nama_produk: item.nama_produk,
            harga_produk: item.harga_produk,
            jumlah: item.jumlah,
            durasi: item.durasi,
            harga_otc: item.harga_otc,
            diskon_produk: item.diskon_produk,
            diskon_otc: item.diskon_otc,
            nominal_diskon_produk: item.nominal_diskon_produk,
            nominal_diskon_otc: item.nominal_diskon_otc,
            ppn_produk: item.ppn_produk,
            ppn_otc: item.ppn_otc,
            produk_final: item.produk_final,
            otc_final: item.otc_final,
            subtotal: (Number(item.produk_final) * item.durasi) + Number(item.otc_final)
        });
        return acc;
    }, {});

    return Object.values(grup);
});

const fetchDetail = (id: string) => {
    // 1. Cari data di grupTransaksi
    const ditemukan = grupTransaksi.value.find((t: any) => t.id_transaksi === id);

    if (ditemukan) {
        // 2. Gunakan 'as Selected_Transaksi' untuk memaksa tipe datanya cocok
        selectedTransaksi.value = ditemukan as Selected_Transaksi;
        showDetailModal.value = true;
    } else {
        console.error("Data transaksi tidak ditemukan di dalam grup");
    }
};


const handleDelete = async (id: string) => {
    if (!confirm(`Hapus permanen laporan penawaran kode: ${id}?`)) return;

    try {
        await axios.delete(`/api/transaksi/${id}`);
        // Hapus data dari rawData secara lokal agar UI langsung update tanpa fetch ulang
        rawData.value = rawData.value.filter(item => item.id_transaksi !== id);
        alert('Laporan berhasil dihapus');
    } catch (err) {
        console.error(err);
        alert('Gagal menghapus laporan');
    }
}

// Logika Filter & Sort
const filteredTransactions = computed(() => {
    let result = [...daftarLaporan.value];

    if (searchQuery.value) {
        result = result.filter(t =>
            t.pelanggan.toLowerCase().includes(searchQuery.value.toLowerCase())
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

const getSortIcon = (field: 'created_at' | 'total_harga') => {
    // Jika kolom ini tidak sedang di-sort, tampilkan ikon netral (atas-bawah)
    if (sortBy.value !== field) return ArrowUpDown;

    // Jika sedang di-sort, tampilkan panah sesuai urutan
    return sortOrder.value === 'asc' ? ArrowUp : ArrowDown;
};

// Helpers
const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
const formatDate = (date: string) => new Date(date).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

// On Mounter Rendering
onMounted(fetchSemuaTransaksi);
</script>

<template>
    <Sidebar>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold uppercase tracking-tight">Laporan Penawaran</h2>
                    <p class="text-sm text-muted-foreground">Pantau aktivitas penawaran</p>
                </div>
                <button
                    class="flex items-center gap-2 rounded-lg border bg-white px-4 py-2 text-sm font-medium shadow-sm hover:bg-gray-50">
                    <Download class="h-4 w-4" /> Export CSV
                </button>
            </div>

            <div class="grid gap-4 rounded-xl border bg-card p-4 shadow-sm sm:flex sm:items-center sm:justify-between">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="searchQuery" type="text" placeholder="Cari Pelanggan..."
                        class="w-full rounded-lg border py-2 pl-10 pr-4 focus:ring-2 focus:ring-blue-500 outline-none" />
                </div>

                <div class="flex items-center gap-2">
                    <button @click="toggleSort('created_at')"
                        :class="['flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-all', sortBy === 'created_at' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white hover:bg-gray-50']">
                        <!-- <Calendar class="h-4 w-4" />  -->
                        <component class="h-4 w-4" :is="getSortIcon('created_at')" />
                        Tanggal
                    </button>
                    <button @click="toggleSort('total_harga')"
                        :class="['flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-all', sortBy === 'total_harga' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white hover:bg-gray-50']">
                        <!-- <ArrowUpDown class="h-4 w-4" /> -->
                        <component class="h-4 w-4" :is="getSortIcon('total_harga')" />
                        Nominal
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
                            <th class="px-6 py-4 text-center font-semibold text-gray-600">Pelanggan</th>
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
                            <td class="px-6 py-4 text-center">{{ trx.pelanggan.toUpperCase() }} </td>
                            <td class="px-6 py-4 text-right font-bold">{{ formatCurrency(trx.total_harga) }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <button @click="fetchDetail(trx.id_transaksi)"
                                        class="text-gray-600 hover:text-gray-800 transition-colors"
                                        title="Lihat Detail">
                                        <Eye class="h-5 w-5" />
                                    </button>

                                    <Link :href="`api/laporan/edit/${trx.id_transaksi}`"
                                        class="text-blue-600 hover:text-blue-800 transition-colors"
                                        title="Edit Penawaran">
                                        <Edit class="h-5 w-5" />
                                    </Link>


                                    <button @click="handleDelete(trx.id_transaksi)"
                                        class="text-red-600 hover:text-red-800 transition-colors" title="Hapus Laporan">
                                        <Trash2Icon class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <div v-if="filteredTransactions.length === 0" class="p-10 text-center text-muted-foreground">
                    Belum ada data transaksi yang ditemukan.
                </div>
            </div>
        </div>

        <div v-if="showDetailModal && selectedTransaksi"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="w-full max-w-2xl rounded-2xl bg-white shadow-2xl">
                <div class="flex items-center justify-between border-b p-6">
                    <div>
                        <h3 class="text-lg font-bold">Rincian Transaksi {{ selectedTransaksi.id_transaksi }}
                        </h3>
                        <p class="text-sm text-gray-500">{{ selectedTransaksi.pelanggan }}</p>
                    </div>
                    <button @click="showDetailModal = false" class="rounded-full p-2 hover:bg-gray-100">
                        <X />
                    </button>
                </div>

                <div class="p-6">
                    <div class="mb-4 flex justify-between rounded-lg bg-gray-50 p-4 text-sm">
                        <span>Waktu: {{ formatDate(selectedTransaksi.created_at) }}</span>
                        <div class="flex-col">
                            <div>
                                <span class="font-bold">Total Penawaran: {{
                                    formatCurrency(selectedTransaksi.total_harga) }}</span>
                            </div>
                            <div>
                                <span class="font-bold">Diskon Agregat: {{
                                    selectedTransaksi.netdiskon }}%</span>
                            </div>
                        </div>
                    </div>



                    <div class="max-h-[40vh] overflow-y-auto border rounded-lg">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 sticky top-0">
                                <tr>
                                    <th class="p-3 text-left">Produk</th>
                                    <th class="p-3 text-center">Harga</th>
                                    <th class="p-3 text-right">Qty</th>
                                    <th class="p-3 text-right">Durasi</th>
                                    <th class="p-3 text-right">OTC</th>
                                    <th class="p-3 text-right">Disc P (%)</th>
                                    <th class="p-3 text-right">Disc O (%)</th>
                                    <th class="p-3 text-right">Nominal Disc Produk</th>
                                    <th class="p-3 text-right">Nominal Disc OTC</th>

                                    <th class="p-3 text-right">PPN Produk</th>
                                    <th class="p-3 text-right">PPN OTC</th>
                                    <th class="p-3 text-right">Final Produk</th>
                                    <th class="p-3 text-right">Final OTC</th>
                                    <th class="p-3 text-right">Monthly Price</th>
                                    <th class="p-3 text-right bg-blue-50">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                <tr v-for="item in selectedTransaksi.items" :key="item.id_produk">
                                    <td class="p-3 font-medium">{{ item.nama_produk }}</td>
                                    <td class="p-3 text-right">{{ formatCurrency(item.harga_produk) }}</td>
                                    <td class="p-3 text-center">{{ item.jumlah }}</td>
                                    <td class="p-3 text-center">{{ item.durasi }} Bln</td>
                                    <td class="p-3 text-right">{{ formatCurrency(item.harga_otc) }}</td>
                                    <td class="p-3 text-center">{{ item.diskon_produk }}%</td>
                                    <td class="p-3 text-center">{{ item.diskon_otc }}%</td>
                                    <td class="p-3 text-right">{{ formatCurrency(item.nominal_diskon_produk) }}</td>
                                    <td class="p-3 text-right">{{ formatCurrency(item.nominal_diskon_otc) }}</td>
                                    <td class="p-3 text-right">{{ formatCurrency(item.ppn_produk) }}</td>
                                    <td class="p-3 text-right">{{ formatCurrency(item.ppn_otc) }}</td>
                                    <td class="p-3 text-right">{{ formatCurrency(item.produk_final) }}</td>
                                    <td class="p-3 text-right">{{ formatCurrency(item.otc_final) }}</td>
                                    <td class="p-3 text-right">{{ formatCurrency(item.harga_produk / item.durasi) }}</td>
                                    <td class="p-3 text-right font-bold text-blue-700 bg-blue-50/50">{{
                                        formatCurrency(item.subtotal) }}</td>
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

<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import axios from 'axios';
import { Search, Eye, Calendar, ArrowUpDown, ArrowUp, ArrowDown, X, Download, Trash2Icon, Edit2, Edit2Icon, Edit } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

// ==========================================
// INTERFACES (TIPE DATA)
// ==========================================

// 1. Interface Raw Data (Sesuai output JSON Controller)
interface Raw_Laporan {
    id_laporan: string; // Dari Controller: t.id_transaksi AS id_laporan
    netdiskon: number;
    total_harga: number;
    created_at: string;
    updated_at: string;
    pelanggan: string;
    // Detail Produk (bisa null jika left join kosong)
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

// 2. Interface List Laporan (Untuk Tabel Utama)
interface Laporan {
    id_transaksi: string; // Mapping dari id_laporan
    created_at: string;
    updated_at: string;
    total_harga: number;
    pelanggan: string;
    netdiskon: number;
}

// 3. Interface Detail Item (Untuk Modal)
interface Laporan_Children {
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

// 4. Interface Selected Laporan (Untuk Modal Detail)
interface Selected_Laporan {
    id_transaksi: string;
    pelanggan: string;
    total_harga: number;
    netdiskon: number;
    created_at: string;
    items: Laporan_Children[];
}

// ==========================================
// STATE MANAGEMENT (REACTIVE)
// ==========================================
const rawData = ref<Raw_Laporan[]>([]);
const searchQuery = ref('');
const isLoading = ref(false);
const showDetailModal = ref(false);
const selectedLaporan = ref<Selected_Laporan | null>(null);

// Sorting State
const sortBy = ref<'created_at' | 'total_harga'>('created_at');
const sortOrder = ref<'asc' | 'desc'>('desc');

// Delete All State
const modalDeleteAllLaporan = ref(false);
const confirmAllLaporan = ref('');

// ==========================================
// DATA FETCHING & COMPUTED
// ==========================================

// 1. Fetch Data dari API
const fetchSemuaLaporan = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/laporan');
        // Simpan data mentah (flat array) dari backend
        rawData.value = response.data.data;
    } catch (err) {
        console.error('Gagal mengambil laporan:', err);
    } finally {
        isLoading.value = false;
    }
};

// 2. Computed: Mengolah Raw Data menjadi List Unik untuk Tabel
const daftarLaporan = computed<Laporan[]>(() => {
    const uniqueMap = new Map();

    rawData.value.forEach((item) => {
        // Skip jika id tidak valid
        if (!item.id_laporan) return;

        if (!uniqueMap.has(item.id_laporan)) {
            uniqueMap.set(item.id_laporan, {
                // PENTING: Mapping id_laporan (DB) ke id_transaksi (UI)
                id_transaksi: item.id_laporan,
                created_at: item.created_at,
                updated_at: item.updated_at,
                total_harga: item.total_harga,
                pelanggan: item.pelanggan,
                netdiskon: item.netdiskon,
            });
        }
    });

    return Array.from(uniqueMap.values());
});

// 3. Computed: Mengelompokkan Data untuk Detail Modal
const grupLaporan = computed(() => {
    const grup = rawData.value.reduce((acc: any, item) => {
        const id = item.id_laporan;

        // Inisialisasi object parent jika belum ada
        if (!acc[id]) {
            acc[id] = {
                id_transaksi: item.id_laporan, // Mapping ke id_transaksi
                pelanggan: item.pelanggan,
                total_harga: item.total_harga,
                netdiskon: item.netdiskon,
                created_at: item.created_at,
                items: []
            };
        }

        // Masukkan detail produk HANYA jika id_produk ada (menangani hasil LEFT JOIN yg kosong)
        if (item.id_produk) {
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
                subtotal: item.subtotal
            });
        }
        return acc;
    }, {});

    return Object.values(grup);
});

// 4. Computed: Filter & Sorting Tabel
const filteredLaporan = computed(() => {
    let result = [...daftarLaporan.value];

    // Filter Pencarian
    if (searchQuery.value) {
        result = result.filter(t =>
            t.pelanggan.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            t.id_transaksi.toLowerCase().includes(searchQuery.value.toLowerCase())
        );
    }

    // Sorting
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

// 5. Computed: Total Bulanan (Recurring) di Modal
const totalBulanan = computed(() => {
    if (!selectedLaporan.value?.items) return 0;

    return selectedLaporan.value.items.reduce((acc, item) => {
        // Asumsi: Harga recurring adalah (Harga Produk setelah diskon * jumlah)
        // Sesuaikan logika bisnis Anda jika perlu
        // Di sini saya ambil logic sederhana: produk_final adalah harga per unit setelah diskon & ppn
        // Jika durasi > 0 biasanya recurring.
        const hargaRecurring = item.durasi > 0 ? (Number(item.produk_final) * item.jumlah) : 0;
        return acc + hargaRecurring;
    }, 0);
});

// ==========================================
// ACTIONS (FUNGSI INTERAKSI)
// ==========================================

const fetchDetail = (id_transaksi: string) => {
    // Cari data di grupLaporan berdasarkan id_transaksi
    const ditemukan = grupLaporan.value.find((t: any) => t.id_transaksi === id_transaksi);

    if (ditemukan) {
        selectedLaporan.value = ditemukan as Selected_Laporan;
        showDetailModal.value = true;
    } else {
        console.error("Data Laporan tidak ditemukan di dalam grup");
    }
};

const handleDelete = async (id: string) => {
    if (!confirm(`Hapus permanen laporan penawaran kode: ${id}?`)) return;

    try {
        await axios.delete(`/api/laporan/${id}`);
        // Hapus data dari rawData secara lokal agar UI update instan
        rawData.value = rawData.value.filter(item => item.id_laporan !== id);
        alert('Laporan berhasil dihapus');
    } catch (err) {
        console.error(err);
        alert('Gagal menghapus laporan');
    }
};

const deleteAllLaporan = async () => {
    try {
        if (confirmAllLaporan.value !== '1234567890') return alert('Kode konfirmasi salah');

        await axios.delete('/api/laporan/delete-all-laporan');
        alert('Semua Laporan Penawaran Berhasil Dihapus');
        modalDeleteAllLaporan.value = false;
        confirmAllLaporan.value = '';
        fetchSemuaLaporan(); // Refresh data
    } catch (error) {
        console.error(error);
        alert('Gagal menghapus semua laporan');
    }
};

const cekDeleteAllLaporan = () => {
    modalDeleteAllLaporan.value = true;
};

// Sorting helper
const toggleSort = (field: 'created_at' | 'total_harga') => {
    if (sortBy.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortOrder.value = 'desc';
    }
};

const getSortIcon = (field: 'created_at' | 'total_harga') => {
    if (sortBy.value !== field) return ArrowUpDown;
    return sortOrder.value === 'asc' ? ArrowUp : ArrowDown;
};

// Formatters
const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
const formatDate = (date: string) => new Date(date).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

// Lifecycle
onMounted(fetchSemuaLaporan);
</script>

<template>
    <Sidebar>
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold uppercase tracking-tight">Laporan Penawaran</h2>
                    <p class="text-sm text-muted-foreground">Pantau aktivitas penawaran</p>
                </div>
                <div class="flex space-x-2">
                    <button
                        class="flex items-center gap-2 rounded-lg border bg-white px-4 py-2 text-sm font-medium shadow-sm hover:bg-gray-50">
                        <Download class="h-4 w-4" /> Export CSV
                    </button>
                    <button @click="cekDeleteAllLaporan"
                        class="flex items-center gap-2 rounded-lg border bg-white px-4 py-2 text-sm font-medium shadow-sm hover:bg-gray-50 text-red-600 border-red-200 hover:bg-red-50">
                        <Trash2Icon class="h-4 w-4" /> Hapus Semua
                    </button>
                </div>
            </div>

            <div v-if="modalDeleteAllLaporan"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 p-4">
                <div class="w-full max-w-sm rounded-lg bg-white p-6 shadow-xl animate-in fade-in zoom-in duration-200">
                    <h3 class="mb-4 text-lg font-bold text-red-600">Konfirmasi Hapus Total</h3>
                    <label class="mb-2 block text-sm font-medium text-gray-700">
                        Tindakan ini tidak dapat dibatalkan. Ketik <strong>1234567890</strong> untuk konfirmasi.
                    </label>

                    <input v-model="confirmAllLaporan" type="text"
                        class="w-full rounded-lg border p-2 outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Masukkan kode konfirmasi..." />

                    <div class="mt-6 flex gap-3">
                        <button @click="modalDeleteAllLaporan = false"
                            class="flex-1 rounded-lg border py-2 hover:bg-gray-50">Batal</button>
                        <button @click="deleteAllLaporan"
                            class="flex-1 rounded-lg bg-red-600 py-2 text-white hover:bg-red-700 font-medium">Hapus</button>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 rounded-xl border bg-card p-4 shadow-sm sm:flex sm:items-center sm:justify-between">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="searchQuery" type="text" placeholder="Cari Pelanggan atau ID..."
                        class="w-full rounded-lg border py-2 pl-10 pr-4 focus:ring-2 focus:ring-blue-500 outline-none" />
                </div>

                <div class="flex items-center gap-2">
                    <button @click="toggleSort('created_at')"
                        :class="['flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-all', sortBy === 'created_at' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white hover:bg-gray-50']">
                        <component class="h-4 w-4" :is="getSortIcon('created_at')" />
                        Tanggal
                    </button>
                    <button @click="toggleSort('total_harga')"
                        :class="['flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-all', sortBy === 'total_harga' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white hover:bg-gray-50']">
                        <component class="h-4 w-4" :is="getSortIcon('total_harga')" />
                        Nominal
                    </button>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border bg-white shadow-sm">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr class="uppercase text-xs tracking-wider">
                            <th class="px-6 py-4 text-left font-semibold text-gray-600 w-16">No</th>
                            <th class="px-6 py-4 text-left font-semibold text-gray-600">ID Laporan</th>
                            <th class="px-6 py-4 text-left font-semibold text-gray-600">Dibuat</th>
                            <th class="px-6 py-4 text-left font-semibold text-gray-600">Diperbarui</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-600">Pelanggan</th>
                            <th class="px-6 py-4 text-right font-semibold text-gray-600">Total Harga</th>
                            <th class="px-6 py-4 text-center font-semibold text-gray-600">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        <tr v-if="isLoading">
                            <td colspan="7" class="p-8 text-center text-gray-500">Memuat data...</td>
                        </tr>
                        <tr v-else-if="filteredLaporan.length === 0">
                            <td colspan="7" class="p-10 text-center text-muted-foreground">
                                Belum ada data Laporan yang ditemukan.
                            </td>
                        </tr>
                        <tr v-for="(trx, index) in filteredLaporan" :key="trx.id_transaksi"
                            class="hover:bg-gray-50/50 transition-colors">
                            <td class="px-6 py-4 text-center text-gray-500">{{ index + 1 }}</td>
                            <td class="px-6 py-4 font-mono font-medium text-blue-600">{{ trx.id_transaksi }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ formatDate(trx.created_at) }}</td>
                            <td class="px-6 py-4 text-gray-500">{{ formatDate(trx.updated_at) }}</td>
                            <td class="px-6 py-4 text-center font-medium">{{ trx.pelanggan ? trx.pelanggan.toUpperCase() : '-' }}</td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900">{{ formatCurrency(trx.total_harga) }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <button @click="fetchDetail(trx.id_transaksi)"
                                        class="text-gray-500 hover:text-gray-800 transition-colors tooltip"
                                        title="Lihat Rincian">
                                        <Eye class="h-5 w-5" />
                                    </button>

                                    <Link :href="`/laporan/${trx.id_transaksi}/edit`"
                                        class="text-blue-600 hover:text-blue-800 transition-colors tooltip"
                                        title="Edit Penawaran">
                                        <Edit class="h-5 w-5" />
                                    </Link>

                                    <button @click="handleDelete(trx.id_transaksi)"
                                        class="text-red-500 hover:text-red-700 transition-colors tooltip"
                                        title="Hapus Permanen">
                                        <Trash2Icon class="h-5 w-5" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="showDetailModal && selectedLaporan"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4 backdrop-blur-sm">
            <div class="w-full max-w-4xl rounded-2xl bg-white shadow-2xl max-h-[90vh] flex flex-col animate-in fade-in zoom-in duration-200">
                <div class="flex items-center justify-between border-b p-6">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Rincian Penawaran</h3>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="px-2 py-0.5 rounded bg-blue-100 text-blue-800 text-xs font-mono font-bold">{{ selectedLaporan.id_transaksi }}</span>
                            <span class="text-sm text-gray-500">{{ selectedLaporan.pelanggan.toUpperCase() }}</span>
                        </div>
                    </div>
                    <button @click="showDetailModal = false" class="rounded-full p-2 hover:bg-gray-100 transition-colors">
                        <X class="h-6 w-6 text-gray-500" />
                    </button>
                </div>

                <div class="p-6 overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="rounded-lg border bg-gray-50 p-4">
                            <p class="text-xs text-gray-500 uppercase font-semibold">Total Penawaran (One-Time)</p>
                            <p class="text-lg font-bold text-gray-900">{{ formatCurrency(selectedLaporan.total_harga) }}</p>
                        </div>
                        <div class="rounded-lg border bg-gray-50 p-4">
                            <p class="text-xs text-gray-500 uppercase font-semibold">Estimasi Bulanan (Recurring)</p>
                            <p class="text-lg font-bold text-blue-600">{{ formatCurrency(totalBulanan) }}</p>
                        </div>
                         <div class="rounded-lg border bg-gray-50 p-4">
                            <p class="text-xs text-gray-500 uppercase font-semibold">Diskon Agregat</p>
                            <p class="text-lg font-bold text-green-600">{{ selectedLaporan.netdiskon }}%</p>
                        </div>
                    </div>

                    <div class="border rounded-lg overflow-hidden">
                        <div class="max-h-[50vh] overflow-y-auto">
                            <table class="w-full text-xs">
                                <thead class="bg-gray-100 sticky top-0 z-10">
                                    <tr>
                                        <th class="p-3 text-left font-semibold w-1/4">Produk</th>
                                        <th class="p-3 text-center font-semibold">Qty</th>
                                        <th class="p-3 text-center font-semibold">Durasi</th>
                                        <th class="p-3 text-right font-semibold">Harga Satuan</th>
                                        <th class="p-3 text-right font-semibold">OTC</th>
                                        <th class="p-3 text-center font-semibold">Disc P(%)</th>
                                        <th class="p-3 text-center font-semibold">Disc O(%)</th>
                                        <th class="p-3 text-right font-semibold">Final P</th>
                                        <th class="p-3 text-right font-semibold">Final O</th>
                                        <th class="p-3 text-right font-semibold bg-blue-50">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y">
                                    <tr v-for="item in selectedLaporan.items" :key="item.id_produk" class="hover:bg-gray-50">
                                        <td class="p-3 font-medium text-gray-900">{{ item.nama_produk }}</td>
                                        <td class="p-3 text-center">{{ item.jumlah }}</td>
                                        <td class="p-3 text-center">{{ item.durasi }} Bln</td>
                                        <td class="p-3 text-right text-gray-500">{{ formatCurrency(item.harga_produk) }}</td>
                                        <td class="p-3 text-right text-gray-500">{{ formatCurrency(item.harga_otc) }}</td>
                                        <td class="p-3 text-center text-red-500">{{ item.diskon_produk }}%</td>
                                        <td class="p-3 text-center text-red-500">{{ item.diskon_otc }}%</td>
                                        <td class="p-3 text-right font-medium">{{ formatCurrency(item.produk_final) }}</td>
                                        <td class="p-3 text-right font-medium">{{ formatCurrency(item.otc_final) }}</td>
                                        <td class="p-3 text-right font-bold text-blue-700 bg-blue-50/30">
                                            {{ formatCurrency(item.subtotal) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="border-t p-4 flex justify-end bg-gray-50 rounded-b-2xl">
                    <button @click="showDetailModal = false"
                        class="rounded-lg bg-gray-900 px-6 py-2 text-white hover:bg-gray-800 font-medium transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </Sidebar>
</template>

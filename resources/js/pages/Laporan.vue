<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import axios from 'axios';
import {
    Search, Eye, Calendar, ArrowUpDown, ArrowUp, ArrowDown,
    X, Download, Trash2, Edit, Loader2, FileX, AlertTriangle
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

// ==========================================
// INTERFACES (TIPE DATA)
// ==========================================

interface Raw_Laporan {
    id_laporan: string;
    netdiskon: number;
    total_harga: number;
    created_at: string;
    updated_at: string;
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
    subtotal: number;
}

interface Laporan {
    id_transaksi: string;
    created_at: string;
    updated_at: string;
    total_harga: number;
    pelanggan: string;
    netdiskon: number;
}

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

interface Selected_Laporan {
    id_transaksi: string;
    pelanggan: string;
    total_harga: number;
    netdiskon: number;
    created_at: string;
    items: Laporan_Children[];
}

// ==========================================
// STATE MANAGEMENT
// ==========================================
const rawData = ref<Raw_Laporan[]>([]);
const searchQuery = ref('');
const isLoading = ref(false);
const showDetailModal = ref(false);
const selectedLaporan = ref<Selected_Laporan | null>(null);

const sortBy = ref<'created_at' | 'total_harga'>('created_at');
const sortOrder = ref<'asc' | 'desc'>('desc');

const modalDeleteAllLaporan = ref(false);
const confirmAllLaporan = ref('');
const isDeletingAll = ref(false);

// ==========================================
// DATA FETCHING & COMPUTED
// ==========================================

const fetchSemuaLaporan = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/laporan');
        rawData.value = response.data.data;
    } catch (err) {
        console.error('Gagal mengambil laporan:', err);
    } finally {
        isLoading.value = false;
    }
};

const daftarLaporan = computed<Laporan[]>(() => {
    const uniqueMap = new Map();
    rawData.value.forEach((item) => {
        if (!item.id_laporan) return;
        if (!uniqueMap.has(item.id_laporan)) {
            uniqueMap.set(item.id_laporan, {
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

const grupLaporan = computed(() => {
    const grup = rawData.value.reduce((acc: Record<string, Selected_Laporan>, item) => {
        const id = item.id_laporan;
        if (!acc[id]) {
            acc[id] = {
                id_transaksi: item.id_laporan,
                pelanggan: item.pelanggan,
                total_harga: item.total_harga,
                netdiskon: item.netdiskon,
                created_at: item.created_at,
                items: []
            };
        }
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

const filteredLaporan = computed(() => {
    let result = [...daftarLaporan.value];
    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(t =>
            t.pelanggan.toLowerCase().includes(q) ||
            t.id_transaksi.toLowerCase().includes(q)
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

const totalBulanan = computed(() => {
    if (!selectedLaporan.value?.items) return 0;
    return selectedLaporan.value.items.reduce((acc, item) => {
        const hargaRecurring = item.durasi > 0 ? (Number(item.produk_final) * item.jumlah) : 0;
        return acc + hargaRecurring;
    }, 0);
});

// ==========================================
// ACTIONS
// ==========================================

const fetchDetail = (id_transaksi: string) => {
    const ditemukan = grupLaporan.value.find((t) => t.id_transaksi === id_transaksi);
    if (ditemukan) {
        selectedLaporan.value = ditemukan;
        showDetailModal.value = true;
    } else {
        console.error("Data Laporan tidak ditemukan");
    }
};

const handleDelete = async (id: string) => {
    if (!confirm(`Hapus permanen laporan penawaran kode: ${id}?`)) return;
    try {
        await axios.delete(`/api/laporan/${id}`);
        rawData.value = rawData.value.filter(item => item.id_laporan !== id);
    } catch (err) {
        console.error(err);
        alert('Gagal menghapus laporan');
    }
};

const deleteAllLaporan = async () => {
    if (confirmAllLaporan.value !== '1234567890') return alert('Kode konfirmasi salah');

    isDeletingAll.value = true;
    try {
        await axios.delete('/api/laporan/delete-all-laporan');
        alert('Semua Laporan Penawaran Berhasil Dihapus');
        modalDeleteAllLaporan.value = false;
        confirmAllLaporan.value = '';
        fetchSemuaLaporan();
    } catch (error) {
        console.error(error);
        alert('Gagal menghapus semua laporan');
    } finally {
        isDeletingAll.value = false;
    }
};

const cekDeleteAllLaporan = () => {
    modalDeleteAllLaporan.value = true;
};

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

const formatCurrency = (val: number) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
const formatDate = (date: string) => new Date(date).toLocaleString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });

onMounted(fetchSemuaLaporan);
</script>

<template>
    <Sidebar>
        <div class="space-y-6 font-sans">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold tracking-tight text-gray-900">Laporan Penawaran</h2>
                    <p class="text-sm text-gray-500">Pantau dan kelola riwayat penawaran yang telah dibuat.</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <button
                        class="flex items-center gap-2 rounded-xl border bg-white px-4 py-2.5 text-sm font-medium shadow-sm hover:bg-gray-50 hover:shadow-md transition-all active:scale-95">
                        <Download class="h-4 w-4 text-gray-600" />
                        <span class="hidden sm:inline">Export CSV</span>
                    </button>
                    <button @click="cekDeleteAllLaporan"
                        class="flex items-center gap-2 rounded-xl border bg-white px-4 py-2.5 text-sm font-medium text-red-600 border-red-100 shadow-sm hover:bg-red-50 hover:border-red-200 transition-all active:scale-95">
                        <Trash2 class="h-4 w-4" />
                        <span class="hidden sm:inline">Hapus Semua</span>
                    </button>
                </div>
            </div>

            <div v-if="modalDeleteAllLaporan"
                class="fixed inset-0 z-[60] flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" @click.self="modalDeleteAllLaporan = false">
                <div class="w-full max-w-sm rounded-2xl bg-white p-6 shadow-2xl animate-in fade-in zoom-in duration-200 border-2 border-red-100">
                    <div class="flex items-center gap-3 mb-4 text-red-600">
                        <div class="p-2 bg-red-50 rounded-full"><AlertTriangle class="w-6 h-6"/></div>
                        <h3 class="text-lg font-bold">Konfirmasi Hapus Total</h3>
                    </div>
                    <label class="mb-3 block text-sm text-gray-600 leading-relaxed">
                        Tindakan ini akan menghapus <strong>seluruh data laporan</strong> secara permanen. Ketik <strong class="text-gray-900 font-mono bg-gray-100 px-1 rounded">1234567890</strong> untuk konfirmasi.
                    </label>
                    <input v-model="confirmAllLaporan" type="text"
                        class="w-full rounded-xl border border-gray-300 p-3 outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all text-center font-mono tracking-widest font-bold"
                        placeholder="Kode Konfirmasi" />
                    <div class="mt-6 flex gap-3">
                        <button @click="modalDeleteAllLaporan = false"
                            class="flex-1 rounded-xl border py-2.5 hover:bg-gray-50 font-medium text-gray-700">Batal</button>
                        <button @click="deleteAllLaporan" :disabled="isDeletingAll"
                            class="flex-1 rounded-xl bg-red-600 py-2.5 text-white hover:bg-red-700 font-medium flex justify-center items-center gap-2 disabled:opacity-70">
                            <Loader2 v-if="isDeletingAll" class="w-4 h-4 animate-spin"/>
                            {{ isDeletingAll ? 'Menghapus...' : 'Hapus Total' }}
                        </button>
                    </div>
                </div>
            </div>

            <div class="grid gap-4 rounded-xl border bg-white p-4 shadow-sm sm:flex sm:items-center sm:justify-between">
                <div class="relative flex-1 max-w-sm">
                    <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                    <input v-model="searchQuery" type="text" placeholder="Cari Pelanggan atau ID Laporan..."
                        class="w-full rounded-xl border border-gray-200 py-2.5 pl-10 pr-4 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" />
                </div>
                <div class="flex items-center gap-2">
                    <button @click="toggleSort('created_at')"
                        :class="['flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-all', sortBy === 'created_at' ? 'bg-blue-50 border-blue-200 text-blue-700 font-medium' : 'bg-white hover:bg-gray-50 text-gray-600']">
                        <component class="h-4 w-4" :is="getSortIcon('created_at')" />
                        Tanggal
                    </button>
                    <button @click="toggleSort('total_harga')"
                        :class="['flex items-center gap-2 rounded-lg border px-3 py-2 text-sm transition-all', sortBy === 'total_harga' ? 'bg-blue-50 border-blue-200 text-blue-700 font-medium' : 'bg-white hover:bg-gray-50 text-gray-600']">
                        <component class="h-4 w-4" :is="getSortIcon('total_harga')" />
                        Nominal
                    </button>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden flex flex-col min-h-[400px]">
                <div class="flex-1 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50/80 text-xs font-semibold uppercase tracking-wider text-gray-500 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 w-16 text-center">No</th>
                                <th class="px-6 py-4">ID Laporan</th>
                                <th class="px-6 py-4">Tanggal Dibuat</th>
                                <th class="px-6 py-4">Pelanggan</th>
                                <th class="px-6 py-4 text-right">Total Harga</th>
                                <th class="px-6 py-4 text-center w-32">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="isLoading">
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <Loader2 class="w-8 h-8 text-blue-600 animate-spin" />
                                        <span class="text-gray-500 font-medium">Memuat data...</span>
                                    </div>
                                </td>
                            </tr>
                            <tr v-else-if="filteredLaporan.length === 0">
                                <td colspan="6" class="px-6 py-20 text-center">
                                    <div class="flex flex-col items-center justify-center gap-3">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-1">
                                            <FileX class="w-8 h-8 text-gray-400" />
                                        </div>
                                        <p class="text-gray-900 font-medium text-lg">Data Laporan Kosong</p>
                                        <p class="text-gray-500 text-sm">Belum ada laporan yang dibuat atau ditemukan.</p>
                                    </div>
                                </td>
                            </tr>
                            <tr v-for="(trx, index) in filteredLaporan" :key="trx.id_transaksi"
                                class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-6 py-4 text-center text-gray-500 font-mono text-xs">{{ index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <span class="font-mono text-xs font-bold text-blue-600 bg-blue-50 px-2 py-1 rounded">{{ trx.id_transaksi }}</span>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <div class="flex items-center gap-2">
                                        <Calendar class="w-3.5 h-3.5 text-gray-400"/>
                                        {{ formatDate(trx.created_at) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-gray-900">{{ trx.pelanggan ? trx.pelanggan.toUpperCase() : '-' }}</div>
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900">{{ formatCurrency(trx.total_harga) }}</td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <button @click="fetchDetail(trx.id_transaksi)"
                                            class="p-2 rounded-lg text-gray-500 hover:text-blue-600 hover:bg-blue-50 transition-colors tooltip" title="Lihat Rincian">
                                            <Eye class="h-4 w-4" />
                                        </button>

                                        <Link :href="`/laporan/${trx.id_transaksi}/edit`"
                                            class="p-2 rounded-lg text-gray-500 hover:text-green-600 hover:bg-green-50 transition-colors tooltip" title="Edit">
                                            <Edit class="h-4 w-4" />
                                        </Link>

                                        <button @click="handleDelete(trx.id_transaksi)"
                                            class="p-2 rounded-lg text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors tooltip" title="Hapus Permanen">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="showDetailModal && selectedLaporan"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-sm p-4" @click.self="showDetailModal = false">
            <div class="w-full max-w-5xl rounded-2xl bg-white shadow-2xl max-h-[90vh] flex flex-col animate-in fade-in zoom-in duration-200 overflow-hidden">
                <div class="flex items-center justify-between border-b p-6 bg-gray-50/50">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">Rincian Penawaran</h3>
                        <div class="flex items-center gap-3 mt-1.5">
                            <span class="px-2.5 py-0.5 rounded bg-blue-100 text-blue-700 text-xs font-mono font-bold border border-blue-200">{{ selectedLaporan.id_transaksi }}</span>
                            <span class="text-sm font-medium text-gray-600">{{ selectedLaporan.pelanggan.toUpperCase() }}</span>
                            <span class="text-xs text-gray-400 flex items-center gap-1"><Calendar class="w-3 h-3"/> {{ formatDate(selectedLaporan.created_at) }}</span>
                        </div>
                    </div>
                    <button @click="showDetailModal = false" class="rounded-full p-2 hover:bg-gray-200 text-gray-400 hover:text-gray-600 transition-colors">
                        <X class="h-6 w-6" />
                    </button>
                </div>

                <div class="p-6 overflow-y-auto flex-1 bg-gray-50/30">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                            <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Total Penawaran (One-Time)</p>
                            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(selectedLaporan.total_harga) }}</p>
                        </div>
                        <div class="rounded-xl border border-blue-100 bg-blue-50/50 p-5 shadow-sm">
                            <p class="text-xs text-blue-600 uppercase font-bold tracking-wider mb-1">Estimasi Bulanan (Recurring)</p>
                            <p class="text-2xl font-bold text-blue-700">{{ formatCurrency(totalBulanan) }}</p>
                        </div>
                         <div class="rounded-xl border border-green-100 bg-green-50/50 p-5 shadow-sm">
                            <p class="text-xs text-green-600 uppercase font-bold tracking-wider mb-1">Diskon Agregat</p>
                            <p class="text-2xl font-bold text-green-700">{{ selectedLaporan.netdiskon }}%</p>
                        </div>
                    </div>

                    <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm">
                        <div class="max-h-[50vh] overflow-y-auto">
                            <table class="w-full text-xs text-left">
                                <thead class="bg-gray-100/80 text-gray-600 font-semibold sticky top-0 z-10 border-b border-gray-200">
                                    <tr>
                                        <th class="p-4 w-1/4">Produk</th>
                                        <th class="p-4 text-center">Qty</th>
                                        <th class="p-4 text-center">Durasi</th>
                                        <th class="p-4 text-right">Harga Satuan</th>
                                        <th class="p-4 text-right">OTC</th>
                                        <th class="p-4 text-center">Disc P(%)</th>
                                        <th class="p-4 text-center">Disc O(%)</th>
                                        <th class="p-4 text-right">Final P</th>
                                        <th class="p-4 text-right">Final O</th>
                                        <th class="p-4 text-right bg-blue-50/50 text-blue-800 border-l border-blue-100">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="item in selectedLaporan.items" :key="item.id_produk" class="hover:bg-gray-50 transition-colors">
                                        <td class="p-4 font-medium text-gray-900">{{ item.nama_produk }}</td>
                                        <td class="p-4 text-center">{{ item.jumlah }}</td>
                                        <td class="p-4 text-center">{{ item.durasi }} Bln</td>
                                        <td class="p-4 text-right text-gray-500">{{ formatCurrency(item.harga_produk) }}</td>
                                        <td class="p-4 text-right text-gray-500">{{ formatCurrency(item.harga_otc) }}</td>
                                        <td class="p-4 text-center text-red-500 font-medium">{{ item.diskon_produk }}%</td>
                                        <td class="p-4 text-center text-red-500 font-medium">{{ item.diskon_otc }}%</td>
                                        <td class="p-4 text-right font-medium">{{ formatCurrency(item.produk_final) }}</td>
                                        <td class="p-4 text-right font-medium">{{ formatCurrency(item.otc_final) }}</td>
                                        <td class="p-4 text-right font-bold text-blue-700 bg-blue-50/20 border-l border-blue-100">
                                            {{ formatCurrency(item.subtotal) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-200 p-4 flex justify-end bg-white">
                    <button @click="showDetailModal = false"
                        class="rounded-xl border border-gray-300 bg-white px-6 py-2.5 text-gray-700 hover:bg-gray-50 font-medium transition-colors shadow-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </Sidebar>
</template>

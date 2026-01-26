<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import axios from 'axios';
import {
    ArrowDown, ArrowUp, ArrowUpDown, Edit, Plus, Search, Trash2,
    X, Import, Trash, Loader2, PackageX, Save
} from 'lucide-vue-next'; // Menggunakan Import icon yang benar dari Lucide
import { computed, onMounted, ref } from 'vue';

// Interface untuk produk
interface Produk {
    id_produk: string;
    nama_produk: string;
    bandwidth: number;
    kategori: string;
    harga_produk: number;
    status: string;
    created_at?: string;
    updated_at?: string;
}

// url api
const apiUrl = '/api/produk';

// ref Reactive State
const produk = ref<Produk[]>([]);

// ref form tambah produk
const formData = ref({
    nama_produk: '',
    kategori: '',
    bandwidth: 0,
    harga_produk: 0,
    status: '',
});

// ref search produk
const searchQuery = ref('');
const searchBandwidth = ref('');
const filterKategori = ref<string>('all');

// ref modal
const showModal = ref(false);
const modalMode = ref<'add' | 'edit'>('add');
const selectedProduct = ref<Produk | null>(null);
const isLoading = ref(false);
const error = ref<string | null>(null);
const isSubmitting = ref(false); // [BARU] Untuk state loading tombol simpan

// ref sorting atau filter
const sortBy = ref<'created_at' | 'updated_at' | 'nama_produk'>('nama_produk');
const sortOrder = ref<'asc' | 'desc'>('asc');

// ref pagination
const currentPage = ref(1);
const itemsPerPage = 10;

// --- FUNCTIONS ---

const fetchProduk = async () => {
    isLoading.value = true;
    error.value = null;
    try {
        const response = await axios.get(apiUrl);
        produk.value = response.data.data;
    } catch (err) {
        console.error('Error fetching products:', err);
        error.value = 'Gagal mengambil data produk dari server.';
    } finally {
        isLoading.value = false;
    }
};

const filteredProduk = computed(() => {
    let result = produk.value;

    if (filterKategori.value !== 'all') {
        result = result.filter(
            (p) => p.kategori.toLowerCase() === filterKategori.value.toLowerCase()
        );
    }

    if (searchQuery.value) {
        const q = searchQuery.value.toLowerCase();
        result = result.filter(
            (p) => p.nama_produk.toLowerCase().includes(q) || p.kategori.toLowerCase().includes(q)
        );
    }

    if (searchBandwidth.value) {
        const bq = searchBandwidth.value.toString();
        result = result.filter(
            (p) => p.bandwidth && p.bandwidth.toString().includes(bq)
        );
    }

    result = [...result].sort((a, b) => {
        const fieldA = a[sortBy.value];
        const fieldB = b[sortBy.value];

        if (!fieldA) return 1;
        if (!fieldB) return -1;

        if (sortBy.value === 'nama_produk') {
            const strA = String(fieldA).toLowerCase();
            const strB = String(fieldB).toLowerCase();
            return sortOrder.value === 'asc'
                ? strA.localeCompare(strB)
                : strB.localeCompare(strA);
        }

        const dateA = new Date(fieldA as string).getTime();
        const dateB = new Date(fieldB as string).getTime();
        return sortOrder.value === 'asc' ? dateA - dateB : dateB - dateA;
    });

    return result;
});

const paginatedProducts = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredProduk.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredProduk.value.length / itemsPerPage);
});

const toggleSort = (field: 'created_at' | 'updated_at' | 'nama_produk') => {
    if (sortBy.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortOrder.value = field === 'nama_produk' ? 'asc' : 'desc';
    }
    currentPage.value = 1;
};

const getSortIcon = (field: 'created_at' | 'updated_at' | 'nama_produk') => {
    if (sortBy.value !== field) return ArrowUpDown;
    return sortOrder.value === 'asc' ? ArrowUp : ArrowDown;
};

// CRUD OPERATIONS
const openAddModal = () => {
    modalMode.value = 'add';
    formData.value = {
        nama_produk: '',
        kategori: '',
        bandwidth: 0,
        harga_produk: 0,
        status: 'active',
    };
    showModal.value = true;
};

const openEditModal = (product: Produk) => {
    modalMode.value = 'edit';
    selectedProduct.value = product;
    formData.value = {
        nama_produk: product.nama_produk,
        kategori: product.kategori,
        bandwidth: product.bandwidth,
        harga_produk: product.harga_produk,
        status: product.status,
    };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedProduct.value = null;
};

const saveProduct = async () => {
    if (!formData.value.nama_produk) return alert('Nama produk wajib diisi');

    isSubmitting.value = true;
    try {
        if (modalMode.value === 'add') {
            await axios.post(apiUrl, formData.value);
            // Alert opsional, bisa diganti toast notification
        } else {
            const id = selectedProduct.value?.id_produk;
            if (id) {
                await axios.put(`${apiUrl}/${id}`, formData.value);
            }
        }
        closeModal();
        await fetchProduk();
        alert(modalMode.value === 'add' ? 'Produk berhasil ditambahkan!' : 'Produk berhasil diperbarui!');
    } catch (err) {
        console.error('Error saving product:', err);
        alert('Gagal menyimpan produk. Periksa konsol untuk detail.');
    } finally {
        isSubmitting.value = false;
    }
};

const deleteProduct = async (id: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan!')) {
        try {
            await axios.delete(`${apiUrl}/${id}`);
            // alert('Produk berhasil dihapus!'); // Opsional, agar tidak terlalu banyak popup
            produk.value = produk.value.filter((p) => p.id_produk !== id);
            if (paginatedProducts.value.length === 0 && currentPage.value > 1) {
                currentPage.value--;
            }
        } catch (err) {
            console.error('Error deleting product:', err);
            alert('Gagal menghapus produk');
        }
    }
};

const deleteAllProduk = async () => {
    if (!confirm('PERINGATAN: Anda yakin ingin menghapus SEMUA produk? \n\nTindakan ini SANGAT BERISIKO dan tidak dapat dibatalkan.')) {
        return;
    }

    isUploading.value = true;
    try {
        await axios.delete(`${apiUrl}/delete-all-produk`);
        alert('Semua produk berhasil dihapus.');
        fetchProduk();
    } catch (err: any) {
        console.error('Error deleting all products:', err);
        const msg = err.response?.data?.message || 'Gagal menghapus semua produk.';
        alert(msg);
    } finally {
        isUploading.value = false;
    }
};

const ruleInputAngka = (event: Event) => {
    const target = event.target as HTMLInputElement;
    let val = target.value.replace(/\D/g, '')
    target.value = val
    searchBandwidth.value = val;
}

// CSV IMPORT
const fileInput = ref<HTMLInputElement | null>(null);
const isUploading = ref(false);

const triggerFileInput = () => {
    fileInput.value?.click();
};

const handleFileUpload = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];

        if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
            alert('Harap unggah file berformat CSV.');
            return;
        }

        const formData = new FormData();
        formData.append('file_csv', file);

        isUploading.value = true;
        try {
            const response = await axios.post(`${apiUrl}/import`, formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });
            alert(response.data.alert)
            fetchProduk();
        } catch (err: any) {
            console.error('Upload error:', err);
            const msg = err.response?.data?.message || 'Gagal mengunggah CSV.';
            alert(msg);
        } finally {
            isUploading.value = false;
            if (fileInput.value) fileInput.value.value = '';
        }
    }
};

const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString?: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID', {
        day: '2-digit', month: 'long', year: 'numeric',
        hour: '2-digit', minute: '2-digit',
    });
};

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

onMounted(() => {
    fetchProduk();
});
</script>

<template>
    <Sidebar>
        <div class="space-y-6 font-sans">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Manajemen Produk</h2>
                    <p class="text-sm text-gray-500">Kelola katalog produk, harga, dan bandwidth.</p>
                </div>

                <div class="flex flex-wrap gap-3 w-full sm:w-auto">
                    <button @click="openAddModal"
                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-white hover:bg-blue-700 transition-all shadow-lg shadow-blue-500/25 active:scale-95">
                        <Plus class="h-5 w-5" />
                        <span class="font-medium">Tambah</span>
                    </button>

                    <button @click="triggerFileInput" :disabled="isUploading"
                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 rounded-xl bg-emerald-600 px-4 py-2.5 text-white hover:bg-emerald-700 transition-all shadow-lg shadow-emerald-500/25 active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed">
                        <Loader2 v-if="isUploading" class="h-4 w-4 animate-spin" />
                        <Import v-else class="h-4 w-4" />
                        <span class="font-medium">Impor CSV</span>
                    </button>
                    <input type="file" ref="fileInput" class="hidden" accept=".csv" @change="handleFileUpload" />

                    <button @click="deleteAllProduk"
                        class="flex-1 sm:flex-none flex items-center justify-center gap-2 rounded-xl border border-red-200 bg-white px-4 py-2.5 text-red-600 hover:bg-red-50 hover:border-red-300 transition-all active:scale-95"
                        title="Hapus Semua Produk">
                        <Trash class="h-4 w-4" />
                        <span class="font-medium">Hapus Semua Produk</span>
                    </button>
                </div>
            </div>

            <div class="rounded-xl border bg-white p-5 shadow-sm space-y-5">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="relative">
                        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <input v-model="searchQuery" type="text" placeholder="Cari nama produk..."
                            class="w-full rounded-lg border border-gray-200 py-2.5 pl-10 pr-4 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" />
                    </div>
                    <div class="relative">
                        <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-gray-400" />
                        <input v-model.number="searchBandwidth" @input="ruleInputAngka($event)" type="text"
                            placeholder="Cari Bandwidth..."
                            class="w-full rounded-lg border border-gray-200 py-2.5 pl-10 pr-4 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all" />
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 pt-2 border-t border-gray-100">
                    <div class="flex items-center gap-2 overflow-x-auto w-full pb-2 sm:pb-0">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Kategori:</span>
                        <button v-for="cat in ['all', 'astinet', 'indibiz']" :key="cat"
                            @click="filterKategori = cat; currentPage = 1"
                            :class="[
                                'rounded-full px-3 py-1 text-xs font-medium transition-all capitalize border',
                                filterKategori === cat
                                    ? 'border-blue-600 bg-blue-50 text-blue-700'
                                    : 'border-gray-200 bg-white text-gray-600 hover:bg-gray-50'
                            ]">
                            {{ cat === 'all' ? 'Semua' : cat }}
                        </button>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:block">Urutkan:</span>
                        <div class="flex gap-1">
                            <button @click="toggleSort('nama_produk')"
                                :class="['p-2 rounded-lg border transition-colors', sortBy === 'nama_produk' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white border-gray-200 hover:bg-gray-50']" title="Nama Produk">
                                <component :is="getSortIcon('nama_produk')" class="h-4 w-4" />
                            </button>
                            <button @click="toggleSort('created_at')"
                                :class="['p-2 rounded-lg border transition-colors', sortBy === 'created_at' ? 'bg-blue-50 border-blue-200 text-blue-700' : 'bg-white border-gray-200 hover:bg-gray-50']" title="Tanggal Dibuat">
                                <component :is="getSortIcon('created_at')" class="h-4 w-4" />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden min-h-[400px] flex flex-col">

                <div v-if="isLoading" class="flex-1 flex flex-col items-center justify-center py-20">
                    <Loader2 class="h-10 w-10 text-blue-600 animate-spin mb-3" />
                    <p class="font-medium text-gray-500">Memuat data produk...</p>
                </div>

                <div v-else-if="error" class="m-6 rounded-xl border border-red-200 bg-red-50 p-4 text-red-700 text-sm flex items-center gap-3">
                    <div class="w-2 h-2 rounded-full bg-red-500"></div>
                    <strong>Error:</strong> {{ error }}
                </div>

                <div v-else-if="filteredProduk.length === 0" class="flex-1 flex flex-col items-center justify-center py-20 text-center">
                    <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-3">
                        <PackageX class="w-8 h-8 text-gray-400" />
                    </div>
                    <h3 class="text-lg font-medium text-gray-900">Produk Tidak Ditemukan</h3>
                    <p class="text-sm text-gray-500 mt-1">Coba ubah filter atau kata kunci pencarian Anda.</p>
                </div>

                <div v-else class="flex-1 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50/80 text-xs font-semibold uppercase tracking-wider text-gray-500 border-b border-gray-100">
                            <tr>
                                <th class="px-6 py-4 w-16 text-center">No</th>
                                <th class="px-6 py-4">Nama Produk</th>
                                <th class="px-6 py-4">Bandwidth</th>
                                <th class="px-6 py-4">Kategori</th>
                                <th class="px-6 py-4 text-right">Harga</th>
                                <th class="px-6 py-4 text-center">Status</th>
                                <th class="px-6 py-4 text-center w-24">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-for="(prod, index) in paginatedProducts" :key="prod.id_produk"
                                class="hover:bg-blue-50/30 transition-colors group">
                                <td class="px-6 py-4 text-center font-mono text-gray-500 text-xs">
                                    {{ (currentPage - 1) * itemsPerPage + index + 1 }}
                                </td>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ prod.nama_produk.toUpperCase() }}
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <span v-if="prod.bandwidth > 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700">
                                        {{ prod.bandwidth }} Mbps
                                    </span>
                                    <span v-else class="text-gray-400">-</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="[
                                        'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border capitalize',
                                        prod.kategori.toLowerCase() === 'astinet' ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-purple-50 text-purple-700 border-purple-100'
                                    ]">
                                        {{ prod.kategori }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium">
                                    {{ formatCurrency(prod.harga_produk) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <span :class="[
                                        'inline-flex items-center px-2 py-1 rounded-full text-xs font-semibold',
                                        prod.status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600'
                                    ]">
                                        {{ prod.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        <button @click="openEditModal(prod)"
                                            class="p-2 rounded-lg text-blue-600 bg-blue-50 hover:bg-blue-100 transition-colors" title="Edit">
                                            <Edit class="h-4 w-4" />
                                        </button>
                                        <button @click="deleteProduct(prod.id_produk)"
                                            class="p-2 rounded-lg text-red-600 bg-red-50 hover:bg-red-100 transition-colors" title="Hapus">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="filteredProduk.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    <div class="text-xs text-gray-500">
                        Menampilkan <span class="font-medium text-gray-900">{{ (currentPage - 1) * itemsPerPage + 1 }}</span> -
                        <span class="font-medium text-gray-900">{{ Math.min(currentPage * itemsPerPage, filteredProduk.length) }}</span>
                        dari <span class="font-medium text-gray-900">{{ filteredProduk.length }}</span> data
                    </div>

                    <div class="flex items-center gap-1">
                        <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                            class="p-2 rounded-lg border bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span class="sr-only">Previous</span>
                            <ArrowUpDown class="h-4 w-4 rotate-90" /> </button>

                        <div class="flex items-center gap-1 px-2">
                            <button v-for="page in totalPages" :key="page" @click="goToPage(page)"
                                v-show="page === 1 || page === totalPages || (page >= currentPage - 1 && page <= currentPage + 1)"
                                :class="[
                                    'w-8 h-8 rounded-lg text-xs font-medium transition-colors flex items-center justify-center',
                                    currentPage === page ? 'bg-blue-600 text-white' : 'hover:bg-gray-100 text-gray-600'
                                ]">
                                {{ page }}
                            </button>
                        </div>

                        <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                            class="p-2 rounded-lg border bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            <span class="sr-only">Next</span>
                            <ArrowUpDown class="h-4 w-4 -rotate-90" /> </button>
                    </div>
                </div>
            </div>
        </div>

        <div v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 backdrop-blur-sm p-4 animate-in fade-in duration-200"
            @click.self="closeModal">

            <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl overflow-hidden transform transition-all scale-100">
                <div class="flex items-center justify-between border-b p-5 bg-gray-50/50">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ modalMode === 'add' ? 'Tambah Produk Baru' : 'Edit Produk' }}
                    </h3>
                    <button @click="closeModal" class="rounded-lg p-2 text-gray-400 hover:bg-gray-200 transition-colors">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="p-6 space-y-5">
                    <div v-if="modalMode === 'edit' && selectedProduct"
                        class="rounded-xl bg-blue-50 p-4 border border-blue-100">
                        <div class="flex items-center gap-2 mb-2 text-blue-800 font-semibold text-xs uppercase tracking-wide">
                            <Edit class="w-3 h-3" /> Info Perubahan
                        </div>
                        <div class="space-y-1 text-xs text-blue-600">
                            <div class="flex justify-between"><span>Dibuat:</span> <span>{{ formatDate(selectedProduct.created_at) }}</span></div>
                            <div class="flex justify-between"><span>Terakhir Diubah:</span> <span>{{ formatDate(selectedProduct.updated_at) }}</span></div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-gray-500 uppercase">Nama Produk <span class="text-red-500">*</span></label>
                            <input v-model="formData.nama_produk" type="text"
                                class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
                                placeholder="Contoh: Astinet 100Mbps" />
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-500 uppercase">Kategori</label>
                                <select v-model="formData.kategori"
                                    class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all bg-white">
                                    <option value="" disabled>Pilih</option>
                                    <option value="astinet">Astinet</option>
                                    <option value="indibiz">Indibiz</option>
                                    <option value="wifi">Wifi Station</option>
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-bold text-gray-500 uppercase">Bandwidth</label>
                                <div class="relative">
                                    <input v-model.number="formData.bandwidth" type="number"
                                        class="w-full rounded-xl border border-gray-300 px-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all pr-8"
                                        placeholder="0" />
                                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-bold">M</span>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-gray-500 uppercase">Harga Dasar</label>
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-500 font-bold">Rp</span>
                                <input v-model.number="formData.harga_produk" type="number"
                                    class="w-full rounded-xl border border-gray-300 pl-8 pr-3 py-2.5 text-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 outline-none transition-all"
                                    placeholder="0" />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1.5 block text-xs font-bold text-gray-500 uppercase">Status</label>
                            <div class="flex gap-4">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="formData.status" value="active" class="text-blue-600 focus:ring-blue-500" />
                                    <span class="text-sm text-gray-700">Aktif</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" v-model="formData.status" value="inactive" class="text-blue-600 focus:ring-blue-500" />
                                    <span class="text-sm text-gray-700">Nonaktif</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button @click="closeModal"
                            class="flex-1 rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                            Batal
                        </button>
                        <button @click="saveProduct" :disabled="isSubmitting"
                            class="flex-1 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700 transition-colors flex items-center justify-center gap-2 disabled:opacity-70 disabled:cursor-not-allowed">
                            <Loader2 v-if="isSubmitting" class="h-4 w-4 animate-spin" />
                            <Save v-else class="h-4 w-4" />
                            <span>{{ modalMode === 'add' ? 'Tambah' : 'Simpan' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
</template>

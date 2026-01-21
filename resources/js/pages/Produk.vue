<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import axios from 'axios';
import { ArrowDown, ArrowUp, ArrowUpDown, Edit, Plus, Search, Trash2, X, ImportIcon, DeleteIcon, Trash2Icon } from 'lucide-vue-next';
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
// ref produk
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

// ref sorting atau filter
const sortBy = ref<'created_at' | 'updated_at' | 'nama_produk'>('nama_produk');
const sortOrder = ref<'asc' | 'desc'>('asc');

// ref pagination
const currentPage = ref(1);
const itemsPerPage = 10;

// Semua function atau fungsi 
// fungsi ambil data produk 
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

    // 1. Filter Kategori
    if (filterKategori.value !== 'all') {
        result = result.filter(
            (p) => p.kategori.toLowerCase() === filterKategori.value.toLowerCase()
        );
    }

    // 2. Filter Search
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

    // 3. Sorting
    result = [...result].sort((a, b) => {
        const fieldA = a[sortBy.value];
        const fieldB = b[sortBy.value];

        if (!fieldA) return 1;
        if (!fieldB) return -1;

        // Jika sorting berdasarkan NAMA (String)
        if (sortBy.value === 'nama_produk') {
            const strA = String(fieldA).toLowerCase();
            const strB = String(fieldB).toLowerCase();
            return sortOrder.value === 'asc'
                ? strA.localeCompare(strB)
                : strB.localeCompare(strA);
        }

        // Jika sorting berdasarkan TANGGAL (Date)
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

// FUNGSI SORTING
const toggleSort = (field: 'created_at' | 'updated_at' | 'nama_produk') => {
    if (sortBy.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortOrder.value = field === 'nama_produk' ? 'asc' : 'desc'; // Nama default A-Z, Tanggal default Terbaru
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
    try {
        if (modalMode.value === 'add') {
            await axios.post(apiUrl, formData.value);
            alert('Produk berhasil ditambahkan!');
        } else {
            const id = selectedProduct.value?.id_produk;
            if (id) {
                await axios.put(`${apiUrl}/${id}`, formData.value);
                alert('Produk berhasil diperbarui!');
            }
        }
        closeModal();
        await fetchProduk();
    } catch (err) {
        console.error('Error saving product:', err);
        alert('Gagal menyimpan produk. Periksa konsol untuk detail.');
    }
};

const deleteProduct = async (id: string) => {
    if (confirm('Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan!')) {
        try {
            await axios.delete(`${apiUrl}/${id}`);
            alert('Produk berhasil dihapus!');
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
    // Memberikan peringatan konfirmasi sebelum menghapus
    if (!confirm('Apakah Anda yakin ingin menghapus SEMUA produk? Tindakan ini tidak dapat dibatalkan.')) {
        return;
    }

    isUploading.value = true; // Gunakan loading state yang sudah ada
    try {
        await axios.delete(`${apiUrl}/delete-all-produk`);

        alert('Semua produk berhasil dihapus.');

        // Panggil fungsi untuk refresh tabel (misal fetchProduk)
        if (typeof fetchProduk === 'function') {
            fetchProduk();
        }
    } catch (err: any) {
        console.error('Error deleting all products:', err);
        const msg = err.response?.data?.message || 'Gagal menghapus semua produk.';
        alert(msg);
    } finally {
        isUploading.value = false;
    }
};

const ruleInputAngka = (event: any) => {
    let val = event.target.value.replace(/\D/g, '')
    event.target.value = val
    searchBandwidth.value = val;
}

// function import csv ke database
// ==========================
// --- CSV ---
const fileInput = ref<HTMLInputElement | null>(null);
const isUploading = ref(false);

// 1. Trigger klik input file hidden
const triggerFileInput = () => {
    fileInput.value?.click();
};

// 2. Handle saat file dipilih
const handleFileUpload = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        const file = target.files[0];

        // Validasi tipe file sederhana di frontend
        if (file.type !== 'text/csv' && !file.name.endsWith('.csv')) {
            alert('Harap unggah file berformat CSV.');
            return;
        }

        const formData = new FormData();
        formData.append('file_csv', file);

        isUploading.value = true;
        try {
            const response = await axios.post(`${apiUrl}/import`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            alert(response.data.alert)
            fetchProduk(); // Refresh data table
        } catch (err: any) {
            console.error('Upload error:', err);
            const msg = err.response?.data?.message || 'Gagal mengunggah CSV.';
            alert(msg);
        } finally {
            isUploading.value = false;
            // Reset input value agar bisa upload file yang sama jika perlu
            if (fileInput.value) fileInput.value.value = '';
        }
    }
};


// function format rupiah
const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

// function format tanggal
const formatDate = (dateString?: string) => {
    if (!dateString) return '-';
    return new Date(dateString).toLocaleString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Function pagination
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
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold">Manajemen Produk</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Kelola semua produk Anda di sini</p>
                </div>
                <div class="flex space-x-3">

                    <!-- Button Tambah -->
                    <button @click="openAddModal"
                        class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700">
                        <Plus class="h-4 w-4" />
                        <span class="font-medium">Tambah Produk</span>
                    </button>

                    <div>
                        <!-- Button Import -->
                        <button @click="triggerFileInput" :disabled="isUploading"
                            class="flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2 text-white transition-colors hover:bg-green-700">
                            <ImportIcon class="h-4 w-4" />
                            <span class="font-medium">{{ 'Impor Data Produk' }}</span>
                        </button>
                        <input type="file" ref="fileInput" class="hidden" accept=".csv" @change="handleFileUpload" />
                    </div>

                    <!-- Button Hapus Semua Produk -->
                    <button @click="deleteAllProduk"
                        class="flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-white transition-colors hover:bg-red-700">
                        <Trash2Icon class="h-4 w-4" />
                        <span class="font-medium">Hapus Semua Produk</span>
                    </button>


                </div>
            </div>

            <!-- Search Bar & Sort Controls -->
            <div class="space-y-4 rounded-lg border bg-card p-6">
                <div class="relative space-x-3">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model="searchQuery" type="text" placeholder="Cari produk..."
                        class="w-[30%] rounded-lg border-[2px] py-2 pr-4 pl-10 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                </div>
                <div class="relative space-x-3">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input v-model.number="searchBandwidth" @input="ruleInputAngka($event)" type="text"
                        placeholder="Cari Bandwidth..."
                        class="w-[20%] rounded-lg border-[2px] py-2 pr-4 pl-10 focus:ring-2 focus:ring-blue-500 focus:outline-none" />
                </div>

                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-medium text-muted-foreground">Jenis:</span>
                        <button v-for="cat in ['all', 'astinet', 'indibiz']" :key="cat"
                            @click="filterKategori = cat; currentPage = 1" :class="[
                                'rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors capitalize',
                                filterKategori === cat
                                    ? 'border-blue-600 bg-blue-50 text-blue-700'
                                    : 'border-gray-300 bg-white hover:bg-muted',
                            ]">
                            {{ cat === 'all' ? 'Semua' : cat }}
                        </button>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <span class="text-sm font-medium text-muted-foreground">Urutkan:</span>
                        <button @click="toggleSort('nama_produk')" :class="[
                            'flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm transition-colors',
                            sortBy === 'nama_produk' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-300 bg-white'
                        ]">
                            <component :is="getSortIcon('nama_produk')" class="h-4 w-4" />
                            <span>Nama Produk</span>
                        </button>
                        <button @click="toggleSort('created_at')" :class="[
                            'flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm transition-colors',
                            sortBy === 'created_at' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-300 bg-white',
                        ]">
                            <component :is="getSortIcon('created_at')" class="h-4 w-4" />
                            <span>Dibuat</span>
                        </button>
                        <button @click="toggleSort('updated_at')" :class="[
                            'flex items-center gap-2 rounded-lg border px-3 py-1.5 text-sm transition-colors',
                            sortBy === 'updated_at' ? 'border-blue-600 bg-blue-50 text-blue-700' : 'border-gray-300 bg-white',
                        ]">
                            <component :is="getSortIcon('updated_at')" class="h-4 w-4" />
                            <span>Diubah</span>
                        </button>
                    </div>
                </div>
            </div>

            <div v-if="isLoading" class="py-10 text-center">
                <p class="font-semibold text-blue-600">Memuat data produk...</p>
            </div>
            <div v-else-if="error" class="relative rounded border border-red-400 bg-red-100 px-4 py-3 text-red-700"
                role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline"> {{ error }}</span>
            </div>
            <div v-else-if="filteredProduk.length === 0" class="rounded-lg border bg-card py-10 text-center">
                <p class="text-muted-foreground">Tidak ada produk ditemukan.</p>
            </div>

            <!-- Table -->
            <div v-else class="overflow-hidden rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    No</th>

                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Nama Produk</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Bandwidth</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Kategori</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Harga</th>

                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Aksi</th>
                            </tr>
                        </thead>

                        <!-- Pagination -->
                        <tbody class="divide-y">
                            <tr v-for="(produk, index) in paginatedProducts" :key="produk.id_produk"
                                class="transition-colors hover:bg-muted/30">
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">{{ index + 1 }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    {{ produk.nama_produk.toUpperCase() }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">{{ produk.bandwidth }} Mbps
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap text-muted-foreground">
                                    {{ produk.kategori.toUpperCase() }}
                                </td>
                                <td class="px-6 py-4 text-sm font-medium whitespace-nowrap">
                                    {{ formatCurrency(produk.harga_produk) }}
                                </td>

                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <span :class="produk.status === 'active'
                                        ? 'rounded-full bg-green-100 px-2 py-1 text-xs font-semibold text-green-800'
                                        : 'rounded-full bg-gray-100 px-2 py-1 text-xs font-semibold text-gray-800'
                                        ">
                                        {{ produk.status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <button @click="openEditModal(produk)"
                                            class="rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50"
                                            title="Edit">
                                            <Edit class="h-4 w-4" />
                                        </button>
                                        <button @click="deleteProduct(produk.id_produk)"
                                            class="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50"
                                            title="Hapus">
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col items-center justify-between gap-4 border-t px-6 py-4 sm:flex-row">
                    <div class="text-sm text-muted-foreground">
                        Menampilkan {{ (currentPage - 1) * itemsPerPage + 1 }} -
                        {{ Math.min(currentPage * itemsPerPage, filteredProduk.length) }}
                        dari {{ filteredProduk.length }} produk
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="goToPage(currentPage - 1)" :disabled="currentPage === 1"
                            class="rounded-lg border px-3 py-1 transition-colors hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50">
                            Previous
                        </button>

                        <button v-for="page in totalPages" :key="page" @click="goToPage(page)" :class="[
                            'rounded-lg border px-3 py-1 transition-colors',
                            currentPage === page ? 'border-blue-600 bg-blue-600 text-white' : 'hover:bg-muted',
                        ]">
                            {{ page }}
                        </button>

                        <button @click="goToPage(currentPage + 1)" :disabled="currentPage === totalPages"
                            class="rounded-lg border px-3 py-1 transition-colors hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50">
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal dengan Info Timestamp di Mode Edit -->
        <div v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-2 sm:p-4"
            @click.self="closeModal">
            <div
                class="my-auto flex max-h-[95vh] w-full max-w-[95vw] flex-col rounded-lg bg-white shadow-xl sm:max-w-md">
                <div class="flex shrink-0 items-center justify-between border-b p-4 sm:p-6">
                    <h3 class="text-base font-semibold sm:text-lg">
                        {{ modalMode === 'add' ? 'Tambah Produk Baru' : 'Edit Produk' }}
                    </h3>
                    <button @click="closeModal" class="rounded-lg p-1 transition-colors hover:bg-muted">
                        <X class="h-4 w-4 sm:h-5 sm:w-5" />
                    </button>
                </div>

                <div class="flex-1 space-y-3 overflow-y-auto p-4 sm:space-y-4 sm:p-6">
                    <!-- Info Timestamp - HANYA MUNCUL SAAT EDIT -->
                    <div v-if="modalMode === 'edit' && selectedProduct"
                        class="rounded-lg border border-blue-200 bg-blue-50 p-3 sm:p-4">
                        <p class="mb-2 text-xs font-semibold text-blue-900 sm:text-sm">📋 Informasi Produk</p>
                        <div class="space-y-1.5 text-xs text-blue-700 sm:text-sm">
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-medium">Dibuat:</span>
                                <span class="text-right">{{ formatDate(selectedProduct.created_at) }}</span>
                            </div>
                            <div class="flex items-start justify-between gap-2">
                                <span class="font-medium">Terakhir Diubah:</span>
                                <span class="text-right">{{ formatDate(selectedProduct.updated_at) }}</span>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Nama Produk</label>
                        <input v-model="formData.nama_produk" type="text"
                            class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                            placeholder="Masukkan nama produk" />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Kategori</label>
                        <input v-model="formData.kategori" type="text"
                            class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                            placeholder="Masukkan kategori" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Bandwidth</label>
                        <input v-model.number="formData.bandwidth" type="number"
                            class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                            placeholder="Masukkan Bandwidth" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Harga</label>
                        <input v-model.number="formData.harga_produk" type="number"
                            class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                            placeholder="Masukkan harga" />
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Status</label>
                        <select v-model="formData.status"
                            class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base">
                            <option value="active">Aktif</option>
                            <option value="inactive">Nonaktif</option>
                        </select>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-2 border-t p-4 sm:gap-3 sm:p-6">
                    <button @click="closeModal"
                        class="flex-1 rounded-lg border px-3 py-1.5 text-sm transition-colors hover:bg-muted sm:px-4 sm:py-2 sm:text-base">
                        Batal
                    </button>
                    <button @click="saveProduct"
                        class="flex-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm text-white transition-colors hover:bg-blue-700 sm:px-4 sm:py-2 sm:text-base">
                        {{ modalMode === 'add' ? 'Tambah' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </Sidebar>
</template>

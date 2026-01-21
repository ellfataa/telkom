<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import { Download, Edit, Plus, Search, Trash2, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Interface untuk transaksi
interface Transaction {
    id: number;
    kode_transaksi: string;
    tanggal: string;
    pelanggan: string;
    subtotal: number;
    diskon: number;
    total: number;
    status: 'lunas' | 'pending' | 'dibatalkan';
}

// Data dummy transaksi
const transactions = ref<Transaction[]>([
    {
        id: 1,
        kode_transaksi: 'TRX-2024-001',
        tanggal: '2024-12-01',
        pelanggan: 'Ahmad Yani',
        subtotal: 5000000,
        diskon: 250000,
        total: 4750000,
        status: 'lunas',
    },
    {
        id: 2,
        kode_transaksi: 'TRX-2024-002',
        tanggal: '2024-12-01',
        pelanggan: 'Siti Nurhaliza',
        subtotal: 3500000,
        diskon: 175000,
        total: 3325000,
        status: 'lunas',
    },
    {
        id: 3,
        kode_transaksi: 'TRX-2024-003',
        tanggal: '2024-12-02',
        pelanggan: 'Budi Santoso',
        subtotal: 8200000,
        diskon: 820000,
        total: 7380000,
        status: 'pending',
    },
    {
        id: 4,
        kode_transaksi: 'TRX-2024-004',
        tanggal: '2024-12-02',
        pelanggan: 'Dewi Lestari',
        subtotal: 2800000,
        diskon: 0,
        total: 2800000,
        status: 'lunas',
    },
    {
        id: 5,
        kode_transaksi: 'TRX-2024-005',
        tanggal: '2024-12-03',
        pelanggan: 'Eko Prasetyo',
        subtotal: 6500000,
        diskon: 325000,
        total: 6175000,
        status: 'lunas',
    },
    {
        id: 6,
        kode_transaksi: 'TRX-2024-006',
        tanggal: '2024-12-03',
        pelanggan: 'Fitri Handayani',
        subtotal: 4200000,
        diskon: 210000,
        total: 3990000,
        status: 'dibatalkan',
    },
    {
        id: 7,
        kode_transaksi: 'TRX-2024-007',
        tanggal: '2024-12-04',
        pelanggan: 'Gunawan Susilo',
        subtotal: 9800000,
        diskon: 980000,
        total: 8820000,
        status: 'lunas',
    },
    {
        id: 8,
        kode_transaksi: 'TRX-2024-008',
        tanggal: '2024-12-04',
        pelanggan: 'Hendra Wijaya',
        subtotal: 3100000,
        diskon: 155000,
        total: 2945000,
        status: 'pending',
    },
    {
        id: 9,
        kode_transaksi: 'TRX-2024-009',
        tanggal: '2024-12-05',
        pelanggan: 'Indah Permata',
        subtotal: 5500000,
        diskon: 275000,
        total: 5225000,
        status: 'lunas',
    },
    {
        id: 10,
        kode_transaksi: 'TRX-2024-010',
        tanggal: '2024-12-05',
        pelanggan: 'Joko Widodo',
        subtotal: 7200000,
        diskon: 360000,
        total: 6840000,
        status: 'lunas',
    },
    {
        id: 11,
        kode_transaksi: 'TRX-2024-011',
        tanggal: '2024-12-06',
        pelanggan: 'Kartika Sari',
        subtotal: 4800000,
        diskon: 240000,
        total: 4560000,
        status: 'lunas',
    },
    {
        id: 12,
        kode_transaksi: 'TRX-2024-012',
        tanggal: '2024-12-06',
        pelanggan: 'Linda Agustin',
        subtotal: 6300000,
        diskon: 315000,
        total: 5985000,
        status: 'pending',
    },
    {
        id: 13,
        kode_transaksi: 'TRX-2024-013',
        tanggal: '2024-12-07',
        pelanggan: 'Made Suarta',
        subtotal: 3900000,
        diskon: 195000,
        total: 3705000,
        status: 'lunas',
    },
    {
        id: 14,
        kode_transaksi: 'TRX-2024-014',
        tanggal: '2024-12-07',
        pelanggan: 'Nadia Putri',
        subtotal: 8500000,
        diskon: 425000,
        total: 8075000,
        status: 'lunas',
    },
    {
        id: 15,
        kode_transaksi: 'TRX-2024-015',
        tanggal: '2024-12-08',
        pelanggan: 'Oscar Lawalata',
        subtotal: 2900000,
        diskon: 0,
        total: 2900000,
        status: 'dibatalkan',
    },
    {
        id: 16,
        kode_transaksi: 'TRX-2024-016',
        tanggal: '2024-12-08',
        pelanggan: 'Putri Marino',
        subtotal: 7800000,
        diskon: 390000,
        total: 7410000,
        status: 'lunas',
    },
    {
        id: 17,
        kode_transaksi: 'TRX-2024-017',
        tanggal: '2024-12-09',
        pelanggan: 'Qori Sandioriva',
        subtotal: 5200000,
        diskon: 260000,
        total: 4940000,
        status: 'lunas',
    },
    {
        id: 18,
        kode_transaksi: 'TRX-2024-018',
        tanggal: '2024-12-09',
        pelanggan: 'Rina Nose',
        subtotal: 4500000,
        diskon: 225000,
        total: 4275000,
        status: 'pending',
    },
    {
        id: 19,
        kode_transaksi: 'TRX-2024-019',
        tanggal: '2024-12-10',
        pelanggan: 'Surya Sahetapy',
        subtotal: 9200000,
        diskon: 460000,
        total: 8740000,
        status: 'lunas',
    },
    {
        id: 20,
        kode_transaksi: 'TRX-2024-020',
        tanggal: '2024-12-10',
        pelanggan: 'Titi Kamal',
        subtotal: 6700000,
        diskon: 335000,
        total: 6365000,
        status: 'lunas',
    },
]);

// State
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 10;
const showModal = ref(false);
const modalMode = ref<'add' | 'edit'>('add');
const selectedTransaction = ref<Transaction | null>(null);

// Form data
const formData = ref({
    kode_transaksi: '',
    tanggal: '',
    pelanggan: '',
    subtotal: 0,
    diskon: 0,
    total: 0,
    status: 'pending' as 'lunas' | 'pending' | 'dibatalkan',
});

// Computed
const filteredTransactions = computed(() => {
    if (!searchQuery.value) return transactions.value;

    return transactions.value.filter(
        (transaction) =>
            transaction.kode_transaksi.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            transaction.pelanggan.toLowerCase().includes(searchQuery.value.toLowerCase()),
    );
});

const paginatedTransactions = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return filteredTransactions.value.slice(start, end);
});

const totalPages = computed(() => {
    return Math.ceil(filteredTransactions.value.length / itemsPerPage);
});

// Summary statistics
const summaryStats = computed(() => {
    const lunas = filteredTransactions.value.filter((t) => t.status === 'lunas');
    return {
        totalTransaksi: filteredTransactions.value.length,
        totalPendapatan: lunas.reduce((sum, t) => sum + t.total, 0),
        totalDiskon: lunas.reduce((sum, t) => sum + t.diskon, 0),
        transaksiLunas: lunas.length,
    };
});

// Methods
const openAddModal = () => {
    modalMode.value = 'add';
    const today = new Date().toISOString().split('T')[0];
    const newKode = `TRX-2024-${String(transactions.value.length + 1).padStart(3, '0')}`;

    formData.value = {
        kode_transaksi: newKode,
        tanggal: today,
        pelanggan: '',
        subtotal: 0,
        diskon: 0,
        total: 0,
        status: 'pending',
    };
    showModal.value = true;
};

const openEditModal = (transaction: Transaction) => {
    modalMode.value = 'edit';
    selectedTransaction.value = transaction;
    formData.value = { ...transaction };
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    selectedTransaction.value = null;
};

const calculateTotal = () => {
    formData.value.total = formData.value.subtotal - formData.value.diskon;
};

const saveTransaction = () => {
    if (modalMode.value === 'add') {
        const newTransaction: Transaction = {
            id: transactions.value.length + 1,
            ...formData.value,
        };
        transactions.value.unshift(newTransaction);
    } else {
        const index = transactions.value.findIndex((t) => t.id === selectedTransaction.value?.id);
        if (index !== -1) {
            transactions.value[index] = { ...formData.value, id: selectedTransaction.value!.id };
        }
    }
    closeModal();
};

const deleteTransaction = (id: number) => {
    if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
        transactions.value = transactions.value.filter((t) => t.id !== id);
        if (paginatedTransactions.value.length === 0 && currentPage.value > 1) {
            currentPage.value--;
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

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'lunas':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'dibatalkan':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const exportToCSV = () => {
    alert('Fitur export CSV akan segera hadir!');
};
</script>

<template>
    <Sidebar>
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
                <div>
                    <h2 class="text-2xl font-bold">Laporan Transaksi</h2>
                    <p class="mt-1 text-sm text-muted-foreground">Kelola dan pantau semua transaksi</p>
                </div>
                <div class="flex gap-2">
                    <button @click="exportToCSV" class="flex items-center gap-2 rounded-lg border px-4 py-2 transition-colors hover:bg-muted">
                        <Download class="h-4 w-4" />
                        <span class="text-sm font-medium">Export</span>
                    </button>
                    <button
                        @click="openAddModal"
                        class="flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-white transition-colors hover:bg-blue-700"
                    >
                        <Plus class="h-4 w-4" />
                        <span class="text-sm font-medium">Tambah Transaksi</span>
                    </button>
                </div>
            </div>

            <!-- Summary Stats -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Total Transaksi</h3>
                        <span class="text-2xl">📊</span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-bold">{{ summaryStats.totalTransaksi }}</div>
                        <p class="mt-1 text-xs text-muted-foreground">Semua status</p>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Total Pendapatan</h3>
                        <span class="text-2xl">💰</span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-bold">{{ formatCurrency(summaryStats.totalPendapatan) }}</div>
                        <p class="mt-1 text-xs text-muted-foreground">Transaksi lunas</p>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Total Diskon</h3>
                        <span class="text-2xl">🎫</span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-bold">{{ formatCurrency(summaryStats.totalDiskon) }}</div>
                        <p class="mt-1 text-xs text-muted-foreground">Diskon diberikan</p>
                    </div>
                </div>

                <div class="rounded-lg border bg-card p-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-medium text-muted-foreground">Transaksi Lunas</h3>
                        <span class="text-2xl">✅</span>
                    </div>
                    <div class="mt-3">
                        <div class="text-2xl font-bold">{{ summaryStats.transaksiLunas }}</div>
                        <p class="mt-1 text-xs text-muted-foreground">Dari {{ summaryStats.totalTransaksi }} transaksi</p>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="rounded-lg border bg-card p-6">
                <div class="relative">
                    <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Cari berdasarkan kode transaksi atau nama pelanggan..."
                        class="w-full rounded-lg border py-2 pr-4 pl-10 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                    />
                </div>
            </div>

            <!-- Transaction Table -->
            <div class="overflow-hidden rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase sm:px-6">
                                    Kode Transaksi
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase sm:px-6">
                                    Tanggal
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase sm:px-6">
                                    Pelanggan
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase sm:px-6">
                                    Subtotal
                                </th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase sm:px-6">Diskon</th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase sm:px-6">Total</th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase sm:px-6">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase sm:px-6">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y">
                            <tr v-for="transaction in paginatedTransactions" :key="transaction.id" class="transition-colors hover:bg-muted/30">
                                <td class="px-4 py-4 text-sm font-medium whitespace-nowrap sm:px-6">
                                    {{ transaction.kode_transaksi }}
                                </td>
                                <td class="px-4 py-4 text-sm whitespace-nowrap text-muted-foreground sm:px-6">
                                    {{ formatDate(transaction.tanggal) }}
                                </td>
                                <td class="px-4 py-4 text-sm whitespace-nowrap sm:px-6">
                                    {{ transaction.pelanggan }}
                                </td>
                                <td class="px-4 py-4 text-sm whitespace-nowrap sm:px-6">
                                    {{ formatCurrency(transaction.subtotal) }}
                                </td>
                                <td class="px-4 py-4 text-sm font-medium whitespace-nowrap text-red-600 sm:px-6">
                                    -{{ formatCurrency(transaction.diskon) }}
                                </td>
                                <td class="px-4 py-4 text-sm font-bold whitespace-nowrap sm:px-6">
                                    {{ formatCurrency(transaction.total) }}
                                </td>
                                <td class="px-4 py-4 text-sm whitespace-nowrap sm:px-6">
                                    <span :class="['rounded-full px-2 py-1 text-xs font-semibold', getStatusColor(transaction.status)]">
                                        {{ transaction.status.charAt(0).toUpperCase() + transaction.status.slice(1) }}
                                    </span>
                                </td>
                                <td class="px-4 py-4 text-sm whitespace-nowrap sm:px-6">
                                    <div class="flex items-center gap-2">
                                        <button
                                            @click="openEditModal(transaction)"
                                            class="rounded-lg p-2 text-blue-600 transition-colors hover:bg-blue-50"
                                            title="Edit"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </button>
                                        <button
                                            @click="deleteTransaction(transaction.id)"
                                            class="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50"
                                            title="Hapus"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="flex flex-col items-center justify-between gap-4 border-t px-4 py-4 sm:flex-row sm:px-6">
                    <div class="text-sm text-muted-foreground">
                        Menampilkan {{ (currentPage - 1) * itemsPerPage + 1 }} -
                        {{ Math.min(currentPage * itemsPerPage, filteredTransactions.length) }}
                        dari {{ filteredTransactions.length }} transaksi
                    </div>
                    <div class="flex items-center gap-2">
                        <button
                            @click="goToPage(currentPage - 1)"
                            :disabled="currentPage === 1"
                            class="rounded-lg border px-3 py-1 text-sm transition-colors hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Previous
                        </button>

                        <button
                            v-for="page in totalPages"
                            :key="page"
                            @click="goToPage(page)"
                            :class="[
                                'rounded-lg border px-3 py-1 text-sm transition-colors',
                                currentPage === page ? 'border-blue-600 bg-blue-600 text-white' : 'hover:bg-muted',
                            ]"
                        >
                            {{ page }}
                        </button>

                        <button
                            @click="goToPage(currentPage + 1)"
                            :disabled="currentPage === totalPages"
                            class="rounded-lg border px-3 py-1 text-sm transition-colors hover:bg-muted disabled:cursor-not-allowed disabled:opacity-50"
                        >
                            Next
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Form - Fully Responsive -->
        <div
            v-if="showModal"
            class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/50 p-2 sm:p-4"
            @click.self="closeModal"
        >
            <div class="my-auto flex max-h-[95vh] w-full max-w-[95vw] flex-col rounded-lg bg-white shadow-xl sm:max-w-lg">
                <!-- Header -->
                <div class="flex shrink-0 items-center justify-between border-b p-4 sm:p-6">
                    <h3 class="text-base font-semibold sm:text-lg">
                        {{ modalMode === 'add' ? 'Tambah Transaksi Baru' : 'Edit Transaksi' }}
                    </h3>
                    <button @click="closeModal" class="rounded-lg p-1 transition-colors hover:bg-muted">
                        <X class="h-4 w-4 sm:h-5 sm:w-5" />
                    </button>
                </div>

                <!-- Content -->
                <div class="flex-1 space-y-3 overflow-y-auto p-4 sm:space-y-4 sm:p-6">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Kode Transaksi</label>
                        <input
                            v-model="formData.kode_transaksi"
                            type="text"
                            :disabled="modalMode === 'edit'"
                            class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none disabled:bg-gray-100 sm:px-3 sm:py-2 sm:text-base"
                            placeholder="TRX-2024-XXX"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Tanggal</label>
                        <input
                            v-model="formData.tanggal"
                            type="date"
                            class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Nama Pelanggan</label>
                        <input
                            v-model="formData.pelanggan"
                            type="text"
                            class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                            placeholder="Masukkan nama pelanggan"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Subtotal</label>
                        <input
                            v-model.number="formData.subtotal"
                            @input="calculateTotal"
                            type="number"
                            class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                            placeholder="0"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Diskon</label>
                        <input
                            v-model.number="formData.diskon"
                            @input="calculateTotal"
                            type="number"
                            class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                            placeholder="0"
                        />
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Total</label>
                        <input
                            v-model.number="formData.total"
                            type="number"
                            disabled
                            class="w-full rounded-lg border bg-gray-100 px-2.5 py-1.5 text-sm font-bold sm:px-3 sm:py-2 sm:text-base"
                        />
                        <p class="mt-1 text-xs text-muted-foreground">
                            {{ formatCurrency(formData.total) }}
                        </p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium sm:mb-2 sm:text-sm">Status</label>
                        <select
                            v-model="formData.status"
                            class="w-full rounded-lg border px-2.5 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none sm:px-3 sm:py-2 sm:text-base"
                        >
                            <option value="pending">Pending</option>
                            <option value="lunas">Lunas</option>
                            <option value="dibatalkan">Dibatalkan</option>
                        </select>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex shrink-0 items-center gap-2 border-t p-4 sm:gap-3 sm:p-6">
                    <button
                        @click="closeModal"
                        class="flex-1 rounded-lg border px-3 py-1.5 text-sm transition-colors hover:bg-muted sm:px-4 sm:py-2 sm:text-base"
                    >
                        Batal
                    </button>
                    <button
                        @click="saveTransaction"
                        class="flex-1 rounded-lg bg-blue-600 px-3 py-1.5 text-sm text-white transition-colors hover:bg-blue-700 sm:px-4 sm:py-2 sm:text-base"
                    >
                        {{ modalMode === 'add' ? 'Tambah' : 'Simpan' }}
                    </button>
                </div>
            </div>
        </div>
    </Sidebar>
</template>

<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import axios from 'axios';
import {
    Wallet,
    FileText,
    ShoppingBasket,
    TrendingUp,
    Activity,
    ArrowUpRight,
    Calendar,
    Users,
    CreditCard
} from 'lucide-vue-next';
import { onMounted, ref, computed } from 'vue';

// --- Interfaces ---
interface Transaksi {
    id_transaksi: string;
    total_harga: number;
    created_at: string;
    pelanggan: string;
}

interface Produk {
    id_produk: string;
    status: string;
    kategori: string;
}

// --- State ---
const isLoading = ref(false);
const transactions = ref<Transaksi[]>([]);
const products = ref<Produk[]>([]);

// --- Computed Stats ---
const stats = computed(() => {
    // 1. Total Revenue (Laporan Pendapatan)
    const totalRevenue = transactions.value.reduce((acc, curr) => acc + Number(curr.total_harga), 0);

    // 2. Total Penawaran
    const totalTrx = transactions.value.length;

    // 3. Total Produk
    const totalProducts = products.value.length;
    const activeProducts = products.value.filter(p => p.status === 'active').length;

    // 4. Rata-rata Nilai
    const avgTrx = totalTrx > 0 ? totalRevenue / totalTrx : 0;

    return {
        revenue: totalRevenue,
        totalTransactions: totalTrx,
        activeProducts,
        totalProducts,
        averageTransaction: avgTrx
    };
});

// --- Recent Activity (5 Terakhir) ---
const recentActivities = computed(() => {
    return [...transactions.value]
        .sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
        .slice(0, 5);
});

// --- Methods ---
const fetchData = async () => {
    isLoading.value = true;
    try {
        const [resTrx, resProd] = await Promise.all([
            axios.get('/api/transaksi'),
            axios.get('/api/produk')
        ]);

        if(resTrx.data && resTrx.data.data) {
            // Filter unik berdasarkan id_transaksi (handling jika data flat join)
            const uniqueMap = new Map();
            resTrx.data.data.forEach((item: any) => {
                if (!uniqueMap.has(item.id_transaksi)) {
                    uniqueMap.set(item.id_transaksi, item);
                }
            });
            transactions.value = Array.from(uniqueMap.values());
        }

        if(resProd.data && resProd.data.data) {
            products.value = resProd.data.data;
        }

    } catch (error) {
        console.error("Gagal mengambil data dashboard:", error);
    } finally {
        isLoading.value = false;
    }
};

const formatCurrency = (val: number) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(val);
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
};

onMounted(() => {
    fetchData();
});
</script>

<template>
    <Sidebar>
        <div class="space-y-8 font-['Plus_Jakarta_Sans',sans-serif]">

            <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Dashboard Overview</h1>
                    <p class="text-sm text-gray-500">Ringkasan performa bisnis Anda.</p>
                </div>
                <div class="flex items-center gap-2 text-sm text-gray-500 bg-white px-4 py-2 rounded-lg border border-gray-200 shadow-sm">
                    <Calendar class="w-4 h-4" />
                    <span>{{ new Date().toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</span>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">

                <div class="lg:col-span-2">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

                        <div class="md:col-span-2 relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md transition-all">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Laporan Pendapatan</p>
                                    <h3 class="text-3xl font-bold text-gray-900 tracking-tight">
                                        {{ isLoading ? '...' : formatCurrency(stats.revenue) }}
                                    </h3>
                                    <div class="mt-2 flex items-center gap-2">
                                        <span class="inline-flex items-center rounded-full bg-green-50 px-2 py-0.5 text-xs font-medium text-green-700">
                                            <TrendingUp class="mr-1 h-3 w-3" /> +12.5%
                                        </span>
                                        <span class="text-xs text-gray-400">dari bulan lalu</span>
                                    </div>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                                    <Wallet class="h-7 w-7" />
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2 relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md transition-all">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Rata-rata Nilai</p>
                                    <h3 class="text-3xl font-bold text-gray-900 tracking-tight">
                                        {{ isLoading ? '...' : formatCurrency(stats.averageTransaction) }}
                                    </h3>
                                    <p class="text-xs text-gray-400 mt-1">Per transaksi penawaran yang dibuat</p>
                                </div>
                                <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-50 text-purple-600">
                                    <CreditCard class="h-7 w-7" />
                                </div>
                            </div>
                        </div>

                        <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md transition-all">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Total Penawaran</p>
                                    <h3 class="text-2xl font-bold text-gray-900">
                                        {{ isLoading ? '...' : stats.totalTransactions }}
                                    </h3>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-orange-50 text-orange-600">
                                    <FileText class="h-5 w-5" />
                                </div>
                            </div>
                            <div class="mt-4 h-1 w-full bg-gray-100 rounded-full overflow-hidden">
                                <div class="h-full bg-orange-500 w-[70%] rounded-full"></div>
                            </div>
                        </div>

                        <div class="relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-6 shadow-sm hover:shadow-md transition-all">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-500 mb-1">Total Produk</p>
                                    <h3 class="text-2xl font-bold text-gray-900">
                                        {{ isLoading ? '...' : stats.totalProducts }}
                                    </h3>
                                </div>
                                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-teal-50 text-teal-600">
                                    <ShoppingBasket class="h-5 w-5" />
                                </div>
                            </div>
                            <div class="mt-4 flex items-center justify-between text-xs">
                                <span class="text-teal-600 font-medium">{{ stats.activeProducts }} Aktif</span>
                                <span class="text-gray-400">{{ stats.totalProducts - stats.activeProducts }} Non-aktif</span>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="space-y-6">

                    <div class="rounded-2xl bg-gradient-to-br from-red-600 to-red-800 p-6 text-white shadow-lg">
                        <h3 class="text-lg font-bold">Buat Penawaran Baru</h3>
                        <p class="mt-2 text-sm text-red-100 opacity-90">
                            Hitung estimasi biaya dan buat laporan penawaran dengan cepat.
                        </p>
                        <a href="/kalkulator" class="mt-5 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white py-3 text-sm font-bold text-red-700 hover:bg-red-50 transition-colors shadow-sm">
                            <Activity class="w-4 h-4" /> Buka Kalkulator
                        </a>
                    </div>

                    <div class="rounded-2xl border border-gray-100 bg-white shadow-sm h-fit">
                        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                            <h3 class="font-bold text-gray-900 text-sm">Aktivitas Terbaru</h3>
                            <a href="/laporan" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Lihat Semua</a>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <div v-if="isLoading" class="p-6 text-center text-xs text-gray-400">Loading...</div>
                            <div v-else-if="recentActivities.length === 0" class="p-6 text-center text-xs text-gray-400">Belum ada aktivitas.</div>

                            <div v-else v-for="trx in recentActivities" :key="trx.id_transaksi" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 transition-colors">
                                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">
                                    <Users class="h-4 w-4" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900">{{ trx.pelanggan }}</p>
                                    <p class="truncate text-xs text-gray-500">{{ formatDate(trx.created_at) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs font-bold text-gray-900">{{ formatCurrency(trx.total_harga) }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>
    </Sidebar>
</template>

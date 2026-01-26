<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import Sidebar from '@/components/Sidebar.vue';
import axios from 'axios';
// Import Icon dari Lucide
import {
  Calendar, TrendingUp, FileText, Users, BarChart3,
  Activity, Trophy, ArrowUpRight, Wifi, Loader2, TrendingDown
} from 'lucide-vue-next';

// Interface diperbaiki agar mendukung field nominal_diskon tanpa 'as any'
interface LaporanRaw {
  id_laporan: string;
  total_harga: number;
  pelanggan: string;
  created_at: string;
  netdiskon: number;
  // Detail item (child)
  id_produk: string;
  nama_produk: string;
  bandwidth: number;
  harga_produk: number;
  jumlah: number;
  produk_final: number;
  subtotal: number;
  kategori: string;
  // Tambahan field optional
  nominal_diskon_produk?: number;
  nominal_diskon_otc?: number;
}

const rawData = ref<LaporanRaw[]>([]);
const isLoading = ref(true);

const fetchData = async () => {
  isLoading.value = true;
  try {
    const response = await axios.get('/api/laporan');
    rawData.value = response.data.data;
    console.log("Data dashboard berhasil diambil:", rawData.value);
  } catch (error) {
    console.error("Gagal mengambil data dashboard:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  fetchData();
});

// 1. Data Unik per Transaksi (Parent Only)
const uniqueTransactions = computed(() => {
  const map = new Map();
  rawData.value.forEach(item => {
    if (!item.id_laporan) return;
    if (!map.has(item.id_laporan)) {
      map.set(item.id_laporan, {
        id: item.id_laporan,
        customer: item.pelanggan,
        total: Number(item.total_harga),
        date: item.created_at,
        diskon: Number(item.netdiskon) || 0
      });
    }
  });
  return Array.from(map.values());
});

// 2. Statistik Utama (Cards)
const stats = computed(() => {

  const txs = uniqueTransactions.value;

  // A. Total Penawaran
  const totalOffer = txs.reduce((acc, curr) => acc + curr.total, 0);

  // B. Total Transaksi
  const totalTx = txs.length;

  // C. Total Pelanggan (Unik berdasarkan nama)
  const uniqueCustomers = new Set(txs.map(t => t.customer?.toLowerCase().trim())).size;

  // D. Rata-rata Penawaran
  const avgOffer = totalTx > 0 ? totalOffer / totalTx : 0;

  // E. Total Diskon (Estimasi dari netdiskon persen ke nominal kasar)
  // Type safe karena interface sudah diupdate
  const totalDiscountReal = rawData.value.reduce((acc, curr) => {
     return acc + (Number(curr.nominal_diskon_produk || 0) + Number(curr.nominal_diskon_otc || 0));
  }, 0);

  // F. Total Bandwidth
  const totalBw = rawData.value.reduce((acc, curr) => acc + (Number(curr.bandwidth) || 0), 0);

  return {
    totalOffer,
    totalTx,
    uniqueCustomers,
    avgOffer,
    totalDiscount: totalDiscountReal,
    totalBandwidth: totalBw
  };
});

// 3. Astinet vs Indibiz (Kategori)
const categoryStats = computed(() => {
  let astinetCount = 0;
  let astinetRevenue = 0;
  let indibizCount = 0;
  let indibizRevenue = 0;

  rawData.value.forEach(item => {
    const name = item.kategori ? item.kategori.toLowerCase() : '';
    if (name.includes('astinet')) {
      astinetCount++; // Hitung item terjual
      astinetRevenue += Number(item.subtotal);
    } else if (name.includes('indibiz')) {
      indibizCount++;
      indibizRevenue += Number(item.subtotal);
    }
  });

  const totalRev = astinetRevenue + indibizRevenue || 1;

  return [
    {
      name: 'Astinet',
      revenue: astinetRevenue,
      transactions: astinetCount,
      color: 'from-blue-500 to-blue-600',
      bgIcon: 'bg-blue-100 text-blue-600', // Helper class untuk icon
      percentage: ((astinetRevenue / totalRev) * 100).toFixed(1)
    },
    {
      name: 'Indibiz',
      revenue: indibizRevenue,
      transactions: indibizCount,
      color: 'from-purple-500 to-purple-600',
      bgIcon: 'bg-purple-100 text-purple-600', // Helper class untuk icon
      percentage: ((indibizRevenue / totalRev) * 100).toFixed(1)
    }
  ];
});

// 4. Produk Terlaris (Top Products)
const topProducts = computed(() => {
  const productMap = new Map();

  rawData.value.forEach(item => {
    if(!item.nama_produk) return;
    const key = item.nama_produk;

    if (!productMap.has(key)) {
      productMap.set(key, {
        name: key,
        sold: 0,
        revenue: 0,
        category: key.toLowerCase().includes('astinet') ? 'Astinet' : 'Indibiz',
        bandwidth: item.bandwidth || 0
      });
    }

    const entry = productMap.get(key);
    entry.sold += Number(item.jumlah);
    entry.revenue += Number(item.subtotal);
  });

  // Urutkan berdasarkan revenue tertinggi, ambil 5
  return Array.from(productMap.values())
    .sort((a, b) => b.revenue - a.revenue)
    .slice(0, 5);
});

// 5. Transaksi Terbaru
const recentTransactions = computed(() => {
  // Ambil dari uniqueTransactions, sort date desc, limit 5
  return uniqueTransactions.value
    .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
    .slice(0, 5)
    .map(tx => {
       const firstItem = rawData.value.find(r => r.id_laporan === tx.id);
       const prodName = firstItem?.nama_produk || 'Layanan Telkom';
       const cat = prodName.toLowerCase().includes('astinet') ? 'Astinet' : 'Indibiz';

       return {
         id: tx.id,
         customer: tx.customer,
         amount: tx.total,
         time: new Date(tx.date).toLocaleDateString('id-ID'),
         product: prodName,
         category: cat
       };
    });
});

// 6. Top Potensial (Pelanggan dengan Total Penawaran Terbanyak)
const topPotential = computed(() => {
  const customerMap = new Map();

  uniqueTransactions.value.forEach(tx => {
    const name = tx.customer || 'Unknown';
    if (!customerMap.has(name)) {
      customerMap.set(name, { name: name, total: 0, transactions: 0 });
    }
    const entry = customerMap.get(name);
    entry.total += tx.total;
    entry.transactions += 1;
  });

  // Urutkan berdasarkan Total Penawaran terbesar
  const sorted = Array.from(customerMap.values()).sort((a, b) => b.total - a.total);
  return sorted.length > 0 ? sorted[0] : { name: '-', total: 0, transactions: 0 };
});

const formatCurrency = (value: number) => {
  return new Intl.NumberFormat('id-ID', {
    style: 'currency',
    currency: 'IDR',
    minimumFractionDigits: 0
  }).format(value);
};

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('id-ID').format(value);
};

</script>

<template>
  <Sidebar>
    <div class="space-y-6 font-sans">
      <div class="flex items-center justify-between">
        <div>
          <h1
            class="text-3xl font-bold tracking-tight bg-gradient-to-r from-gray-900 to-gray-600 dark:from-white dark:to-gray-400 bg-clip-text text-transparent">
            Dashboard Analytics
          </h1>
          <p class="text-muted-foreground mt-1">Monitor performa bisnis telekomunikasi Anda</p>
        </div>
        <div class="hidden md:flex items-center gap-2 px-4 py-2 rounded-lg border bg-card">
            <Calendar class="w-4 h-4 text-muted-foreground" />
          <span class="text-sm font-medium">{{ new Date().toLocaleDateString('id-ID', {
            day: 'numeric', month: 'long',
            year: 'numeric'
          }) }}</span>
        </div>
      </div>

      <div v-if="isLoading" class="h-64 flex flex-col items-center justify-center text-center">
         <Loader2 class="w-10 h-10 text-blue-600 animate-spin mb-4" />
         <p class="text-lg font-semibold text-gray-500 animate-pulse">Memuat Data Dashboard...</p>
      </div>

      <div v-else class="space-y-6 animate-in fade-in zoom-in duration-300">

        <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">

          <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-xl hover:shadow-blue-500/10">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-blue-500/10 to-transparent rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
            <div class="relative">
              <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 shadow-lg shadow-blue-500/30">
                  <TrendingUp class="w-6 h-6 text-white" />
                </div>
              </div>
              <h3 class="text-sm font-medium text-muted-foreground mb-1">Total Penawaran</h3>
              <div class="text-2xl font-bold">{{ formatCurrency(stats.totalOffer) }}</div>
              <p class="text-xs text-muted-foreground mt-2">Akumulasi nilai</p>
            </div>
          </div>

          <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-xl hover:shadow-purple-500/10">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-purple-500/10 to-transparent rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
            <div class="relative">
              <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-gradient-to-br from-purple-500 to-purple-600 shadow-lg shadow-purple-500/30">
                  <FileText class="w-6 h-6 text-white" />
                </div>
              </div>
              <h3 class="text-sm font-medium text-muted-foreground mb-1">Total Transaksi</h3>
              <div class="text-2xl font-bold">{{ formatNumber(stats.totalTx) }}</div>
              <p class="text-xs text-muted-foreground mt-2">Jumlah laporan dibuat</p>
            </div>
          </div>

          <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-xl hover:shadow-green-500/10">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-green-500/10 to-transparent rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
            <div class="relative">
              <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-gradient-to-br from-green-500 to-green-600 shadow-lg shadow-green-500/30">
                  <Users class="w-6 h-6 text-white" />
                </div>
              </div>
              <h3 class="text-sm font-medium text-muted-foreground mb-1">Total Pelanggan</h3>
              <div class="text-2xl font-bold">{{ formatNumber(stats.uniqueCustomers) }}</div>
              <p class="text-xs text-muted-foreground mt-2">Pelanggan unik</p>
            </div>
          </div>

          <div class="group relative overflow-hidden rounded-xl border bg-card p-6 transition-all hover:shadow-xl hover:shadow-orange-500/10">
            <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-br from-orange-500/10 to-transparent rounded-full -mr-16 -mt-16 transition-transform group-hover:scale-110"></div>
            <div class="relative">
              <div class="flex items-center justify-between mb-4">
                <div class="p-3 rounded-xl bg-gradient-to-br from-orange-500 to-orange-600 shadow-lg shadow-orange-500/30">
                  <BarChart3 class="w-6 h-6 text-white" />
                </div>
              </div>
              <h3 class="text-sm font-medium text-muted-foreground mb-1">Rata-rata Penawaran</h3>
              <div class="text-2xl font-bold">{{ formatCurrency(stats.avgOffer) }}</div>
              <p class="text-xs text-muted-foreground mt-2">Per laporan</p>
            </div>
          </div>
        </div>

        <div class="grid gap-6 md:grid-cols-2">

          <div class="rounded-xl border bg-card overflow-hidden shadow-sm">
            <div class="p-6 border-b bg-gradient-to-r from-muted/50 to-muted/30">
              <h3 class="text-lg font-semibold flex items-center gap-2">
                <Activity class="w-5 h-5 text-primary" />
                Astinet vs Indibiz
              </h3>
              <p class="text-sm text-muted-foreground mt-1">Komparasi pendapatan kategori</p>
            </div>
            <div class="p-6">
              <div class="space-y-6">
                <div v-for="(category, index) in categoryStats" :key="index" class="group">
                  <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-3">
                      <div :class="['w-12 h-12 rounded-xl flex items-center justify-center font-bold text-lg', category.bgIcon]">
                        {{ category.name.substring(0, 1) }}
                      </div>
                      <div>
                        <p class="font-semibold">{{ category.name }}</p>
                        <p class="text-xs text-muted-foreground">{{ category.transactions }} item terjual</p>
                      </div>
                    </div>
                    <div class="text-right">
                      <p class="font-bold text-lg">{{ formatCurrency(category.revenue) }}</p>
                      <p class="text-xs text-muted-foreground">{{ category.percentage }}%</p>
                    </div>
                  </div>
                  <div class="relative h-3 bg-muted rounded-full overflow-hidden">
                    <div
                      :class="['absolute inset-y-0 left-0 bg-gradient-to-r', category.color, 'rounded-full transition-all duration-700 ease-out']"
                      :style="{ width: `${category.percentage}%` }">
                      <div class="absolute inset-0 bg-white/20 animate-pulse"></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="rounded-xl border bg-card overflow-hidden shadow-sm">
            <div class="p-6 border-b bg-gradient-to-r from-muted/50 to-muted/30">
              <h3 class="text-lg font-semibold flex items-center gap-2">
                <Trophy class="w-5 h-5 text-primary" />
                Produk Terlaris
              </h3>
              <p class="text-sm text-muted-foreground mt-1">Top 5 produk berdasarkan pendapatan</p>
            </div>
            <div class="p-0">
               <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                  <thead class="bg-muted/50 text-muted-foreground">
                    <tr>
                      <th class="px-6 py-3 font-medium">Produk</th>
                       <th class="px-6 py-3 font-medium text-center">Bw</th>
                      <th class="px-6 py-3 font-medium text-center">Qty</th>
                      <th class="px-6 py-3 font-medium text-right">Total</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y">
                    <tr v-for="(prod, idx) in topProducts" :key="idx" class="hover:bg-muted/30 transition-colors">
                      <td class="px-6 py-3">
                        <div class="font-medium text-gray-900">{{ prod.name }}</div>
                        <div class="text-xs text-muted-foreground">{{ prod.category }}</div>
                      </td>
                       <td class="px-6 py-3 text-center">
                        <span v-if="prod.bandwidth > 0" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                          {{ prod.bandwidth }}M
                        </span>
                        <span v-else class="text-xs text-gray-400">-</span>
                      </td>
                      <td class="px-6 py-3 text-center">{{ prod.sold }}</td>
                      <td class="px-6 py-3 text-right font-bold">{{ formatCurrency(prod.revenue) }}</td>
                    </tr>
                     <tr v-if="topProducts.length === 0">
                      <td colspan="4" class="px-6 py-4 text-center text-muted-foreground">Belum ada data penjualan</td>
                    </tr>
                  </tbody>
                </table>
               </div>
            </div>
          </div>
        </div>

        <div class="rounded-xl border bg-card overflow-hidden shadow-sm">
          <div class="p-6 border-b bg-gradient-to-r from-muted/50 to-muted/30">
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-lg font-semibold flex items-center gap-2">
                  <ArrowUpRight class="w-5 h-5 text-primary" />
                  Transaksi Terbaru
                </h3>
                <p class="text-sm text-muted-foreground mt-1">Aktivitas penawaran terkini</p>
              </div>
              <a href="/laporan" class="text-sm text-primary hover:underline font-medium">Lihat Semua</a>
            </div>
          </div>
          <div class="divide-y">
            <div v-for="(transaction, index) in recentTransactions" :key="index"
              class="p-5 hover:bg-muted/50 transition-all group">
              <div class="flex items-center gap-4">
                <div
                  class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary/20 to-primary/5 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                  <FileText class="w-6 h-6 text-primary" />
                </div>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <p class="font-semibold truncate">{{ transaction.customer ? transaction.customer.toUpperCase() : 'TANPA NAMA' }}</p>
                    <span :class="[
                      transaction.category === 'Astinet' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-400',
                      'text-xs px-2 py-0.5 rounded-md font-medium flex-shrink-0'
                    ]">
                      {{ transaction.category }}
                    </span>
                  </div>
                  <div class="flex items-center gap-3 text-xs text-muted-foreground">
                    <span class="font-mono text-xs">{{ transaction.id }}</span>
                    <span>•</span>
                    <span class="truncate max-w-[200px]">{{ transaction.product }}</span>
                    <span>•</span>
                    <span>{{ transaction.time }}</span>
                  </div>
                </div>
                <div class="text-right flex-shrink-0">
                  <p class="font-bold text-lg">{{ formatCurrency(transaction.amount) }}</p>
                </div>
              </div>
            </div>
            <div v-if="recentTransactions.length === 0" class="p-6 text-center text-muted-foreground">
               Belum ada transaksi terbaru.
            </div>
          </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">

          <div class="rounded-xl border bg-card p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 rounded-lg bg-red-100 dark:bg-red-900/30">
                <TrendingDown class="w-5 h-5 text-red-600 dark:text-red-400" />
              </div>
              <h3 class="font-semibold text-sm">Total Diskon</h3>
            </div>
            <div class="text-2xl font-bold">{{ formatCurrency(stats.totalDiscount) }}</div>
            <p class="text-xs text-muted-foreground mt-2">Nominal potongan harga</p>
          </div>

          <div class="rounded-xl border bg-card p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 rounded-lg bg-cyan-100 dark:bg-cyan-900/30">
                <Wifi class="w-5 h-5 text-cyan-600 dark:text-cyan-400" />
              </div>
              <h3 class="font-semibold text-sm">Total Bandwidth</h3>
            </div>
            <div class="text-2xl font-bold">{{ formatNumber(stats.totalBandwidth) }} Mbps</div>
            <p class="text-xs text-muted-foreground mt-2">Kapasitas terjual</p>
          </div>

          <div class="rounded-xl border bg-card p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-3 mb-3">
              <div class="p-2 rounded-lg bg-amber-100 dark:bg-amber-900/30">
                <Trophy class="w-5 h-5 text-amber-600 dark:text-amber-400" />
              </div>
              <h3 class="font-semibold text-sm">Top Potensial</h3>
            </div>
            <div class="text-xl font-bold truncate" :title="topPotential.name">
              {{ topPotential.name.length > 20 ? topPotential.name.substring(0, 20) + '...' : topPotential.name.toUpperCase() }}
            </div>
            <p class="text-xs text-muted-foreground mt-2">
               {{ formatCurrency(topPotential.total) }} ({{ topPotential.transactions }} Trx)
            </p>
          </div>
        </div>
      </div>
    </div>
  </Sidebar>
</template>

<style scoped>
.animate-in {
  animation-duration: 0.5s;
  animation-timing-function: cubic-bezier(0.16, 1, 0.3, 1);
}
</style>

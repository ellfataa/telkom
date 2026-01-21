<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
</script>

<template>
  <Sidebar>
    <!-- Konten ini akan masuk ke slot <main> di Sidebar -->
    <div class="space-y-6">
      <!-- Welcome Section -->
      <div class="rounded-lg border bg-card p-6">
        <h2 class="text-2xl font-bold mb-2">Selamat Datang di Dashboard</h2>
        <p class="text-muted-foreground">
          Kelola sistem administrasi Anda dengan mudah dan efisien
        </p>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-lg border bg-card p-6">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-muted-foreground">Total Users</h3>
            <span class="text-2xl">👥</span>
          </div>
          <div class="mt-3">
            <div class="text-2xl font-bold">1,234</div>
            <p class="text-xs text-muted-foreground mt-1">+12% dari bulan lalu</p>
          </div>
        </div>

        <div class="rounded-lg border bg-card p-6">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-muted-foreground">Revenue</h3>
            <span class="text-2xl">💰</span>
          </div>
          <div class="mt-3">
            <div class="text-2xl font-bold">Rp 0</div>
            <p class="text-xs text-muted-foreground mt-1">+8% dari bulan lalu</p>
          </div>
        </div>

        <div class="rounded-lg border bg-card p-6">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-muted-foreground">Transaksi</h3>
            <span class="text-2xl">📊</span>
          </div>
          <div class="mt-3">
            <div class="text-2xl font-bold">0</div>
            <p class="text-xs text-muted-foreground mt-1">+0% dari bulan lalu</p>
          </div>
        </div>

        <div class="rounded-lg border bg-card p-6">
          <div class="flex items-center justify-between">
            <h3 class="text-sm font-medium text-muted-foreground">Pendaftar Baru</h3>
            <span class="text-2xl">✨</span>
          </div>
          <div class="mt-3">
            <div class="text-2xl font-bold">145</div>
            <p class="text-xs text-muted-foreground mt-1">+0% dari bulan lalu</p>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div class="rounded-lg border bg-card">
        <div class="p-6 border-b">
          <h3 class="text-lg font-semibold">Aktivitas Terbaru</h3>
        </div>
        <div class="p-6">
          <div class="space-y-4">
            <div class="flex items-start gap-4">
              <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
              <div class="flex-1">
                <p class="text-sm font-medium">Transaksi baru dibuat</p>
                <p class="text-xs text-muted-foreground">5 menit yang lalu</p>
              </div>
            </div>
            <div class="flex items-start gap-4">
              <div class="w-2 h-2 rounded-full bg-green-500 mt-2"></div>
              <div class="flex-1">
                <p class="text-sm font-medium">User baru mendaftar</p>
                <p class="text-xs text-muted-foreground">15 menit yang lalu</p>
              </div>
            </div>
            <div class="flex items-start gap-4">
              <div class="w-2 h-2 rounded-full bg-yellow-500 mt-2"></div>
              <div class="flex-1">
                <p class="text-sm font-medium">Pembayaran pending</p>
                <p class="text-xs text-muted-foreground">1 jam yang lalu</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Sidebar>
</template>
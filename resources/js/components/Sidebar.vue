<script setup lang="ts">
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import {
    Calculator, FileText, Home, LogOut, Settings,
    ShoppingBasket, User, ChevronDown, ChevronRight
} from 'lucide-vue-next';

// State untuk mengontrol dropdown Produk
const isProdukOpen = ref(false);

const toggleProduk = () => {
    isProdukOpen.value = !isProdukOpen.value;
};

// Fungsi logout
const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <aside class="w-64 flex-shrink-0 border-r bg-white flex flex-col">

            <div class="border-b p-6 flex items-center gap-3">
                <div
                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600">
                    <span class="text-lg font-bold text-white">A</span>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Admin Panel</h2>
                    <p class="text-xs text-gray-500">Management System</p>
                </div>
            </div>

            <div class="flex-1 overflow-y-auto px-3 py-4 space-y-6">

                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Menu Utama</p>
                    <nav class="mt-2 space-y-1">
                        <Link href="/"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
                            <Home class="h-4 w-4" /> Dashboard
                        </Link>
                        <Link href="/kalkulator"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
                            <Calculator class="h-4 w-4" /> Kalkulator
                        </Link>

                    </nav>
                </div>

                <div>
                    <p class="px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Sistem</p>
                    <nav class="mt-2 space-y-1">

                        <!-- <div>
                            <button @click="toggleProduk"
                                class="w-full flex items-center justify-between px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
                                <div class="flex items-center gap-3">
                                    <ShoppingBasket class="h-4 w-4" />
                                    <span>Produk</span>
                                </div>
                                <ChevronDown v-if="isProdukOpen" class="h-4 w-4" />
                                <ChevronRight v-else class="h-4 w-4" />
                            </button>

                            <div v-show="isProdukOpen" class="mt-1 ml-7 space-y-1 border-l-2 border-gray-100 pl-2">
                                <Link href="/produk"
                                    class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                                    Produk
                                </Link>
                                <Link href="/produk/nonpots"
                                    class="block px-3 py-2 text-sm text-gray-600 hover:text-blue-600 hover:bg-blue-50 rounded-md transition-colors">
                                    Produk Astinet
                                </Link>
                            </div>
                        </div> -->

                        <Link href="/produk"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
                            <ShoppingBasket class="h-4 w-4" /> Produk
                        </Link>
                        <Link href="/laporan"
                            class="flex items-center gap-3 px-3 py-2 text-sm font-medium text-gray-700 rounded-md hover:bg-gray-100 transition-colors">
                            <FileText class="h-4 w-4" /> Laporan Penawaran
                        </Link>

                    </nav>
                </div>
            </div>

            <div class="border-t p-4">
                <button
                    @click="handleLogout"
                    class="w-full flex items-center gap-3 px-3 py-2 text-sm font-medium text-red-600 rounded-md hover:bg-red-50 transition-colors">
                    <LogOut class="h-4 w-4" />
                    <span>Logout</span>
                </button>
            </div>
        </aside>

        <div class="flex-1 flex flex-col">
            <header class="h-16 flex items-center px-6 bg-white border-b sticky top-0 z-10">
                <h1 class="text-lg font-semibold text-gray-800">Halaman Admin</h1>
            </header>

            <main class="p-6">
                <div class="max-w-7xl mx-auto">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

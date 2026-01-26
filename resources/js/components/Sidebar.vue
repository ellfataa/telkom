<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    Calculator, FileText, Home, LogOut,
    ShoppingBasket, User, Menu, X, Users // Gunakan Users icon biar beda dikit
} from 'lucide-vue-next';

const page = usePage();
const currentUrl = computed(() => page.url);

const userName = computed(() => {
    return page.props.auth?.user?.name || 'User';
});

// Helper untuk active state
// Menggunakan startsWith agar sub-halaman juga dianggap aktif (misal /pelanggan/create)
const isActive = (route: string) => {
    return currentUrl.value === route || currentUrl.value.startsWith(route + '/');
};

const isSidebarOpen = ref(false);

const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="flex min-h-screen bg-gray-50 font-['Inter',sans-serif]">

        <aside
            class="w-72 bg-white border-r border-gray-200 flex flex-col fixed inset-y-0 left-0 z-30 transform transition-transform duration-300 lg:translate-x-0 lg:static shadow-sm"
            :class="isSidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="h-20 flex items-center gap-3 px-6 border-b border-gray-100">
                <div class="relative w-10 h-10 flex items-center justify-center">
                    <div class="relative w-full h-full bg-red-600 rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-red-500/20">
                        T
                    </div>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-900 tracking-tight leading-none">
                        Telkom<span class="text-red-600">BizPlan</span>
                    </h1>
                    <p class="text-[10px] text-gray-500 font-medium mt-1 uppercase tracking-wider">Admin Dashboard</p>
                </div>
                <button @click="isSidebarOpen = false" class="lg:hidden ml-auto text-gray-400">
                    <X class="w-6 h-6" />
                </button>
            </div>

            <div class="flex-1 overflow-y-auto px-4 py-6 space-y-8 custom-scrollbar">

                <div>
                    <p class="px-4 text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Menu Utama</p>
                    <nav class="space-y-1">
                        <Link href="/home"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group"
                            :class="isActive('/home') ? 'bg-red-50 text-red-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                        >
                            <Home class="h-5 w-5 transition-colors" :class="isActive('/home') ? 'text-red-600' : 'text-gray-400 group-hover:text-gray-600'" />
                            Dashboard
                        </Link>

                        <Link href="/pelanggan"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group"
                            :class="isActive('/pelanggan') ? 'bg-red-50 text-red-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                        >
                            <User class="h-5 w-5 transition-colors" :class="isActive('/pelanggan') ? 'text-red-600' : 'text-gray-400 group-hover:text-gray-600'" />
                            Pelanggan
                        </Link>

                        <Link href="/kalkulator"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group"
                            :class="isActive('/kalkulator') ? 'bg-red-50 text-red-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                        >
                            <Calculator class="h-5 w-5 transition-colors" :class="isActive('/kalkulator') ? 'text-red-600' : 'text-gray-400 group-hover:text-gray-600'" />
                            Kalkulator
                        </Link>
                    </nav>
                </div>

                <div>
                    <p class="px-4 text-xs font-bold uppercase tracking-wider text-gray-400 mb-3">Sistem & Data</p>
                    <nav class="space-y-1">
                        <Link href="/produk"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group"
                            :class="isActive('/produk') ? 'bg-red-50 text-red-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                        >
                            <ShoppingBasket class="h-5 w-5 transition-colors" :class="isActive('/produk') ? 'text-red-600' : 'text-gray-400 group-hover:text-gray-600'" />
                            Produk
                        </Link>

                        <Link href="/laporan"
                            class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 group"
                            :class="isActive('/laporan') ? 'bg-red-50 text-red-700 shadow-sm' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'"
                        >
                            <FileText class="h-5 w-5 transition-colors" :class="isActive('/laporan') ? 'text-red-600' : 'text-gray-400 group-hover:text-gray-600'" />
                            Laporan Penawaran
                        </Link>
                    </nav>
                </div>
            </div>

            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                <button
                    @click="handleLogout"
                    class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-white bg-gray-900 hover:bg-red-600 rounded-xl transition-all duration-300 shadow-lg shadow-gray-200 hover:shadow-red-500/20 group"
                >
                    <LogOut class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
                    <span>Keluar Aplikasi</span>
                </button>
            </div>
        </aside>

        <div v-if="isSidebarOpen" @click="isSidebarOpen = false" class="fixed inset-0 bg-black/50 z-20 lg:hidden backdrop-blur-sm"></div>

        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <header class="h-20 flex items-center justify-between px-6 bg-white/80 backdrop-blur-md border-b border-gray-200 sticky top-0 z-10">
                <div class="flex items-center gap-4">
                    <button @click="isSidebarOpen = true" class="p-2 text-gray-500 hover:bg-gray-100 rounded-lg lg:hidden">
                        <Menu class="w-6 h-6" />
                    </button>
                    <div>
                        <h1 class="text-xl font-bold text-gray-800 tracking-tight">Selamat Datang, {{ userName }}</h1>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-100 border border-gray-200 flex items-center justify-center text-gray-600 font-bold">
                        {{ userName.charAt(0).toUpperCase() }}
                    </div>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-4 lg:p-8">
                <div class="max-w-7xl mx-auto animate-fade-in-up">
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 20px; }
.animate-fade-in-up { animation: fadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>

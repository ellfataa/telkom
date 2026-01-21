<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <Head title="Log in">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    </Head>

    <div class="min-h-screen flex bg-white dark:bg-[#0a0a0a] font-['Inter'] selection:bg-red-500 selection:text-white">

        <div class="hidden lg:flex w-[55%] relative overflow-hidden bg-gray-900 items-center justify-center">
            <div class="absolute inset-0 bg-gradient-to-br from-red-700 via-red-800 to-red-950 z-0"></div>

            <div class="absolute inset-0 opacity-10 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] mix-blend-overlay"></div>

            <div class="absolute -top-40 -right-40 w-96 h-96 bg-red-500 rounded-full blur-[128px] opacity-40 animate-pulse"></div>
            <div class="absolute bottom-0 left-0 w-80 h-80 bg-black rounded-full blur-[100px] opacity-30"></div>

            <div class="relative z-10 text-center px-16 max-w-2xl">
                <div class="mb-8 flex justify-center">
                    <div class="w-20 h-20 bg-white/10 backdrop-blur-lg border border-white/20 rounded-2xl flex items-center justify-center shadow-2xl">
                        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold text-white tracking-tight leading-tight mb-6">
                    Connect the World <br> with <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-200 to-white">Innovation</span>
                </h2>
                <p class="text-red-100/80 text-lg leading-relaxed font-light">
                    Sistem manajemen terintegrasi untuk mendukung produktivitas dan konektivitas digital Indonesia.
                </p>
            </div>

            <div class="absolute bottom-8 text-white/20 text-xs tracking-widest uppercase">
                © Telkom Indonesia Digital Ecosystem
            </div>
        </div>

        <div class="w-full lg:w-[45%] flex flex-col justify-center items-center p-8 lg:p-16 bg-white dark:bg-[#0a0a0a] relative">

            <div class="lg:hidden absolute top-8 left-8 flex items-center gap-2">
                 <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center text-white font-bold">T</div>
                 <span class="font-bold text-gray-900 dark:text-white">TelkomPortal</span>
            </div>

            <div class="w-full max-w-[420px]">
                <div class="mb-10">
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Selamat Datang</h1>
                    <p class="text-gray-500 mt-2 text-sm dark:text-gray-400 font-light">
                        Masuk ke akun Anda untuk mengakses dashboard.
                    </p>
                </div>

                <div v-if="status" class="mb-6 p-4 rounded-xl bg-green-50 border border-green-100 text-green-700 text-sm font-medium flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-6">
                    <div class="space-y-2">
                        <label for="email" class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">Email</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-red-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                autofocus
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:bg-white focus:ring-2 focus:ring-red-100 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:bg-gray-900 dark:border-gray-800 dark:text-white"
                                placeholder="nama@telkom.co.id"
                            />
                        </div>
                        <div v-if="form.errors.email" class="text-red-500 text-xs font-medium ml-1 mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div class="space-y-2">
                        <!-- <div class="flex justify-between items-center ml-1">
                            <label for="password" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Password</label>
                            <Link v-if="canResetPassword" :href="route('password.request')" class="text-xs text-red-600 hover:text-red-700 font-semibold transition-colors">
                                Lupa Password?
                            </Link>
                        </div> -->
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-red-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                </svg>
                            </div>
                            <input
                                id="password"
                                type="password"
                                v-model="form.password"
                                required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:bg-white focus:ring-2 focus:ring-red-100 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:bg-gray-900 dark:border-gray-800 dark:text-white"
                                placeholder="••••••••"
                            />
                        </div>
                        <div v-if="form.errors.password" class="text-red-500 text-xs font-medium ml-1 mt-1">{{ form.errors.password }}</div>
                    </div>

                    <!-- <div class="flex items-center justify-between pt-2">
                        <label class="flex items-center cursor-pointer">
                            <input
                                type="checkbox"
                                v-model="form.remember"
                                class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500 transition-all"
                            />
                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Ingat Saya</span>
                        </label>
                    </div> -->

                    <button
                        :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                        class="w-full py-4 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold rounded-xl shadow-lg shadow-red-500/30 transform transition-all active:scale-[0.98] focus:ring-4 focus:ring-red-200"
                    >
                        <span v-if="form.processing" class="flex items-center justify-center gap-2">
                             <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                        <span v-else>Masuk Akun</span>
                    </button>

                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Belum punya akun akses?
                            <Link :href="route('register')" class="text-red-600 font-bold hover:text-red-800 hover:underline transition-all">
                                Daftar Sekarang
                            </Link>
                        </p>
                    </div>
                </form>
            </div>

            <div class="lg:hidden mt-12 text-center text-xs text-gray-400">
                © Telkom Indonesia
            </div>
        </div>
    </div>
</template>

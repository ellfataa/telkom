<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <Head title="Register">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    </Head>

    <div class="min-h-screen flex bg-white dark:bg-[#0a0a0a] font-['Inter'] selection:bg-red-500 selection:text-white">

        <div class="hidden lg:flex w-[55%] relative overflow-hidden bg-gray-900 items-center justify-center">
            <div class="absolute inset-0 bg-gradient-to-bl from-gray-900 via-red-950 to-red-900 z-0"></div>

            <div class="absolute inset-0 opacity-10 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] mix-blend-overlay"></div>

            <div class="absolute bottom-[-10%] left-[-10%] w-[500px] h-[500px] bg-red-600 rounded-full blur-[150px] opacity-30 animate-pulse"></div>
            <div class="absolute top-[20%] right-[10%] w-60 h-60 bg-white rounded-full blur-[100px] opacity-10"></div>

            <div class="relative z-10 text-center px-16 max-w-2xl">
                <div class="mb-8 flex justify-center">
                    <div class="w-20 h-20 bg-white/5 backdrop-blur-lg border border-white/10 rounded-2xl flex items-center justify-center shadow-2xl">
                        <svg class="w-10 h-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                    </div>
                </div>
                <h2 class="text-4xl md:text-5xl font-bold text-white tracking-tight leading-tight mb-6">
                    Join the <br> <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-400 to-red-200">Digital Ecosystem</span>
                </h2>
                <p class="text-gray-300 text-lg leading-relaxed font-light">
                    Mulai perjalanan inovasi Anda. Daftarkan diri untuk berkolaborasi dan menciptakan solusi masa depan.
                </p>
            </div>

            <div class="absolute bottom-8 text-white/20 text-xs tracking-widest uppercase">
                © Telkom Indonesia Digital Ecosystem
            </div>
        </div>

        <div class="w-full lg:w-[45%] flex flex-col justify-center items-center p-8 lg:p-12 bg-white dark:bg-[#0a0a0a] relative overflow-y-auto">

            <div class="lg:hidden absolute top-8 left-8 flex items-center gap-2">
                 <div class="w-8 h-8 bg-red-600 rounded-lg flex items-center justify-center text-white font-bold">T</div>
                 <span class="font-bold text-gray-900 dark:text-white">TelkomPortal</span>
            </div>

            <div class="w-full max-w-[420px] py-8 lg:py-0">
                <div class="mb-8">
                    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Buat Akun Baru</h1>
                    <p class="text-gray-500 mt-2 text-sm dark:text-gray-400 font-light">
                        Lengkapi data diri Anda untuk memulai akses.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <div class="space-y-2">
                        <label for="name" class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">Nama Lengkap</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-red-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                            </div>
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                required
                                autofocus
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:bg-white focus:ring-2 focus:ring-red-100 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:bg-gray-900 dark:border-gray-800 dark:text-white"
                                placeholder="Nama Lengkap Anda"
                            />
                        </div>
                        <div v-if="form.errors.name" class="text-red-500 text-xs font-medium ml-1 mt-1">{{ form.errors.name }}</div>
                    </div>

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
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:bg-white focus:ring-2 focus:ring-red-100 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:bg-gray-900 dark:border-gray-800 dark:text-white"
                                placeholder="nama@email.com"
                            />
                        </div>
                        <div v-if="form.errors.email" class="text-red-500 text-xs font-medium ml-1 mt-1">{{ form.errors.email }}</div>
                    </div>

                    <div class="space-y-2">
                        <label for="password" class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">Password</label>
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
                                placeholder="Minimal 8 karakter"
                            />
                        </div>
                        <div v-if="form.errors.password" class="text-red-500 text-xs font-medium ml-1 mt-1">{{ form.errors.password }}</div>
                    </div>

                    <div class="space-y-2">
                        <label for="password_confirmation" class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">Konfirmasi Password</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-red-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <input
                                id="password_confirmation"
                                type="password"
                                v-model="form.password_confirmation"
                                required
                                class="w-full pl-12 pr-4 py-3.5 bg-gray-50 border border-gray-200 text-gray-900 rounded-xl focus:bg-white focus:ring-2 focus:ring-red-100 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:bg-gray-900 dark:border-gray-800 dark:text-white"
                                placeholder="Ulangi password"
                            />
                        </div>
                        <div v-if="form.errors.password_confirmation" class="text-red-500 text-xs font-medium ml-1 mt-1">{{ form.errors.password_confirmation }}</div>
                    </div>

                    <div class="pt-2">
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
                                Mendaftarkan...
                            </span>
                            <span v-else>Daftar Akun</span>
                        </button>
                    </div>

                    <div class="mt-8 text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            Sudah punya akun?
                            <Link :href="route('login')" class="text-red-600 font-bold hover:text-red-800 hover:underline transition-all">
                                Login disini
                            </Link>
                        </p>
                    </div>
                </form>
            </div>

            <div class="lg:hidden mt-8 text-center text-xs text-gray-400">
                © Telkom Indonesia
            </div>
        </div>
    </div>
</template>

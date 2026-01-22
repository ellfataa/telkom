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
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </Head>

    <div class="min-h-screen w-full flex flex-col lg:flex-row font-['Plus_Jakarta_Sans',sans-serif] selection:bg-red-500 selection:text-white dark:bg-[#050505] dark:text-gray-100 overflow-hidden relative isolate">

        <div class="relative w-full lg:w-[50%] h-[15vh] lg:h-auto lg:min-h-screen order-first overflow-hidden bg-gray-900 border-b lg:border-b-0 lg:border-r border-gray-200 dark:border-gray-800 lg:sticky lg:top-0">

            <img
                src="/image/auth.jpg"
                alt="Telkom Indonesia Authentication"
                class="absolute inset-0 w-full h-full object-cover object-center animate-slow-pan"
            >

            <div class="absolute inset-0 bg-gradient-to-t from-[#050505] via-gray-900/50 to-transparent lg:bg-gradient-to-r lg:from-[#050505] lg:via-gray-900/30 lg:to-transparent"></div>

            <div class="absolute inset-0 opacity-20 bg-[url('https://grainy-gradients.vercel.app/noise.svg')] mix-blend-overlay"></div>

            <div class="hidden lg:flex absolute bottom-0 left-0 w-full p-12 z-20 flex-col justify-end h-full">
                 <div class="bg-white/10 backdrop-blur-md border border-white/10 p-6 rounded-3xl shadow-2xl animate-fade-in-up delay-300 lg:mb-6">
                    <div class="flex items-start gap-4">
                        <div class="p-3 bg-gradient-to-br from-red-600 to-red-700 rounded-2xl shrink-0 shadow-lg shadow-red-600/20 flex items-center justify-center">
                            <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white mb-1 tracking-tight">Autentikasi</h3>
                            <p class="text-gray-200 text-sm leading-relaxed font-medium opacity-90">
                                Mari bersama meningkatkan efektivitas dan transparansi penawaran produk TelkomGroup melalui sistem terintegrasi.
                            </p>
                        </div>
                    </div>
                 </div>
            </div>
        </div>

        <div class="w-full lg:w-[50%] flex flex-col justify-center relative isolate bg-white dark:bg-[#050505] transition-colors duration-300 py-8 lg:py-12 min-h-full">

            <div class="absolute inset-0 -z-10 h-full w-full bg-[linear-gradient(to_right,#8080800a_1px,transparent_1px),linear-gradient(to_bottom,#8080800a_1px,transparent_1px)] bg-[size:14px_24px]"></div>
            <div class="absolute top-0 right-0 w-full h-full overflow-hidden pointer-events-none -z-10">
                 <div class="absolute top-[5%] right-[5%] w-[400px] h-[400px] bg-red-500/5 rounded-full blur-[120px]"></div>
            </div>

            <div class="lg:hidden absolute top-4 left-6 flex items-center gap-2">
                 <div class="w-8 h-8 bg-gradient-to-br from-red-600 to-red-700 rounded-lg flex items-center justify-center text-white font-bold shadow-md">T</div>
                 <span class="font-bold text-gray-900 dark:text-white tracking-tight">Telkom<span class="text-red-600">Portal</span></span>
            </div>

            <div class="w-full max-w-[480px] mx-auto px-6 lg:px-12 animate-fade-in-up">

                <div class="mb-8">
                    <h1 class="text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-white tracking-tight mb-2">
                        Buat Akun Baru
                    </h1>
                    <p class="text-base text-gray-500 dark:text-gray-400">
                        Lengkapi data diri Anda untuk memulai akses penuh.
                    </p>
                </div>

                <form @submit.prevent="submit" class="space-y-5">

                    <div class="space-y-1.5">
                        <label for="name" class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">Nama Lengkap</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-red-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" /></svg>
                            </div>
                            <input
                                id="name"
                                type="text"
                                v-model="form.name"
                                required
                                autofocus
                                class="w-full pl-12 pr-4 py-3 bg-gray-50 hover:bg-gray-100/50 border border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:bg-gray-900/50 dark:hover:bg-gray-900 dark:border-gray-800 dark:text-white dark:focus:bg-gray-900"
                                placeholder="Masukkan nama lengkap"
                            />
                        </div>
                        <div v-if="form.errors.name" class="text-red-600 text-xs font-medium ml-1 flex items-center gap-1 mt-1">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label for="email" class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">Email Perusahaan/Pribadi</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-red-600 transition-colors">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                            </div>
                            <input
                                id="email"
                                type="email"
                                v-model="form.email"
                                required
                                class="w-full pl-12 pr-4 py-3 bg-gray-50 hover:bg-gray-100/50 border border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:bg-gray-900/50 dark:hover:bg-gray-900 dark:border-gray-800 dark:text-white dark:focus:bg-gray-900"
                                placeholder="nama@email.com"
                            />
                        </div>
                        <div v-if="form.errors.email" class="text-red-600 text-xs font-medium ml-1 flex items-center gap-1 mt-1">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="space-y-1.5">
                            <label for="password" class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">Password</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-red-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" /></svg>
                                </div>
                                <input
                                    id="password"
                                    type="password"
                                    v-model="form.password"
                                    required
                                    class="w-full pl-12 pr-4 py-3 bg-gray-50 hover:bg-gray-100/50 border border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:bg-gray-900/50 dark:hover:bg-gray-900 dark:border-gray-800 dark:text-white dark:focus:bg-gray-900"
                                    placeholder="Min. 8 karakter"
                                />
                            </div>
                            <div v-if="form.errors.password" class="text-red-600 text-xs font-medium ml-1 mt-1">{{ form.errors.password }}</div>
                        </div>

                        <div class="space-y-1.5">
                            <label for="password_confirmation" class="text-sm font-semibold text-gray-700 dark:text-gray-300 ml-1">Ulangi Password</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 group-focus-within:text-red-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                </div>
                                <input
                                    id="password_confirmation"
                                    type="password"
                                    v-model="form.password_confirmation"
                                    required
                                    class="w-full pl-12 pr-4 py-3 bg-gray-50 hover:bg-gray-100/50 border border-gray-200 text-gray-900 rounded-2xl focus:bg-white focus:ring-4 focus:ring-red-500/10 focus:border-red-500 transition-all outline-none placeholder-gray-400 dark:bg-gray-900/50 dark:hover:bg-gray-900 dark:border-gray-800 dark:text-white dark:focus:bg-gray-900"
                                    placeholder="Konfirmasi"
                                />
                            </div>
                        </div>
                    </div>

                    <div class="pt-2">
                        <button
                            :class="{ 'opacity-70 cursor-not-allowed': form.processing }"
                            :disabled="form.processing"
                            class="w-full relative py-3.5 bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-bold rounded-2xl shadow-xl shadow-red-500/25 transform transition-all duration-200 hover:-translate-y-0.5 active:scale-[0.98] focus:ring-4 focus:ring-red-200 dark:focus:ring-red-900 overflow-hidden group"
                        >
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-shimmer"></div>
                            <span v-if="form.processing" class="flex items-center justify-center gap-2 relative">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Memproses...
                            </span>
                            <span v-else class="relative flex items-center justify-center gap-2">
                                Daftar Sekarang
                                <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </span>
                        </button>
                    </div>

                    <div class="text-center">
                        <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                            Sudah punya akun?
                            <Link :href="route('login')" class="text-red-600 font-bold hover:text-red-700 hover:underline transition-colors ml-1">
                                Masuk disini
                            </Link>
                        </p>
                    </div>
                </form>

                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800/50 text-center">
                    <p class="text-xs text-gray-400 dark:text-gray-600 font-medium">
                        &copy; {{ new Date().getFullYear() }} Telkom Indonesia. Protected by Enterprise Security.
                    </p>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
.animate-fade-in-up {
    animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
    transform: translateY(20px);
}

.delay-300 { animation-delay: 0.3s; }

@keyframes fadeInUp {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes shimmer {
    100% { transform: translateX(100%); }
}
.group-hover\:animate-shimmer {
    animation: shimmer 1.5s infinite;
}

.animate-slow-pan {
    animation: slowPan 30s infinite alternate ease-in-out;
}
@keyframes slowPan {
    from { object-position: 50% 50%; transform: scale(1.05); }
    to { object-position: 60% 50%; transform: scale(1.15); }
}
</style>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    </Head>

    <div class="min-h-screen w-full flex flex-col lg:flex-row font-['Plus_Jakarta_Sans',sans-serif] selection:bg-red-500 selection:text-white dark:bg-[#050505] dark:text-gray-100 overflow-hidden relative isolate">

        <div class="relative w-full lg:w-[45%] h-[30vh] lg:h-auto lg:min-h-screen order-first lg:order-last overflow-hidden bg-gray-900 border-b lg:border-b-0 lg:border-l border-gray-200 dark:border-gray-800">
            <img
                src="/image/telkom.jpg"
                alt="Telkom Indonesia Office"
                class="absolute inset-0 w-full h-full object-cover object-center"
            >

            <div class="absolute inset-0 bg-gradient-to-t from-[#050505]/80 via-transparent to-[#050505]/10 lg:bg-gradient-to-l lg:from-[#050505]/5 lg:via-[#050505]/10 lg:to-[#050505]/40"></div>

            <div class="absolute bottom-6 right-6 lg:bottom-10 lg:right-10 bg-white/10 backdrop-blur-md p-4 rounded-2xl shadow-2xl border border-white/20 z-20 animate-fade-in-up delay-300">
                <div class="flex items-center gap-3">
                    <div class="flex items-center justify-center w-10 h-10 bg-red-600 rounded-full shadow-lg shadow-red-600/30">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <div>
                        <p class="font-bold text-white text-sm leading-tight tracking-wide">Telkom Landmark</p>
                        <p class="text-red-100/80 text-xs font-medium">Smart Office Tower</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full lg:w-[55%] flex flex-col justify-between relative isolate bg-white dark:bg-[#050505] transition-colors duration-300">

            <div class="absolute inset-0 -z-10 h-full w-full bg-[linear-gradient(to_right,#8080800a_1px,transparent_1px),linear-gradient(to_bottom,#8080800a_1px,transparent_1px)] bg-[size:14px_24px] mask-image-gradient"></div>
            <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none -z-10">
                <div class="absolute -top-[10%] -left-[10%] w-[600px] h-[600px] bg-red-500/5 rounded-full blur-[100px] opacity-60 dark:opacity-20"></div>
            </div>

            <nav class="w-full px-6 lg:px-16 py-6 flex justify-between items-center z-20">
                <div class="flex items-center gap-3 select-none">
                    <div class="relative w-10 h-10 flex items-center justify-center">
                        <div class="relative w-full h-full bg-transparent rounded-xl flex items-center justify-center text-white font-bold text-xl shadow-lg shadow-red-500/20">
                            <img src="/image/logo-telkom.png" alt="Logo Telkom Indonesia" class="w-6 h-6"/>
                        </div>
                    </div>
                    <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Telkom<span class="text-red-600">BizPlan</span>
                    </span>
                </div>
            </nav>

            <main class="flex-grow flex items-center px-6 lg:px-16 py-10 z-10">
                <div class="max-w-xl w-full space-y-10 text-center lg:text-left">
                    <div class="space-y-6 animate-fade-in-up">
                        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold tracking-tight text-gray-900 leading-[1.15] dark:text-white text-balance">
                            Empowering<br class="hidden sm:block"/>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 via-red-500 to-orange-500">
                                Product Offering
                            </span>
                        </h1>

                        <p class="text-base sm:text-lg text-gray-500 dark:text-gray-400 leading-relaxed text-pretty max-w-lg mx-auto lg:mx-0">
                            Satu portal digital untuk mengelola laporan dan penawaran produk TelkomGroup secara cepat dan terintegrasi.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start animate-fade-in-up delay-100">
                        <Link
                            href="/login"
                            class="group relative px-8 py-3.5 bg-red-600 text-white font-bold rounded-2xl shadow-xl shadow-red-500/20 hover:bg-red-700 transition-all duration-300 transform hover:-translate-y-1 overflow-hidden"
                        >
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent -translate-x-full group-hover:animate-shimmer"></div>
                            <span class="relative flex items-center justify-center gap-3">
                                Masuk Akun
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </span>
                        </Link>

                        <Link
                            href="/register"
                            class="px-8 py-3.5 bg-transparent text-gray-700 dark:text-gray-200 border-2 border-gray-200 dark:border-gray-800 font-bold rounded-2xl hover:border-red-600 hover:text-red-600 dark:hover:border-red-500 dark:hover:text-red-400 transition-all duration-300 transform hover:-translate-y-1 text-center"
                        >
                            Daftar Sekarang
                        </Link>
                    </div>

                    <div class="pt-8 border-t border-gray-100 dark:border-gray-800/50 flex items-center justify-center lg:justify-start gap-8 animate-fade-in-up delay-200">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white">1.0</h3>
                            <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Platform Version</p>
                        </div>
                    </div>
                </div>
            </main>

            <footer class="px-6 lg:px-16 py-6 text-center lg:text-left z-10">
                <p class="text-xs text-gray-400 dark:text-gray-600 font-medium">
                    &copy; {{ new Date().getFullYear() }} Telkom Indonesia. All rights reserved.
                </p>
            </footer>
        </div>

    </div>
</template>

<style scoped>
/* Animation Utilities */
.animate-fade-in-up {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    opacity: 0;
}

.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }

@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes shimmer {
    100% { transform: translateX(100%); }
}
.group-hover\:animate-shimmer {
    animation: shimmer 1.5s infinite;
}

.mask-image-gradient {
    mask-image: linear-gradient(to bottom, rgba(0,0,0,1) 60%, rgba(0,0,0,0));
}
</style>

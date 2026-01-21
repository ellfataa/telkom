<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="anonymous">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    </Head>

    <div class="min-h-screen bg-gray-50 flex flex-col font-['Inter'] selection:bg-red-600 selection:text-white dark:bg-[#0a0a0a] dark:text-gray-100 overflow-hidden relative">

        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none z-0">
            <div class="absolute -top-[10%] -right-[5%] w-[600px] h-[600px] bg-red-500/10 rounded-full blur-3xl rounded-bl-[150px]"></div>
            <div class="absolute top-[20%] -left-[10%] w-[400px] h-[400px] bg-gray-300/20 dark:bg-gray-700/20 rounded-full blur-3xl"></div>
        </div>

        <nav class="relative z-10 w-full max-w-7xl mx-auto px-6 py-8 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-red-600 to-red-700 rounded-lg shadow-lg flex items-center justify-center text-white font-bold text-lg">
                    T
                </div>
                <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Telkom<span class="text-red-600">Portal</span>
                </span>
            </div>
        </nav>

        <main class="relative z-10 flex-grow flex items-center justify-center p-6">
            <div class="max-w-5xl w-full grid lg:grid-cols-2 gap-12 items-center">

                <div class="space-y-8 text-center lg:text-left animate-fade-in-up">
                    <div class="space-y-4">
                        <span class="inline-block py-1 px-3 rounded-full bg-red-50 border border-red-100 text-red-600 text-xs font-semibold uppercase tracking-wider dark:bg-red-900/20 dark:border-red-800 dark:text-red-400">
                            Digital Solution
                        </span>
                        <h1 class="text-5xl md:text-6xl font-extrabold tracking-tight text-gray-900 leading-[1.1] dark:text-white">
                            Innovate for <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-red-500">
                                Indonesia's Future
                            </span>
                        </h1>
                        <p class="text-lg text-gray-500 max-w-lg mx-auto lg:mx-0 leading-relaxed dark:text-gray-400">
                            Platform terintegrasi untuk pengelolaan layanan digital dan konektivitas tanpa batas. Bergabunglah untuk pengalaman yang lebih baik.
                        </p>
                    </div>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                        <Link
                            href="/login"
                            class="group relative px-8 py-3.5 bg-red-600 text-white font-semibold rounded-xl shadow-lg shadow-red-500/30 hover:bg-red-700 hover:shadow-red-600/40 transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center gap-2"
                        >
                            <span>Login Akun</span>
                            <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </Link>

                        <Link
                            href="/register"
                            class="px-8 py-3.5 bg-white text-gray-700 border border-gray-200 font-semibold rounded-xl hover:border-red-200 hover:bg-red-50/50 hover:text-red-600 transition-all duration-300 transform hover:-translate-y-1 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            Registrasi
                        </Link>
                    </div>

                    <div class="pt-6 flex items-center justify-center lg:justify-start gap-8 opacity-60 grayscale hover:grayscale-0 transition-all duration-500">
                         <div class="h-8 w-24 bg-gray-200 rounded dark:bg-gray-700"></div>
                         <div class="h-8 w-24 bg-gray-200 rounded dark:bg-gray-700"></div>
                    </div>
                </div>

                <div class="hidden lg:block relative">
                    <div class="relative w-full aspect-square max-w-md mx-auto">
                        <div class="absolute inset-0 bg-gradient-to-tr from-gray-100 to-white rounded-3xl shadow-2xl border border-white/50 backdrop-blur-xl z-20 flex flex-col p-6 dark:from-gray-800 dark:to-gray-900 dark:border-gray-700">
                            <div class="flex justify-between items-center mb-8">
                                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center text-red-600">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                </div>
                                <div class="h-2 w-12 bg-gray-200 rounded-full dark:bg-gray-700"></div>
                            </div>
                            <div class="space-y-4 flex-grow">
                                <div class="h-4 w-3/4 bg-gray-100 rounded dark:bg-gray-700"></div>
                                <div class="h-4 w-1/2 bg-gray-100 rounded dark:bg-gray-700"></div>
                                <div class="mt-8 p-4 bg-red-50 rounded-xl border border-red-100 dark:bg-red-900/10 dark:border-red-900/30">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-red-600 text-white flex items-center justify-center text-xs font-bold">✓</div>
                                        <div>
                                            <div class="h-3 w-24 bg-red-200 rounded mb-1 dark:bg-red-900"></div>
                                            <div class="h-2 w-16 bg-red-100 rounded dark:bg-red-900/50"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                             <div class="mt-auto h-24 flex items-end justify-between gap-2 px-2">
                                <div class="w-full bg-red-200 rounded-t-sm h-[40%] animate-pulse dark:bg-red-900"></div>
                                <div class="w-full bg-red-300 rounded-t-sm h-[70%] animate-pulse delay-75 dark:bg-red-800"></div>
                                <div class="w-full bg-red-500 rounded-t-sm h-[50%] animate-pulse delay-150 dark:bg-red-700"></div>
                                <div class="w-full bg-red-600 rounded-t-sm h-[85%] animate-pulse delay-300 dark:bg-red-600"></div>
                            </div>
                        </div>

                        <div class="absolute -top-6 -right-6 w-24 h-24 bg-white rounded-2xl shadow-xl z-30 flex items-center justify-center border border-gray-100 animate-bounce-slow dark:bg-gray-800 dark:border-gray-700">
                             <span class="text-2xl font-bold text-gray-800 dark:text-white">4.0</span>
                        </div>
                        <div class="absolute -bottom-4 -left-4 w-full h-full bg-red-600 rounded-3xl -z-10 opacity-20 transform rotate-3"></div>
                    </div>
                </div>
            </div>
        </main>

        <footer class="relative z-10 py-6 text-center">
            <p class="text-sm text-gray-400">© 2024 Telkom Indonesia. All rights reserved.</p>
        </footer>
    </div>
</template>

<style scoped>
/* Custom animations */
.animate-fade-in-up {
    animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1);
}

.animate-bounce-slow {
    animation: bounce 4s infinite;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes bounce {
    0%, 100% {
        transform: translateY(-5%);
    }
    50% {
        transform: translateY(5%);
    }
}
</style>

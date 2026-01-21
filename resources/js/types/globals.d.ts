import { PageProps as InertiaPageProps } from '@inertiajs/core';
import { AxiosInstance } from 'axios';
import ziggyRoute, { Config as ZiggyConfig } from 'ziggy-js';
import { AppPageProps } from '@/types/index';

// 1. Extend ImportMeta interface for Vite (Kode Lama Anda)
declare module 'vite/client' {
    interface ImportMetaEnv {
        readonly VITE_APP_NAME: string;
        [key: string]: string | boolean | undefined;
    }

    interface ImportMeta {
        readonly env: ImportMetaEnv;
        readonly glob: <T>(pattern: string) => Record<string, () => Promise<T>>;
    }
}

// 2. Definisi Global untuk 'route', 'Ziggy', dan 'axios' (INI YANG MEMPERBAIKI ERROR)
declare global {
    interface Window {
        axios: AxiosInstance;
    }

    /* Mendefinisikan fungsi route() secara global */
    var route: typeof ziggyRoute;
    var Ziggy: ZiggyConfig;
}

// 3. Extend Inertia PageProps (Kode Lama Anda)
declare module '@inertiajs/core' {
    interface PageProps extends InertiaPageProps, AppPageProps {}
}

// 4. Extend Vue Component Properties (Kode Lama Anda + route)
declare module 'vue' {
    import { Router, Page, createHeadManager } from '@inertiajs/core'; // Pastikan import ini ada jika tipe digunakan di bawah

    interface ComponentCustomProperties {
        $inertia: Router;
        $page: Page<PageProps>;
        $headManager: ReturnType<typeof createHeadManager>;

        // Tambahkan route agar bisa dipakai di template tanpa error
        route: typeof ziggyRoute;
    }
}

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Calculator, FileText, Home, LogOut, Settings, ShoppingBasket, User } from 'lucide-vue-next';

import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarHeader,
    SidebarInset,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarProvider,
    SidebarTrigger,
} from '@/components/ui/sidebar';
</script>

<template>
    <SidebarProvider>
        <Sidebar>
            <!-- Header dengan gradient subtle -->
            <SidebarHeader class="border-b p-6">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-blue-600">
                        <span class="text-lg font-bold text-white">A</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-bold">Admin Panel</h2>
                        <p class="text-xs text-muted-foreground">Management System</p>
                    </div>
                </div>
            </SidebarHeader>

            <SidebarContent class="px-2 py-4">
                <!-- Menu Utama -->
                <SidebarGroup>
                    <SidebarGroupLabel class="px-2 text-xs font-semibold text-muted-foreground"> Menu Utama </SidebarGroupLabel>
                    <SidebarGroupContent>
                        <SidebarMenu class="gap-1">
                            <SidebarMenuItem>
                                <SidebarMenuButton as-child class="transition-colors hover:bg-accent">
                                    <Link href="/home" class="flex items-center gap-3 px-3 py-2">
                                        <Home class="h-4 w-4" /> <span class="text-sm font-medium">Dashboard</span>
                                    </Link>
                                    <Link href="/kalkulator-indibiz" class="flex items-center gap-3 px-3 py-2">
                                        <Calculator class="h-4 w-4" /> <span class="text-sm font-medium">Kalkulator Indibiz</span>
                                    </Link>
                                    <Link href="/kalkulator-nonpots" class="flex items-center gap-3 px-3 py-2">
                                        <Calculator class="h-4 w-4" /> <span class="text-sm font-medium">Kalkulator Non Pots</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>

                <!-- Menu Pengaturan -->
                <SidebarGroup>
                    <SidebarGroupLabel class="mt-4 px-2 text-xs font-semibold text-muted-foreground"> Sistem </SidebarGroupLabel>
                    <SidebarGroupContent>
                        <SidebarMenu class="gap-1">
                            <SidebarMenuItem>
                                <SidebarMenuButton as-child class="transition-colors hover:bg-accent">
                                    <Link href="/produk" class="flex items-center gap-3 px-3 py-2">
                                        <ShoppingBasket class="h-4 w-4" /> <span class="text-sm font-medium">Produk</span>
                                    </Link>

                                    <Link href="/laporan" class="flex items-center gap-3 px-3 py-2">
                                        <FileText class="h-4 w-4" /> <span class="text-sm font-medium">Laporan</span>
                                    </Link>

                                    <Link href="/user" class="flex items-center gap-3 px-3 py-2">
                                        <User />
                                        <span class="text-sm font-medium">User</span>
                                    </Link>
                                    <Link href="/pengaturan" class="flex items-center gap-3 px-3 py-2">
                                        <Settings />
                                        <span class="text-sm font-medium">Pengaturan</span>
                                    </Link>
                                </SidebarMenuButton>
                            </SidebarMenuItem>
                        </SidebarMenu>
                    </SidebarGroupContent>
                </SidebarGroup>
            </SidebarContent>

            <!-- Footer dengan hover effect -->
            <SidebarFooter class="border-t p-4">
                <SidebarMenuButton class="w-full transition-colors hover:bg-destructive/10 hover:text-destructive">
                    <LogOut class="h-4 w-4" />
                    <span class="text-sm font-medium">Logout</span>
                </SidebarMenuButton>
            </SidebarFooter>
        </Sidebar>

        <SidebarInset>
            <!-- Header yang lebih clean -->
            <header
                class="sticky top-0 z-10 flex h-14 items-center gap-4 border-b bg-background/95 px-4 backdrop-blur supports-[backdrop-filter]:bg-background/60 lg:h-16 lg:px-6"
            >
                <SidebarTrigger class="rounded-md transition-colors hover:bg-accent" />
                <div class="flex-1">
                    <h1 class="text-lg font-semibold lg:text-xl">Halaman Admin</h1>
                </div>
            </header>

            <!-- Main content dengan max-width untuk readability -->
            <main class="flex-1 p-4 lg:p-6">
                <div class="mx-auto max-w-7xl">
                    <slot />
                </div>
            </main>
        </SidebarInset>
    </SidebarProvider>
</template>

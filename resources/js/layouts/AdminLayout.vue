<script setup>
import { HomeIcon, DocumentTextIcon, BanknotesIcon, UsersIcon, CubeIcon } from '@heroicons/vue/24/outline';
import TopNav from '../components/layout/TopNav.vue';

const mobileLinks = [
    { name: 'dashboard', label: 'Dashboard', icon: HomeIcon },
    { name: 'cotizaciones', label: 'Cotizaciones', icon: DocumentTextIcon },
    { name: 'ventas', label: 'Ventas', icon: BanknotesIcon },
    { name: 'clientes', label: 'Clientes', icon: UsersIcon },
    { name: 'productos', label: 'Productos', icon: CubeIcon },
];
</script>

<template>
    <div class="flex min-h-screen flex-col bg-surface-container-low">
        <TopNav />

        <main class="flex-1 overflow-y-auto p-margin-mobile pb-20 lg:p-sp-lg lg:pb-sp-lg">
            <div class="mx-auto max-w-app">
                <router-view />
            </div>
        </main>

        <!-- Mobile bottom nav: the top nav's tabs wrap awkwardly on narrow screens -->
        <nav class="fixed inset-x-0 bottom-0 z-10 flex border-t border-outline-variant bg-surface-container-lowest lg:hidden">
            <router-link
                v-for="link in mobileLinks"
                :key="link.name"
                :to="{ name: link.name }"
                class="flex flex-1 flex-col items-center gap-1 py-2 text-label-sm text-on-surface-variant"
                active-class="text-primary"
            >
                <component :is="link.icon" class="h-5 w-5" />
                {{ link.label }}
            </router-link>
        </nav>
    </div>
</template>

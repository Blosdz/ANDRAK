<script setup>
import { useRoute, useRouter } from 'vue-router';
import {
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuPortal,
    DropdownMenuRoot,
    DropdownMenuTrigger,
} from 'reka-ui';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';
import { useAuthStore } from '../../stores/auth';

const appName = window.appConfig.appName;
const auth = useAuthStore();
const router = useRouter();
const route = useRoute();

const links = [
    { name: 'dashboard', label: 'Dashboard' },
    { name: 'cotizaciones', label: 'Cotizaciones' },
    { name: 'ventas', label: 'Ventas' },
    { name: 'clientes', label: 'Clientes' },
    { name: 'productos', label: 'Productos' },
];

function isActive(linkName) {
    return route.name === linkName || (typeof route.name === 'string' && route.name.startsWith(`${linkName}.`));
}

async function logout() {
    await auth.logout();
    router.push({ name: 'login' });
}
</script>

<template>
    <header class="flex min-h-16 flex-wrap items-center justify-between gap-2 bg-primary px-margin-mobile py-2 lg:px-sp-md">
        <div class="flex flex-wrap items-center gap-sp-md">
            <div class="flex items-center gap-2.5">
                <span class="flex h-8 w-8 items-center justify-center rounded-md bg-secondary text-label-md font-extrabold text-on-secondary">A</span>
                <span class="font-sans text-body-md font-bold text-on-primary">{{appName}}</span>
            </div>

            <nav class="hidden gap-1 lg:flex lg:flex-wrap">
                <router-link
                    v-for="link in links"
                    :key="link.name"
                    :to="{ name: link.name }"
                    class="rounded-sm px-sp-sm py-2 text-label-md text-inverse-primary transition-colors hover:text-on-primary"
                    :class="isActive(link.name) ? 'bg-[#22345a] text-on-primary' : ''"
                >
                    {{ link.label }}
                </router-link>
            </nav>
        </div>

        <DropdownMenuRoot>
            <DropdownMenuTrigger class="flex items-center gap-2 rounded-sm px-sp-sm py-2 text-label-md text-inverse-primary hover:text-on-primary focus:outline-none">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#22345a] text-label-md font-semibold text-on-primary">
                    {{ auth.usuario?.nombre?.charAt(0)?.toUpperCase() }}
                </span>
                <span class="hidden sm:block">{{ auth.usuario?.nombre }}</span>
                <ChevronDownIcon class="h-4 w-4" />
            </DropdownMenuTrigger>

            <DropdownMenuPortal>
                <DropdownMenuContent
                    align="end"
                    :side-offset="4"
                    class="z-50 w-56 rounded-md border border-outline-variant bg-surface-container-lowest py-1 shadow-[0_4px_12px_rgba(0,27,61,0.1)] focus:outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95"
                >
                    <DropdownMenuItem as-child>
                        <router-link
                            :to="{ name: 'empresa' }"
                            class="block px-sp-sm py-2 text-label-md text-on-surface outline-none data-[highlighted]:bg-surface-container-high"
                        >
                            Configuracion de empresa
                        </router-link>
                    </DropdownMenuItem>
                    <DropdownMenuItem
                        class="block w-full px-sp-sm py-2 text-left text-label-md text-error outline-none data-[highlighted]:bg-surface-container-high"
                        @select="logout"
                    >
                        Cerrar sesion
                    </DropdownMenuItem>
                </DropdownMenuContent>
            </DropdownMenuPortal>
        </DropdownMenuRoot>
    </header>
</template>

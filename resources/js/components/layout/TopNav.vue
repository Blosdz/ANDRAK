<script setup>
import { ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import {
    DialogClose,
    DialogContent,
    DialogOverlay,
    DialogPortal,
    DialogRoot,
    DialogTitle,
    DialogTrigger,
} from 'reka-ui';
import {
    ArrowLeftOnRectangleIcon,
    BanknotesIcon,
    Bars3Icon,
    ChevronDoubleLeftIcon,
    ChevronDoubleRightIcon,
    Cog6ToothIcon,
    CubeIcon,
    DocumentTextIcon,
    Squares2X2Icon,
    UsersIcon,
    XMarkIcon,
} from '@heroicons/vue/24/outline';
import { useAuthStore } from '../../stores/auth';

const appName = window.appConfig.appName;
const auth = useAuthStore();
const router = useRouter();
const route = useRoute();
const menuOpen = ref(false);
const collapsed = ref(false);

const links = [
    { name: 'dashboard', label: 'Dashboard', icon: Squares2X2Icon },
    { name: 'cotizaciones', label: 'Cotizaciones', icon: DocumentTextIcon },
    { name: 'ventas', label: 'Ventas', icon: BanknotesIcon },
    { name: 'clientes', label: 'Clientes', icon: UsersIcon },
    { name: 'productos', label: 'Productos', icon: CubeIcon },
    { name: 'empresa', label: 'Configuración de empresa', icon: Cog6ToothIcon },
];

function isActive(linkName) {
    return route.name === linkName || (typeof route.name === 'string' && route.name.startsWith(`${linkName}.`));
}

watch(() => route.fullPath, () => {
    menuOpen.value = false;
});

async function logout() {
    menuOpen.value = false;
    await auth.logout();
    router.push({ name: 'login' });
}
</script>

<template>
    <aside class="sticky top-0 hidden h-screen shrink-0 flex-col border-r border-outline-variant bg-surface-container-lowest transition-[width] duration-200 lg:flex"
        :class="collapsed ? 'w-20' : 'w-64'">
        <div class="flex min-h-16 items-center border-b border-outline-variant px-sp-sm" :class="collapsed ? 'justify-center' : 'justify-between'">
            <router-link v-if="!collapsed" :to="{ name: 'dashboard' }" class="flex min-w-0 items-center gap-sp-sm">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary font-sans text-label-md font-extrabold text-white">A</span>
                <span class="truncate font-sans text-body-lg font-bold text-on-surface">{{ appName }}</span>
            </router-link>
            <button type="button" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-md text-on-surface-variant hover:bg-surface-container-high focus-visible:outline-2 focus-visible:outline-primary"
                :aria-label="collapsed ? 'Expandir barra lateral' : 'Contraer barra lateral'" :aria-expanded="!collapsed" @click="collapsed = !collapsed">
                <ChevronDoubleRightIcon v-if="collapsed" class="h-5 w-5" />
                <ChevronDoubleLeftIcon v-else class="h-5 w-5" />
            </button>
        </div>

        <nav aria-label="Navegación principal" class="flex-1 overflow-y-auto p-sp-sm">
            <router-link v-for="link in links" :key="link.name" :to="{ name: link.name }"
                class="mb-1 flex min-h-11 items-center gap-3 rounded-md py-3 text-body-md font-medium transition-colors"
                :class="[collapsed ? 'justify-center px-2' : 'px-sp-md', isActive(link.name) ? 'bg-primary !text-white hover:bg-primary' : 'text-on-surface hover:bg-surface-container-high']"
                :aria-label="collapsed ? link.label : undefined" :title="collapsed ? link.label : undefined">
                <component :is="link.icon" class="h-5 w-5 shrink-0" />
                <span v-if="!collapsed" class="truncate">{{ link.label }}</span>
            </router-link>
        </nav>

        <div class="border-t border-outline-variant" :class="collapsed ? 'p-sp-sm' : 'p-sp-md'">
            <p v-if="!collapsed" class="mb-sp-sm truncate text-label-md text-on-surface-variant">{{ auth.usuario?.nombre }}</p>
            <button type="button" class="flex min-h-11 w-full items-center gap-3 rounded-md py-3 text-body-md font-medium text-error hover:bg-surface-container-high"
                :class="collapsed ? 'justify-center px-2' : 'px-sp-md'" :aria-label="collapsed ? 'Cerrar sesión' : undefined" :title="collapsed ? 'Cerrar sesión' : undefined" @click="logout">
                <ArrowLeftOnRectangleIcon class="h-5 w-5 shrink-0" />
                <span v-if="!collapsed">Cerrar sesión</span>
            </button>
        </div>
    </aside>

    <header class="flex min-h-16 items-center justify-between border-b border-outline-variant bg-surface-container-lowest px-margin-mobile lg:hidden">
        <router-link :to="{ name: 'dashboard' }" class="flex min-w-0 items-center gap-sp-sm">
            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-md bg-primary font-sans text-label-md font-extrabold text-white">A</span>
            <span class="truncate font-sans text-body-lg font-bold text-on-surface">{{ appName }}</span>
        </router-link>

        <DialogRoot v-model:open="menuOpen">
            <DialogTrigger as-child>
                <button type="button" class="flex h-10 items-center gap-2 rounded-md border border-outline-variant px-sp-sm text-label-md font-semibold text-on-surface transition-colors hover:bg-surface-container-high focus-visible:outline-2 focus-visible:outline-primary"
                    aria-label="Abrir menú de navegación">
                    <Bars3Icon class="h-5 w-5" />
                    <span class="hidden sm:inline">Menú</span>
                </button>
            </DialogTrigger>

            <DialogPortal>
                <DialogOverlay class="fixed inset-0 z-40 bg-on-surface/40" />
                <DialogContent class="fixed inset-y-0 left-0 z-50 flex w-full max-w-xs flex-col border-r border-outline-variant bg-surface-container-lowest shadow-xl focus:outline-none">
                    <div class="flex items-center justify-between border-b border-outline-variant px-sp-md py-sp-md">
                        <DialogTitle class="font-sans text-title-lg font-bold text-on-surface">Navegación</DialogTitle>
                        <DialogClose class="rounded-md p-2 text-on-surface-variant hover:bg-surface-container-high" aria-label="Cerrar menú">
                            <XMarkIcon class="h-5 w-5" />
                        </DialogClose>
                    </div>

                    <nav aria-label="Navegación principal" class="flex-1 overflow-y-auto p-sp-sm">
                        <router-link v-for="link in links" :key="link.name" :to="{ name: link.name }"
                            class="mb-1 block rounded-md px-sp-md py-3 text-body-md font-medium transition-colors"
                            :class="isActive(link.name) ? 'bg-primary !text-white hover:bg-primary' : 'text-on-surface hover:bg-surface-container-high'"
                            @click="menuOpen = false">
                            {{ link.label }}
                        </router-link>
                    </nav>

                    <div class="border-t border-outline-variant p-sp-md">
                        <p class="mb-sp-sm truncate text-label-md text-on-surface-variant">{{ auth.usuario?.nombre }}</p>
                        <button type="button" class="w-full rounded-md px-sp-md py-3 text-left text-body-md font-medium text-error hover:bg-surface-container-high" @click="logout">
                            Cerrar sesión
                        </button>
                    </div>
                </DialogContent>
            </DialogPortal>
        </DialogRoot>
    </header>
</template>

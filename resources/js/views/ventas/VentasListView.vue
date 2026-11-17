<script setup>
import { h, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { EyeIcon, ArrowDownTrayIcon } from '@heroicons/vue/24/outline';
import http from '../../http';
import { downloadPdf } from '../../utils/download';
import DataTable from '../../components/DataTable.vue';
import Button from '../../components/ui/button/Button.vue';
import Input from '../../components/ui/input/Input.vue';
import PageEyebrow from '../../components/PageEyebrow.vue';
import DropdownMenu from '../../components/ui/dropdown-menu/DropdownMenu.vue';

const router = useRouter();

const ventas = ref([]);
const meta = ref(null);
const loading = ref(false);
const buscar = ref('');
const page = ref(1);

function money(monto, moneda) {
    return `${moneda} ${Number(monto).toFixed(2)}`;
}

async function fetchVentas() {
    loading.value = true;
    try {
        const { data } = await http.get('/ventas', {
            params: { buscar: buscar.value || undefined, page: page.value },
        });
        ventas.value = data.data;
        meta.value = data;
    } finally {
        loading.value = false;
    }
}

let debounceHandle;
watch(buscar, () => {
    clearTimeout(debounceHandle);
    debounceHandle = setTimeout(() => {
        page.value = 1;
        fetchVentas();
    }, 300);
});

function onPageChange(newPage) {
    page.value = newPage;
    fetchVentas();
}

const columns = [
    { id: 'numero', accessorKey: 'numero', header: 'Numero' },
    { id: 'cliente', header: 'Cliente', cell: (info) => info.row.original.cliente?.nombre || '-' },
    { id: 'total', header: 'Total', cell: (info) => money(info.row.original.monto_final, info.row.original.moneda) },
    { id: 'moneda', accessorKey: 'moneda', header: 'Moneda' },
    { id: 'fecha', header: 'Fecha', cell: (info) => new Date(info.row.original.created_at).toLocaleDateString() },
    { id: 'cotizacion', header: 'Cotizacion Relacionada', cell: (info) => info.row.original.cotizacion?.numero || '-' },
    {
        id: 'acciones',
        header: 'Acciones',
        cell: (info) => {
            const venta = info.row.original;
            return h('div', { class: 'flex gap-1' }, [
                h(Button, { variant: 'ghost', size: 'icon', title: 'Ver', onClick: () => router.push({ name: 'ventas.show', params: { id: venta.id } }) }, () => h(EyeIcon, { class: 'h-4 w-4' })),
                h(DropdownMenu, {
                    items: [
                        { label: 'Descargar completo', onSelect: () => downloadPdf(`/ventas/${venta.id}/pdf`, `Contrato-${venta.numero}.pdf`) },
                        { label: 'Descargar recibo', onSelect: () => downloadPdf(`/ventas/${venta.id}/pdf?tipo=recibo`, `Recibo-${venta.numero}.pdf`) },
                    ],
                }, {
                    trigger: () => h(Button, { variant: 'ghost', size: 'icon', title: 'Descargar' }, () => h(ArrowDownTrayIcon, { class: 'h-4 w-4' })),
                }),
            ]);
        },
    },
];

onMounted(fetchVentas);
</script>

<template>
    <div class="flex flex-col gap-sp-md">
        <div>
            <PageEyebrow>Panel de gestion</PageEyebrow>
            <h1 class="text-headline-lg-mobile text-on-surface lg:text-headline-lg">Ventas</h1>
        </div>

        <Input v-model="buscar" type="search" placeholder="Buscar por numero o cliente..." class="max-w-sm" />

        <DataTable :columns="columns" :data="ventas" :loading="loading" :meta="meta" @page-change="onPageChange" />
    </div>
</template>

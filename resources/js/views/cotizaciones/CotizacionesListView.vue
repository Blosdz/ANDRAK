<script setup>
import { h, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { EyeIcon, PencilIcon, ArrowDownTrayIcon, ArrowsRightLeftIcon, PlusIcon } from '@heroicons/vue/24/outline';
import http from '../../http';
import { downloadPdf } from '../../utils/download';
import DataTable from '../../components/DataTable.vue';
import Badge from '../../components/Badge.vue';
import PageEyebrow from '../../components/PageEyebrow.vue';
import FormSelect from '../../components/form/FormSelect.vue';
import Button from '../../components/ui/button/Button.vue';
import Input from '../../components/ui/input/Input.vue';
import CotizacionFormModal from './CotizacionFormModal.vue';

const router = useRouter();

const cotizaciones = ref([]);
const meta = ref(null);
const loading = ref(false);
const buscar = ref('');
const estado = ref('');
const page = ref(1);
const showCrear = ref(false);

function money(monto, moneda) {
    return `${moneda} ${Number(monto).toFixed(2)}`;
}

function resumenProductos(lineas) {
    if (!lineas.length) return '-';
    if (lineas.length === 1) return lineas[0].nombre;
    return `${lineas[0].nombre} +${lineas.length - 1} mas`;
}

function totalCantidad(lineas) {
    return lineas.reduce((sum, l) => sum + l.cantidad, 0);
}

async function fetchCotizaciones() {
    loading.value = true;
    try {
        const { data } = await http.get('/cotizaciones', {
            params: { buscar: buscar.value || undefined, estado: estado.value || undefined, page: page.value },
        });
        cotizaciones.value = data.data;
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
        fetchCotizaciones();
    }, 300);
});

watch(estado, () => {
    page.value = 1;
    fetchCotizaciones();
});

function onPageChange(newPage) {
    page.value = newPage;
    fetchCotizaciones();
}

function onCotizacionCreada() {
    showCrear.value = false;
    page.value = 1;
    fetchCotizaciones();
}

async function convertir(cotizacion) {
    try {
        await http.post(`/cotizaciones/${cotizacion.id}/convertir-venta`, {});
        await fetchCotizaciones();
    } catch (e) {
        alert(e.response?.data?.message || 'No se pudo convertir la cotizacion.');
    }
}

const columns = [
    { id: 'numero', accessorKey: 'numero', header: 'Numero' },
    { id: 'cliente', header: 'Cliente', cell: (info) => info.row.original.cliente?.nombre || '-' },
    { id: 'producto', header: 'Producto', cell: (info) => resumenProductos(info.row.original.lineas) },
    { id: 'cantidad', header: 'Cantidad', cell: (info) => totalCantidad(info.row.original.lineas) },
    { id: 'moneda', accessorKey: 'moneda', header: 'Moneda' },
    { id: 'monto', header: 'Monto', cell: (info) => money(info.row.original.total, info.row.original.moneda) },
    { id: 'estado', header: 'Estado', cell: (info) => h(Badge, { value: info.row.original.estado }) },
    { id: 'fecha', header: 'Fecha', cell: (info) => new Date(info.row.original.created_at).toLocaleDateString() },
    {
        id: 'acciones',
        header: 'Acciones',
        cell: (info) => {
            const cot = info.row.original;
            const botones = [
                h(Button, { variant: 'ghost', size: 'icon', title: 'Ver', onClick: () => router.push({ name: 'cotizaciones.show', params: { id: cot.id } }) }, () => h(EyeIcon, { class: 'h-4 w-4' })),
                h(Button, { variant: 'ghost', size: 'icon', title: 'Descargar PDF', onClick: () => downloadPdf(`/cotizaciones/${cot.id}/pdf`, `${cot.numero}.pdf`) }, () => h(ArrowDownTrayIcon, { class: 'h-4 w-4' })),
            ];

            if (cot.estado !== 'convertida') {
                botones.splice(1, 0, h(Button, { variant: 'ghost', size: 'icon', title: 'Editar', onClick: () => router.push({ name: 'cotizaciones.editar', params: { id: cot.id } }) }, () => h(PencilIcon, { class: 'h-4 w-4' })));
                botones.push(h(Button, { variant: 'ghost', size: 'icon', class: 'text-secondary hover:bg-secondary-container', title: 'Convertir en Venta', onClick: () => convertir(cot) }, () => h(ArrowsRightLeftIcon, { class: 'h-4 w-4' })));
            }

            return h('div', { class: 'flex gap-1' }, botones);
        },
    },
];

onMounted(fetchCotizaciones);
</script>

<template>
    <div class="flex flex-col gap-sp-md">
        <div class="flex flex-col gap-sp-sm sm:flex-row sm:items-end sm:justify-between">
            <div>
                <PageEyebrow>Panel de gestion</PageEyebrow>
                <h1 class="text-headline-lg-mobile text-on-surface lg:text-headline-lg">Cotizaciones</h1>
            </div>
            <Button @click="showCrear = true">
                <PlusIcon class="h-5 w-5" /> Nueva Cotizacion
            </Button>
        </div>

        <CotizacionFormModal :open="showCrear" @close="showCrear = false" @saved="onCotizacionCreada" />

        <div class="flex flex-col gap-sp-sm sm:flex-row">
            <Input v-model="buscar" type="search" placeholder="Buscar por numero o cliente..." class="max-w-sm" />
            <FormSelect
                v-model="estado"
                class="max-w-xs"
                :options="[
                    { value: '', label: 'Todos los estados' },
                    { value: 'borrador', label: 'Borrador' },
                    { value: 'enviada', label: 'Enviada' },
                    { value: 'aceptada', label: 'Aceptada' },
                    { value: 'rechazada', label: 'Rechazada' },
                    { value: 'convertida', label: 'Convertida' },
                ]"
            />
        </div>

        <DataTable :columns="columns" :data="cotizaciones" :loading="loading" :meta="meta" @page-change="onPageChange" />
    </div>
</template>

<script setup>
import { h, onMounted, ref, watch } from 'vue';
import { PencilIcon, EyeIcon, TrashIcon, CubeIcon, PlusIcon, ArrowDownTrayIcon, ArrowUpTrayIcon } from '@heroicons/vue/24/outline';
import http from '../../http';
import { downloadExcel } from '../../utils/download';
import DataTable from '../../components/DataTable.vue';
import Badge from '../../components/Badge.vue';
import CodigoTag from '../../components/CodigoTag.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import ProductoModal from './ProductoModal.vue';
import Button from '../../components/ui/button/Button.vue';
import Input from '../../components/ui/input/Input.vue';
import PageEyebrow from '../../components/PageEyebrow.vue';
import Alert from '../../components/ui/alert/Alert.vue';
import AlertTitle from '../../components/ui/alert/AlertTitle.vue';
import AlertDescription from '../../components/ui/alert/AlertDescription.vue';

const productos = ref([]);
const meta = ref(null);
const loading = ref(false);
const buscar = ref('');
const page = ref(1);

const showModal = ref(false);
const modalMode = ref('crear'); // crear | ver | editar
const selected = ref(null);

const showConfirm = ref(false);
const deleting = ref(null);
const deletingLoading = ref(false);

const importInput = ref(null);
const importing = ref(false);
const importResultado = ref(null);

const tipoCambio = ref(null);
async function cargarTipoCambio() {
    try {
        const { data } = await http.get('/tipo-cambio');
        tipoCambio.value = data;
    } catch (e) {
        tipoCambio.value = null;
    }
}

function money(precio, moneda) {
    return `${moneda} ${Number(precio).toFixed(2)}`;
}

function equivalentePen(precio, moneda) {
    if (moneda !== 'USD' || !tipoCambio.value) return null;
    return Number(precio) * tipoCambio.value.venta;
}

async function fetchProductos() {
    loading.value = true;
    try {
        const { data } = await http.get('/productos', {
            params: { buscar: buscar.value || undefined, page: page.value },
        });
        productos.value = data.data;
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
        fetchProductos();
    }, 300);
});

function onPageChange(newPage) {
    page.value = newPage;
    fetchProductos();
}

function abrirCrear() {
    selected.value = null;
    modalMode.value = 'crear';
    showModal.value = true;
}

function abrirVer(producto) {
    selected.value = producto;
    modalMode.value = 'ver';
    showModal.value = true;
}

function abrirEditar(producto) {
    selected.value = producto;
    modalMode.value = 'editar';
    showModal.value = true;
}

function pedirEliminar(producto) {
    deleting.value = producto;
    showConfirm.value = true;
}

async function confirmarEliminar() {
    deletingLoading.value = true;
    try {
        await http.delete(`/productos/${deleting.value.id}`);
        showConfirm.value = false;
        deleting.value = null;
        await fetchProductos();
    } finally {
        deletingLoading.value = false;
    }
}

function onSaved() {
    showModal.value = false;
    fetchProductos();
}

async function descargarPlantilla() {
    await downloadExcel('/productos/plantilla-excel', 'plantilla-productos.xlsx');
}

function abrirImportar() {
    importResultado.value = null;
    importInput.value?.click();
}

async function onImportarArchivo(event) {
    const file = event.target.files?.[0];
    event.target.value = '';
    if (!file) return;

    const formData = new FormData();
    formData.append('archivo', file);

    importing.value = true;
    importResultado.value = null;
    try {
        const { data } = await http.post('/productos/importar-excel', formData);
        importResultado.value = data;
        if (data.creados > 0) await fetchProductos();
    } catch (e) {
        importResultado.value = { creados: 0, errores: [{ fila: '-', mensaje: e.response?.data?.message || 'No se pudo importar el archivo.' }] };
    } finally {
        importing.value = false;
    }
}

const columns = [
    {
        id: 'imagen',
        header: 'Imagen',
        meta: { hideOnCard: true },
        cell: () => h('div', { class: 'flex h-10 w-10 items-center justify-center bg-surface-container-high text-on-surface-variant' }, [
            h(CubeIcon, { class: 'h-5 w-5' }),
        ]),
    },
    { id: 'nombre', accessorKey: 'nombre', header: 'Nombre' },
    { id: 'codigo', accessorKey: 'codigo', header: 'Codigo', cell: (info) => (info.getValue() ? h(CodigoTag, { codigo: info.getValue() }) : '-') },
    {
        id: 'descripcion',
        accessorKey: 'descripcion',
        header: 'Descripcion',
        cell: (info) => h('span', { class: 'line-clamp-1 max-w-xs' }, info.getValue() || '-'),
    },
    {
        id: 'stock',
        header: 'Stock',
        cell: (info) => {
            const almacen = info.row.original.almacen;
            if (!almacen) return '-';
            const bajo = almacen.cantidad_disponible <= almacen.stock_minimo;
            return h(Badge, { value: String(almacen.cantidad_disponible), map: bajo ? { [almacen.cantidad_disponible]: 'bg-error-container text-on-error-container' } : {} });
        },
    },
    {
        id: 'precio',
        header: 'Precio',
        cell: (info) => {
            const equivalente = equivalentePen(info.row.original.precio, info.row.original.moneda);
            return h('div', [
                h('span', money(info.row.original.precio, info.row.original.moneda)),
                equivalente !== null
                    ? h('div', { class: 'text-label-sm text-on-surface-variant' }, `S/ ${equivalente.toFixed(2)}`)
                    : null,
            ]);
        },
    },
    {
        id: 'estado',
        header: 'Estado',
        cell: (info) => h(Badge, { value: info.row.original.estado }),
    },
    {
        id: 'acciones',
        header: 'Acciones',
        cell: (info) => h('div', { class: 'flex gap-1' }, [
            h(Button, { variant: 'ghost', size: 'icon', onClick: () => abrirVer(info.row.original) }, () => h(EyeIcon, { class: 'h-4 w-4' })),
            h(Button, { variant: 'ghost', size: 'icon', onClick: () => abrirEditar(info.row.original) }, () => h(PencilIcon, { class: 'h-4 w-4' })),
            h(Button, { variant: 'ghost', size: 'icon', class: 'text-error hover:bg-error-container', onClick: () => pedirEliminar(info.row.original) }, () => h(TrashIcon, { class: 'h-4 w-4' })),
        ]),
    },
];

onMounted(() => {
    fetchProductos();
    cargarTipoCambio();
});
</script>

<template>
    <div class="flex flex-col gap-sp-md">
        <div class="flex flex-col gap-sp-sm sm:flex-row sm:items-end sm:justify-between">
            <div>
                <PageEyebrow>Catalogo e inventario</PageEyebrow>
                <h1 class="text-headline-lg-mobile text-on-surface lg:text-headline-lg">Productos</h1>
            </div>
            <div class="flex flex-wrap gap-2">
                <Button variant="outline" @click="descargarPlantilla">
                    <ArrowDownTrayIcon class="h-5 w-5" /> Plantilla Excel
                </Button>
                <Button variant="outline" :disabled="importing" @click="abrirImportar">
                    <ArrowUpTrayIcon class="h-5 w-5" /> {{ importing ? 'Importando...' : 'Importar Excel' }}
                </Button>
                <input ref="importInput" type="file" accept=".xlsx,.xls,.csv" class="hidden" @change="onImportarArchivo">
                <Button @click="abrirCrear">
                    <PlusIcon class="h-5 w-5" /> Nuevo Producto
                </Button>
            </div>
        </div>

        <Alert v-if="importResultado" :variant="importResultado.errores.length ? 'warning' : 'success'">
            <AlertTitle>
                {{ importResultado.creados }} producto{{ importResultado.creados === 1 ? '' : 's' }} importado{{ importResultado.creados === 1 ? '' : 's' }}
                <template v-if="importResultado.errores.length">, {{ importResultado.errores.length }} con errores</template>
            </AlertTitle>
            <AlertDescription v-if="importResultado.errores.length">
                <ul class="list-disc pl-4">
                    <li v-for="(err, i) in importResultado.errores" :key="i">Fila {{ err.fila }}: {{ err.mensaje }}</li>
                </ul>
            </AlertDescription>
        </Alert>

        <Input v-model="buscar" type="search" placeholder="Buscar por nombre o codigo..." class="max-w-sm" />

        <DataTable :columns="columns" :data="productos" :loading="loading" :meta="meta" @page-change="onPageChange" />

        <ProductoModal
            :open="showModal"
            :mode="modalMode"
            :producto-id="selected?.id"
            @close="showModal = false"
            @saved="onSaved"
        />

        <ConfirmDialog
            :open="showConfirm"
            :message="`Se eliminara el producto '${deleting?.nombre}'. Esta accion se puede revertir solo desde la base de datos.`"
            :loading="deletingLoading"
            @close="showConfirm = false"
            @confirm="confirmarEliminar"
        />
    </div>
</template>

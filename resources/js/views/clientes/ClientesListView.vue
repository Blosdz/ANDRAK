<script setup>
import { h, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { PencilIcon, EyeIcon, TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
import http from '../../http';
import DataTable from '../../components/DataTable.vue';
import ConfirmDialog from '../../components/ConfirmDialog.vue';
import ClienteFormModal from './ClienteFormModal.vue';
import Button from '../../components/ui/button/Button.vue';
import Input from '../../components/ui/input/Input.vue';
import PageEyebrow from '../../components/PageEyebrow.vue';

const router = useRouter();

const clientes = ref([]);
const meta = ref(null);
const loading = ref(false);
const buscar = ref('');
const page = ref(1);

const showModal = ref(false);
const selected = ref(null);

const showConfirm = ref(false);
const deleting = ref(null);
const deletingLoading = ref(false);

async function fetchClientes() {
    loading.value = true;
    try {
        const { data } = await http.get('/clientes', {
            params: { buscar: buscar.value || undefined, page: page.value },
        });
        clientes.value = data.data;
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
        fetchClientes();
    }, 300);
});

function onPageChange(newPage) {
    page.value = newPage;
    fetchClientes();
}

function abrirCrear() {
    selected.value = null;
    showModal.value = true;
}

function abrirEditar(cliente) {
    selected.value = cliente;
    showModal.value = true;
}

function verDetalle(cliente) {
    router.push({ name: 'clientes.show', params: { id: cliente.id } });
}

function pedirEliminar(cliente) {
    deleting.value = cliente;
    showConfirm.value = true;
}

async function confirmarEliminar() {
    deletingLoading.value = true;
    try {
        await http.delete(`/clientes/${deleting.value.id}`);
        showConfirm.value = false;
        deleting.value = null;
        await fetchClientes();
    } finally {
        deletingLoading.value = false;
    }
}

function onSaved() {
    showModal.value = false;
    fetchClientes();
}

const columns = [
    { id: 'nombre', accessorKey: 'nombre', header: 'Nombre' },
    { id: 'ruc', accessorKey: 'ruc', header: 'RUC', cell: (info) => info.getValue() || '-' },
    { id: 'empresa', accessorKey: 'empresa', header: 'Empresa', cell: (info) => info.getValue() || '-' },
    { id: 'telefono', accessorKey: 'telefono', header: 'Telefono', cell: (info) => info.getValue() || '-' },
    { id: 'correo', accessorKey: 'correo', header: 'Correo', cell: (info) => info.getValue() || '-' },
    { id: 'cotizaciones', header: 'Cotizaciones', cell: (info) => info.row.original.cotizaciones_count },
    { id: 'ventas', header: 'Ventas', cell: (info) => info.row.original.ventas_count },
    {
        id: 'acciones',
        header: 'Acciones',
        cell: (info) => h('div', { class: 'flex gap-1' }, [
            h(Button, { variant: 'ghost', size: 'icon', onClick: () => verDetalle(info.row.original) }, () => h(EyeIcon, { class: 'h-4 w-4' })),
            h(Button, { variant: 'ghost', size: 'icon', onClick: () => abrirEditar(info.row.original) }, () => h(PencilIcon, { class: 'h-4 w-4' })),
            h(Button, { variant: 'ghost', size: 'icon', class: 'text-error hover:bg-error-container', onClick: () => pedirEliminar(info.row.original) }, () => h(TrashIcon, { class: 'h-4 w-4' })),
        ]),
    },
];

onMounted(fetchClientes);
</script>

<template>
    <div class="flex flex-col gap-sp-md">
        <div class="flex flex-col gap-sp-sm sm:flex-row sm:items-end sm:justify-between">
            <div>
                <PageEyebrow>Directorio</PageEyebrow>
                <h1 class="text-headline-lg-mobile text-on-surface lg:text-headline-lg">Clientes</h1>
            </div>
            <Button @click="abrirCrear">
                <PlusIcon class="h-5 w-5" /> Nuevo Cliente
            </Button>
        </div>

        <Input v-model="buscar" type="search" placeholder="Buscar por nombre, RUC o empresa..." class="max-w-sm" />

        <DataTable :columns="columns" :data="clientes" :loading="loading" :meta="meta" @page-change="onPageChange" />

        <ClienteFormModal :open="showModal" :cliente="selected" @close="showModal = false" @saved="onSaved" />

        <ConfirmDialog
            :open="showConfirm"
            :message="`Se eliminara el cliente '${deleting?.nombre}'. Esta accion se puede revertir solo desde la base de datos.`"
            :loading="deletingLoading"
            @close="showConfirm = false"
            @confirm="confirmarEliminar"
        />
    </div>
</template>

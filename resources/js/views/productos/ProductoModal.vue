<script setup>
import { computed, reactive, ref, watch } from 'vue';
import http from '../../http';
import Modal from '../../components/Modal.vue';
import FormInput from '../../components/form/FormInput.vue';
import FormSelect from '../../components/form/FormSelect.vue';
import Button from '../../components/ui/button/Button.vue';
import CodigoTag from '../../components/CodigoTag.vue';
import { normalizarCodigo } from '../../utils/codigo';

const props = defineProps({
    open: { type: Boolean, required: true },
    mode: { type: String, default: 'crear' }, // crear | ver | editar
    productoId: { type: [String, Number], default: null },
});

const emit = defineEmits(['close', 'saved']);

const internalMode = ref(props.mode);
const loading = ref(false);
const saving = ref(false);
const errors = ref({});
const imagePreview = ref(null);

const form = reactive({
    nombre: '',
    codigo: '',
    descripcion: '',
    categoria: '',
    precio: '',
    moneda: 'PEN',
    estado: 'activo',
    cantidad_disponible: 0,
    stock_minimo: 5,
    dimensiones: '',
    peso: '',
    motor: '',
    img: null,
});

const isReadonly = computed(() => internalMode.value === 'ver');
const title = computed(() => (props.mode === 'crear' ? 'Nuevo Producto' : form.nombre || 'Producto'));
const codigoPreview = computed(() => normalizarCodigo(form.codigo));

function resetForm() {
    Object.assign(form, {
        nombre: '', codigo: '', descripcion: '', categoria: '',
        precio: '', moneda: 'PEN', estado: 'activo',
        cantidad_disponible: 0, stock_minimo: 5,
        dimensiones: '', peso: '', motor: '', img: null,
    });
    imagePreview.value = null;
    errors.value = {};
}

async function cargarProducto() {
    if (!props.productoId) {
        resetForm();
        return;
    }

    loading.value = true;
    try {
        const { data } = await http.get(`/productos/${props.productoId}`);
        form.nombre = data.nombre;
        form.codigo = data.codigo || '';
        form.descripcion = data.descripcion || '';
        form.categoria = data.categoria || '';
        form.precio = data.precio;
        form.moneda = data.moneda;
        form.estado = data.estado;
        form.cantidad_disponible = data.almacen?.cantidad_disponible ?? 0;
        form.stock_minimo = data.almacen?.stock_minimo ?? 5;
        form.dimensiones = data.descripcion_extra?.dimensiones || '';
        form.peso = data.descripcion_extra?.peso || '';
        form.motor = data.descripcion_extra?.motor || '';
        form.img = data.img || null;
        imagePreview.value = data.img || null;
    } finally {
        loading.value = false;
    }
}

watch(() => props.open, (isOpen) => {
    if (isOpen) {
        internalMode.value = props.mode;
        cargarProducto();
    }
});

function onImagePick(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
        form.img = reader.result;
        imagePreview.value = reader.result;
    };
    reader.readAsDataURL(file);
}

async function guardar() {
    saving.value = true;
    errors.value = {};

    const payload = {
        nombre: form.nombre,
        codigo: form.codigo || null,
        descripcion: form.descripcion || null,
        categoria: form.categoria || null,
        precio: form.precio,
        moneda: form.moneda,
        estado: form.estado,
        cantidad_disponible: form.cantidad_disponible,
        stock_minimo: form.stock_minimo,
        img: form.img,
        descripcion_extra: {
            dimensiones: form.dimensiones || null,
            peso: form.peso || null,
            motor: form.motor || null,
        },
    };

    try {
        let creado;
        if (props.mode === 'crear') {
            const { data } = await http.post('/productos', payload);
            creado = data;
        } else {
            const { data } = await http.put(`/productos/${props.productoId}`, payload);
            creado = data;
        }

        emit('saved', creado);
    } catch (e) {
        errors.value = e.response?.data?.errors || {};
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Modal :open="open" :title="title" max-width="max-w-4xl" @close="$emit('close')">
        <div v-if="loading" class="py-sp-lg text-center text-on-surface-variant">Cargando...</div>

        <div v-else class="flex flex-col gap-sp-md">
            <div class="grid grid-cols-1 gap-sp-md sm:grid-cols-[auto_1fr]">
                <label class="block">
                    <span class="text-field-label text-on-surface-variant">Imagen (opcional)</span>
                    <div
                        class="mt-1 flex h-28 w-28 items-center justify-center overflow-hidden rounded-md border-2 border-dashed border-outline-variant"
                        :style="!imagePreview ? { backgroundImage: 'repeating-linear-gradient(45deg, var(--color-surface-container-low), var(--color-surface-container-low) 10px, var(--color-surface-container) 10px, var(--color-surface-container) 20px)' } : {}"
                    >
                        <img v-if="imagePreview" :src="imagePreview" class="h-full w-full object-cover" alt="">
                        <span v-else class="px-2 text-center text-label-sm text-outline">Arrastra aqui</span>
                    </div>
                    <input v-if="!isReadonly" type="file" accept="image/*" class="mt-2 w-28 text-label-sm text-on-surface-variant" @change="onImagePick">
                </label>

                <div class="grid grid-cols-1 gap-sp-md sm:grid-cols-2">
                    <FormInput v-model="form.nombre" label="Nombre" required :disabled="isReadonly" :error="errors.nombre?.[0]" />
                    <div>
                        <FormInput v-model="form.codigo" label="Codigo" placeholder="#maquinaria_pesada" :disabled="isReadonly" :error="errors.codigo?.[0]" />
                        <div class="mt-1 flex items-center gap-2">
                            <span class="text-label-sm text-outline">Se usa para buscar. Vista previa:</span>
                            <CodigoTag v-if="codigoPreview" :codigo="codigoPreview" />
                            <span v-else class="text-label-sm text-outline">—</span>
                        </div>
                    </div>
                    <FormInput v-model="form.categoria" label="Categoria" :disabled="isReadonly" />
                    <FormSelect v-model="form.estado" label="Estado" :options="[{ value: 'activo', label: 'Activo' }, { value: 'inactivo', label: 'Inactivo' }]" :disabled="isReadonly" />
                </div>
            </div>

            <label class="block">
                <span class="text-field-label text-on-surface-variant">Descripcion <span class="text-outline">(opcional)</span></span>
                <textarea
                    v-model="form.descripcion"
                    :disabled="isReadonly"
                    rows="2"
                    class="mt-1 w-full rounded-sm border border-outline-variant bg-surface-container-lowest px-sp-sm py-2 text-body-md text-on-surface focus:border-primary focus:ring-2 focus:ring-primary/15 focus:outline-none"
                />
            </label>

            <div class="grid grid-cols-2 gap-sp-md sm:grid-cols-4">
                <FormInput v-model="form.precio" type="number" step="0.01" label="Precio unitario" required :disabled="isReadonly" :error="errors.precio?.[0]" />
                <FormSelect v-model="form.moneda" label="Moneda" required :options="[{ value: 'PEN', label: 'PEN' }, { value: 'USD', label: 'USD' }]" :disabled="isReadonly" />
                <FormInput v-model="form.cantidad_disponible" type="number" label="Stock actual" :disabled="isReadonly" />
                <FormInput v-model="form.stock_minimo" type="number" label="Stock minimo" :disabled="isReadonly" />
            </div>

            <div class="grid grid-cols-1 gap-sp-md sm:grid-cols-3">
                <FormInput v-model="form.dimensiones" label="Dimensiones" :disabled="isReadonly" />
                <FormInput v-model="form.peso" label="Peso" :disabled="isReadonly" />
                <FormInput v-model="form.motor" label="Motor" :disabled="isReadonly" />
            </div>

            <div class="flex justify-end gap-2 border-t border-outline-variant pt-sp-sm">
                <Button v-if="isReadonly" @click="internalMode = 'editar'">Editar</Button>
                <Button v-else :disabled="saving" @click="guardar">
                    {{ saving ? 'Guardando...' : 'Guardar producto' }}
                </Button>
            </div>
        </div>
    </Modal>
</template>

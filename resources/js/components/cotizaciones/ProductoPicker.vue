<script setup>
import { ref, watch } from 'vue';
import { Combobox, ComboboxInput, ComboboxOptions, ComboboxOption } from '@headlessui/vue';
import { TrashIcon, PlusIcon } from '@heroicons/vue/24/outline';
import http from '../../http';
import Button from '../ui/button/Button.vue';
import Input from '../ui/input/Input.vue';
import Table from '../ui/table/Table.vue';
import TableHeader from '../ui/table/TableHeader.vue';
import TableBody from '../ui/table/TableBody.vue';
import TableRow from '../ui/table/TableRow.vue';
import TableHead from '../ui/table/TableHead.vue';
import TableCell from '../ui/table/TableCell.vue';
import ProductoModal from '../../views/productos/ProductoModal.vue';

const props = defineProps({
    modelValue: { type: Array, required: true }, // [{ producto_id, nombre, cantidad, precio_unitario, subtotal }]
    moneda: { type: String, default: 'PEN' },
});

const emit = defineEmits(['update:modelValue']);

const query = ref('');
const resultados = ref([]);
const showAddItem = ref(false);
const tipoCambio = ref(null);

const tipoCambioPromise = http.get('/tipo-cambio')
    .then(({ data }) => { tipoCambio.value = data; })
    .catch(() => {});

function equivalentePen(precio, moneda) {
    if (moneda !== 'USD' || !tipoCambio.value) return null;
    return Number(precio) * tipoCambio.value.venta;
}

function precioEnMonedaCotizacion(producto) {
    const precio = Number(producto.precio);
    if (producto.moneda === props.moneda || !tipoCambio.value) return precio;
    if (producto.moneda === 'USD' && props.moneda === 'PEN') {
        return Number((precio * tipoCambio.value.venta).toFixed(2));
    }
    if (producto.moneda === 'PEN' && props.moneda === 'USD') {
        return Number((precio / tipoCambio.value.venta).toFixed(2));
    }
    return precio;
}

let debounceHandle;
watch(query, (value) => {
    clearTimeout(debounceHandle);
    if (!value) {
        resultados.value = [];
        return;
    }
    debounceHandle = setTimeout(async () => {
        const { data } = await http.get('/productos', { params: { buscar: value } });
        resultados.value = data.data;
    }, 250);
});

async function agregar(producto) {
    if (!producto) return;

    if (producto.moneda !== props.moneda && !tipoCambio.value) {
        await tipoCambioPromise;
    }

    const existente = props.modelValue.find((l) => l.producto_id === producto.id);
    if (existente) {
        actualizarCantidad(existente, existente.cantidad + 1);
    } else {
        const precio = precioEnMonedaCotizacion(producto);
        emit('update:modelValue', [
            ...props.modelValue,
            {
                producto_id: producto.id,
                nombre: producto.nombre,
                cantidad: 1,
                precio_unitario: precio,
                subtotal: precio,
            },
        ]);
    }

    query.value = '';
    resultados.value = [];
}

function onProductoCreado(producto) {
    showAddItem.value = false;
    if (producto) agregar(producto);
}

function actualizarCantidad(linea, cantidad) {
    actualizarLinea(linea, { cantidad: Number(cantidad) });
}

function actualizarPrecio(linea, precio) {
    actualizarLinea(linea, { precio_unitario: Number(precio) });
}

function actualizarLinea(linea, cambios) {
    const lineas = props.modelValue.map((l) => {
        if (l !== linea) return l;
        const actualizada = { ...l, ...cambios };
        actualizada.subtotal = Number((actualizada.cantidad * actualizada.precio_unitario).toFixed(2));
        return actualizada;
    });
    emit('update:modelValue', lineas);
}

function quitar(linea) {
    emit('update:modelValue', props.modelValue.filter((l) => l !== linea));
}
</script>

<template>
    <div class="flex flex-col gap-sp-sm">
        <div class="flex flex-col gap-sp-sm sm:flex-row">
            <Combobox class="flex-1" @update:modelValue="agregar">
                <div class="relative">
                    <ComboboxInput
                        v-model="query"
                        placeholder="Buscar productos por nombre o codigo..."
                        class="h-12 w-full rounded-sm border border-outline-variant bg-surface-container-lowest px-sp-sm text-body-md text-on-surface focus:border-primary focus:ring-2 focus:ring-primary/15 focus:outline-none"
                        @change="query = $event.target.value"
                    />
                    <ComboboxOptions v-if="resultados.length" class="absolute z-10 mt-1 w-full rounded-md border border-outline-variant bg-surface-container-lowest py-1 shadow-[0_4px_12px_rgba(0,27,61,0.1)]">
                        <ComboboxOption
                            v-for="producto in resultados"
                            :key="producto.id"
                            :value="producto"
                            v-slot="{ active }"
                        >
                            <div class="flex justify-between px-sp-sm py-2 text-body-md" :class="active ? 'bg-surface-container-high' : ''">
                                <span>{{ producto.nombre }} <span class="text-on-surface-variant">({{ producto.codigo || 's/c' }})</span></span>
                                <span class="text-right text-on-surface-variant">
                                    {{ producto.moneda }} {{ Number(producto.precio).toFixed(2) }}
                                    <span v-if="equivalentePen(producto.precio, producto.moneda) !== null" class="block text-label-sm">
                                        S/ {{ equivalentePen(producto.precio, producto.moneda).toFixed(2) }}
                                    </span>
                                </span>
                            </div>
                        </ComboboxOption>
                    </ComboboxOptions>
                </div>
            </Combobox>
            <Button type="button" variant="outline" class="shrink-0" @click="showAddItem = true">
                <PlusIcon class="h-4 w-4" /> Nuevo producto
            </Button>
        </div>

        <ProductoModal :open="showAddItem" mode="crear" @close="showAddItem = false" @saved="onProductoCreado" />

        <div class="hidden overflow-hidden rounded-md border border-outline-variant md:block">
            <Table>
                <TableHeader>
                    <TableRow class="hover:bg-transparent">
                        <TableHead>Producto</TableHead>
                        <TableHead class="w-24">Cantidad</TableHead>
                        <TableHead class="w-32">Precio</TableHead>
                        <TableHead class="w-32">Subtotal</TableHead>
                        <TableHead class="w-10"></TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="!modelValue.length" class="hover:bg-transparent">
                        <TableCell colspan="5" class="py-sp-md text-center text-on-surface-variant">Agrega productos usando el buscador.</TableCell>
                    </TableRow>
                    <TableRow v-for="linea in modelValue" :key="linea.producto_id">
                        <TableCell>{{ linea.nombre }}</TableCell>
                        <TableCell>
                            <Input
                                type="number"
                                min="1"
                                :model-value="linea.cantidad"
                                class="h-9"
                                @change="actualizarCantidad(linea, $event.target.value)"
                            />
                        </TableCell>
                        <TableCell>
                            <Input
                                type="number"
                                min="0"
                                step="0.01"
                                :model-value="linea.precio_unitario"
                                class="h-9"
                                @change="actualizarPrecio(linea, $event.target.value)"
                            />
                            <span v-if="equivalentePen(linea.precio_unitario, moneda) !== null" class="mt-1 block text-label-sm text-on-surface-variant">
                                S/ {{ equivalentePen(linea.precio_unitario, moneda).toFixed(2) }}
                            </span>
                        </TableCell>
                        <TableCell>
                            {{ moneda }} {{ linea.subtotal.toFixed(2) }}
                            <span v-if="equivalentePen(linea.subtotal, moneda) !== null" class="mt-1 block text-label-sm text-on-surface-variant">
                                S/ {{ equivalentePen(linea.subtotal, moneda).toFixed(2) }}
                            </span>
                        </TableCell>
                        <TableCell>
                            <Button variant="ghost" size="icon" class="text-error hover:bg-error-container" @click="quitar(linea)">
                                <TrashIcon class="h-4 w-4" />
                            </Button>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div class="divide-y divide-outline-variant rounded-md border border-outline-variant md:hidden">
            <p v-if="!modelValue.length" class="bg-surface-container-lowest py-sp-md text-center text-body-md text-on-surface-variant">
                Agrega productos usando el buscador.
            </p>
            <div v-for="linea in modelValue" :key="linea.producto_id" class="flex flex-col gap-sp-sm bg-surface-container-lowest p-sp-sm">
                <div class="flex items-start justify-between gap-sp-sm">
                    <span class="text-body-md font-semibold text-on-surface">{{ linea.nombre }}</span>
                    <Button variant="ghost" size="icon" class="shrink-0 text-error hover:bg-error-container" @click="quitar(linea)">
                        <TrashIcon class="h-4 w-4" />
                    </Button>
                </div>
                <label class="block">
                    <span class="text-field-label text-on-surface-variant">Cantidad</span>
                    <Input
                        type="number"
                        min="1"
                        :model-value="linea.cantidad"
                        class="mt-1 h-9 w-full"
                        @change="actualizarCantidad(linea, $event.target.value)"
                    />
                </label>
                <label class="block">
                    <span class="text-field-label text-on-surface-variant">Precio</span>
                    <Input
                        type="number"
                        min="0"
                        step="0.01"
                        :model-value="linea.precio_unitario"
                        class="mt-1 h-9 w-full"
                        @change="actualizarPrecio(linea, $event.target.value)"
                    />
                    <span v-if="equivalentePen(linea.precio_unitario, moneda) !== null" class="mt-1 block text-label-sm text-on-surface-variant">
                        S/ {{ equivalentePen(linea.precio_unitario, moneda).toFixed(2) }}
                    </span>
                </label>
                <div class="flex items-baseline justify-between border-t border-outline-variant pt-sp-sm text-body-md">
                    <span class="text-eyebrow uppercase text-on-surface-variant">Subtotal</span>
                    <span class="text-right text-on-surface">
                        {{ moneda }} {{ linea.subtotal.toFixed(2) }}
                        <span v-if="equivalentePen(linea.subtotal, moneda) !== null" class="block text-label-sm text-on-surface-variant">
                            S/ {{ equivalentePen(linea.subtotal, moneda).toFixed(2) }}
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

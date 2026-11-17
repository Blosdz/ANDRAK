<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { Combobox, ComboboxInput, ComboboxOptions, ComboboxOption } from '@headlessui/vue';
import http from '../../http';
import Modal from '../../components/Modal.vue';
import FormSelect from '../../components/form/FormSelect.vue';
import FormInput from '../../components/form/FormInput.vue';
import FormCheckbox from '../../components/form/FormCheckbox.vue';
import ProductoPicker from '../../components/cotizaciones/ProductoPicker.vue';
import Button from '../../components/ui/button/Button.vue';
import Alert from '../../components/ui/alert/Alert.vue';
import AlertDescription from '../../components/ui/alert/AlertDescription.vue';

const props = defineProps({
    open: { type: Boolean, required: true },
});

const emit = defineEmits(['close', 'saved']);

const TASA_IMPUESTO = 0.18;

const saving = ref(false);
const errors = ref({});

const clienteQuery = ref('');
const clienteResultados = ref([]);
const clienteSeleccionado = ref(null);

const form = reactive({
    moneda: 'PEN',
    horas_entrega: '',
    detalles: '',
    referencia: '',
    validez_dias: 5,
    garantia: '',
    aplicarImpuesto: false,
    lineas: [],
});

const subtotal = computed(() => form.lineas.reduce((sum, l) => sum + l.subtotal, 0));
const impuestos = computed(() => (form.aplicarImpuesto ? Number((subtotal.value * TASA_IMPUESTO).toFixed(2)) : 0));
const total = computed(() => subtotal.value + impuestos.value);

const tipoCambio = ref(null);
const totalPen = computed(() => (tipoCambio.value ? Number((total.value * tipoCambio.value.venta).toFixed(2)) : null));

const TASA_DETRACCION = 0.12;
const totalEnSoles = computed(() => (form.moneda === 'USD' ? totalPen.value : total.value));
const montoDetraccion = computed(() => (
    totalEnSoles.value && totalEnSoles.value > 700
        ? Number((totalEnSoles.value * TASA_DETRACCION).toFixed(2))
        : null
));

async function cargarTipoCambio() {
    try {
        const { data } = await http.get('/tipo-cambio');
        tipoCambio.value = data;
    } catch (e) {
        tipoCambio.value = null;
    }
}

watch(() => form.moneda, (moneda) => {
    if (moneda === 'USD' && !tipoCambio.value) cargarTipoCambio();
}, { immediate: true });

let debounceHandle;
watch(clienteQuery, (value) => {
    clearTimeout(debounceHandle);
    if (!value) {
        clienteResultados.value = [];
        return;
    }
    debounceHandle = setTimeout(async () => {
        const { data } = await http.get('/clientes', { params: { buscar: value } });
        clienteResultados.value = data.data;
    }, 250);
});

function seleccionarCliente(cliente) {
    clienteSeleccionado.value = cliente;
    clienteQuery.value = '';
    clienteResultados.value = [];
}

function resetForm() {
    clienteQuery.value = '';
    clienteResultados.value = [];
    clienteSeleccionado.value = null;
    Object.assign(form, {
        moneda: 'PEN', horas_entrega: '', detalles: '', referencia: '', validez_dias: 5, garantia: '', aplicarImpuesto: false, lineas: [],
    });
    errors.value = {};
}

watch(() => props.open, (isOpen) => {
    if (isOpen) resetForm();
});

async function guardar() {
    saving.value = true;
    errors.value = {};

    const payload = {
        cliente_id: clienteSeleccionado.value?.id,
        moneda: form.moneda,
        horas_entrega: form.horas_entrega || null,
        detalles: form.detalles || null,
        referencia: form.referencia || null,
        validez_dias: form.validez_dias === '' ? null : Number(form.validez_dias),
        garantia: form.garantia || null,
        impuestos: form.aplicarImpuesto ? impuestos.value : null,
        lineas: form.lineas.map((l) => ({
            producto_id: l.producto_id,
            cantidad: l.cantidad,
            precio_unitario: l.precio_unitario,
        })),
    };

    try {
        const { data } = await http.post('/cotizaciones', payload);
        emit('saved', data);
    } catch (e) {
        errors.value = e.response?.data?.errors || {};
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Modal :open="open" title="Nueva cotizacion" max-width="max-w-3xl" @close="$emit('close')">
        <div class="flex flex-col gap-sp-md">
            <Alert v-if="errors.general" variant="destructive">
                <AlertDescription>{{ errors.general[0] }}</AlertDescription>
            </Alert>

            <div>
                <span class="text-field-label text-on-surface-variant">Cliente <span class="text-error">*</span></span>
                <div v-if="clienteSeleccionado" class="mt-1 flex items-center justify-between rounded-sm border border-outline-variant px-sp-sm py-2">
                    <span class="text-body-md text-on-surface">{{ clienteSeleccionado.nombre }} <span class="text-on-surface-variant">({{ clienteSeleccionado.empresa || 'sin empresa' }})</span></span>
                    <Button variant="link" @click="clienteSeleccionado = null">Cambiar</Button>
                </div>
                <Combobox v-else @update:modelValue="seleccionarCliente">
                    <div class="relative mt-1">
                        <ComboboxInput
                            placeholder="Buscar cliente por nombre, RUC o empresa..."
                            class="h-12 w-full rounded-sm border border-outline-variant bg-surface-container-lowest px-sp-sm text-body-md text-on-surface focus:border-primary focus:ring-2 focus:ring-primary/15 focus:outline-none"
                            @change="clienteQuery = $event.target.value"
                        />
                        <ComboboxOptions v-if="clienteResultados.length" class="absolute z-10 mt-1 w-full rounded-md border border-outline-variant bg-surface-container-lowest py-1 shadow-[0_4px_12px_rgba(0,27,61,0.1)]">
                            <ComboboxOption v-for="cliente in clienteResultados" :key="cliente.id" :value="cliente" v-slot="{ active }">
                                <div class="px-sp-sm py-2 text-body-md" :class="active ? 'bg-surface-container-high' : ''">
                                    {{ cliente.nombre }} <span class="text-on-surface-variant">({{ cliente.empresa || 'sin empresa' }})</span>
                                </div>
                            </ComboboxOption>
                        </ComboboxOptions>
                    </div>
                </Combobox>
                <p v-if="errors.cliente_id" class="mt-1 text-label-sm text-error">{{ errors.cliente_id[0] }}</p>
            </div>

            <div>
                <span class="text-field-label text-on-surface-variant">Productos <span class="text-error">*</span></span>
                <div class="mt-1">
                    <ProductoPicker v-model="form.lineas" :moneda="form.moneda" />
                </div>
                <p v-if="errors.lineas" class="mt-1 text-label-sm text-error">{{ errors.lineas[0] }}</p>
            </div>

            <div class="grid grid-cols-1 gap-sp-md sm:grid-cols-3">
                <FormSelect v-model="form.moneda" label="Moneda" required :options="[{ value: 'PEN', label: 'PEN' }, { value: 'USD', label: 'USD' }]" />
                <FormCheckbox v-model="form.aplicarImpuesto" label="Impuestos (opcional) IGV 18%" />
                <FormInput v-model="form.horas_entrega" type="number" label="Horas de entrega" />
            </div>

            <div class="grid grid-cols-1 gap-sp-md sm:grid-cols-3">
                <FormInput v-model="form.referencia" label="Referencia" placeholder="Segun muestra" />
                <FormInput v-model="form.validez_dias" type="number" label="Validez de la oferta (dias)" />
                <FormInput v-model="form.garantia" label="Garantia" placeholder="6 meses por defectos de fabricacion" />
            </div>

            <label class="block">
                <span class="text-field-label text-on-surface-variant">Notas</span>
                <textarea
                    v-model="form.detalles"
                    rows="2"
                    placeholder="Observaciones adicionales para el PDF"
                    class="mt-1 w-full rounded-sm border border-outline-variant bg-surface-container-lowest px-sp-sm py-2 text-body-md text-on-surface focus:border-primary focus:ring-2 focus:ring-primary/15 focus:outline-none"
                />
            </label>

            <div class="flex flex-col gap-2 border-t border-outline-variant pt-sp-sm sm:items-end">
                <div class="flex w-full max-w-xs justify-between text-body-md text-on-surface-variant">
                    <span>Subtotal</span>
                    <span class="font-mono">{{ form.moneda }} {{ subtotal.toFixed(2) }}</span>
                </div>
                <div v-if="form.aplicarImpuesto" class="flex w-full max-w-xs justify-between text-body-md text-on-surface-variant">
                    <span>Impuestos (IGV 18%)</span>
                    <span class="font-mono">{{ form.moneda }} {{ impuestos.toFixed(2) }}</span>
                </div>
                <div class="flex w-full max-w-xs justify-between border-t border-outline-variant pt-2 font-sans text-body-lg font-extrabold text-on-surface">
                    <span>Total</span>
                    <span>{{ form.moneda }} {{ total.toFixed(2) }}</span>
                </div>
                <div v-if="form.moneda === 'USD' && totalPen !== null" class="flex w-full max-w-xs justify-between text-body-md text-on-surface-variant">
                    <span>Equivalente (S/ {{ tipoCambio.venta.toFixed(3) }})</span>
                    <span class="font-mono">PEN {{ totalPen.toFixed(2) }}</span>
                </div>
                <p v-if="montoDetraccion" class="w-full max-w-xs rounded-sm border border-outline-variant bg-surface-container-high px-sp-sm py-2 text-label-sm text-on-surface-variant">
                    Operacion sujeta a detraccion (SPOT): 12% = S/ {{ montoDetraccion.toFixed(2) }} (aplica al superar S/ 700).
                </p>
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                <Button variant="ghost" @click="$emit('close')">Cancelar</Button>
                <Button :disabled="saving" @click="guardar">
                    {{ saving ? 'Guardando...' : 'Guardar cotizacion' }}
                </Button>
            </div>
        </div>
    </Modal>
</template>

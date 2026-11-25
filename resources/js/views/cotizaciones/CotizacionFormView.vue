<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { Combobox, ComboboxInput, ComboboxOptions, ComboboxOption } from '@headlessui/vue';
import http from '../../http';
import { downloadPdf } from '../../utils/download';
import FormSelect from '../../components/form/FormSelect.vue';
import FormInput from '../../components/form/FormInput.vue';
import FormCheckbox from '../../components/form/FormCheckbox.vue';
import ProductoPicker from '../../components/cotizaciones/ProductoPicker.vue';
import Button from '../../components/ui/button/Button.vue';
import Alert from '../../components/ui/alert/Alert.vue';
import AlertDescription from '../../components/ui/alert/AlertDescription.vue';
import PageEyebrow from '../../components/PageEyebrow.vue';
import ClienteFormModal from '../clientes/ClienteFormModal.vue';

const props = defineProps({ id: { type: [String, Number], required: true } });
const router = useRouter();

const TASA_IMPUESTO = 0.18;
const TASA_DETRACCION = 0.12;

const loading = ref(false);
const saving = ref(false);
const converting = ref(false);
const errors = ref({});
const empresas = ref([]);
const opcionesEmisor = computed(() => empresas.value.map((item) => ({
    value: item.id, label: `${item.nombre_comercial} — ${item.ruc}`,
})));
const cotizacionActual = ref(null);

const clienteQuery = ref('');
const clienteResultados = ref([]);
const clienteSeleccionado = ref(null);
const showClienteModal = ref(false);

const form = reactive({
    empresa_id: null,
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

const isConvertida = computed(() => cotizacionActual.value?.estado === 'convertida');

const tipoCambio = ref(null);
const totalPen = computed(() => (tipoCambio.value ? Number((total.value * tipoCambio.value.venta).toFixed(2)) : null));

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

function clienteCreado(cliente) {
    seleccionarCliente(cliente);
    showClienteModal.value = false;
}

async function cargarCotizacion() {
    loading.value = true;
    try {
        const { data } = await http.get(`/cotizaciones/${props.id}`);
        cotizacionActual.value = data;
        clienteSeleccionado.value = data.cliente;
        form.empresa_id = data.empresa_id;
        form.moneda = data.moneda;
        form.horas_entrega = data.horas_entrega ?? '';
        form.detalles = data.detalles ?? '';
        form.referencia = data.referencia ?? '';
        form.validez_dias = data.validez_dias ?? 5;
        form.garantia = data.garantia ?? '';
        form.aplicarImpuesto = !!data.impuestos && Number(data.impuestos) > 0;
        form.lineas = data.lineas.map((l) => ({
            producto_id: l.producto_id,
            nombre: l.nombre,
            cantidad: l.cantidad,
            precio_unitario: Number(l.precio_unitario),
            subtotal: Number(l.subtotal),
        }));
    } finally {
        loading.value = false;
    }
}

async function guardar() {
    saving.value = true;
    errors.value = {};

    const payload = {
        empresa_id: form.empresa_id,
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
        const { data } = await http.put(`/cotizaciones/${props.id}`, payload);
        cotizacionActual.value = data;
    } catch (e) {
        errors.value = e.response?.data?.errors || {};
    } finally {
        saving.value = false;
    }
}

async function generarPdf() {
    await downloadPdf(`/cotizaciones/${props.id}/pdf`, `${cotizacionActual.value.numero}.pdf`);
}

async function convertirVenta() {
    converting.value = true;
    try {
        await http.post(`/cotizaciones/${props.id}/convertir-venta`, {});
        router.push({ name: 'cotizaciones.show', params: { id: props.id } });
    } catch (e) {
        errors.value = { general: [e.response?.data?.message || 'No se pudo convertir la cotizacion.'] };
    } finally {
        converting.value = false;
    }
}

watch(() => props.id, cargarCotizacion, { immediate: true });
onMounted(async () => {
    const { data } = await http.get('/empresas');
    empresas.value = data;
});
watch(() => form.moneda, (moneda) => {
    if (moneda === 'USD' && !tipoCambio.value) cargarTipoCambio();
}, { immediate: true });
</script>

<template>
    <div class="flex flex-col gap-sp-md">
        <div>
            <PageEyebrow>Cotizaciones</PageEyebrow>
            <h1 class="text-headline-lg-mobile text-on-surface lg:text-headline-lg">
                Cotizacion {{ cotizacionActual?.numero || '' }}
            </h1>
        </div>

        <div v-if="loading" class="text-on-surface-variant">Cargando...</div>

        <div v-else class="grid grid-cols-1 items-start gap-sp-md lg:grid-cols-[1.5fr_1fr]">
            <div class="flex flex-col gap-sp-md">
                <Alert v-if="errors.general" variant="destructive">
                    <AlertDescription>{{ errors.general[0] }}</AlertDescription>
                </Alert>

                <div class="rounded-lg border border-outline-variant bg-surface-container-lowest p-sp-md lg:p-sp-lg">
                    <div class="mb-sp-sm text-eyebrow text-on-surface-variant">Datos del cliente</div>

                    <FormSelect v-model="form.empresa_id" label="Emisor (RUC)" required :options="opcionesEmisor"
                        :disabled="isConvertida" :error="errors.empresa_id?.[0]" />

                    <div class="flex items-center justify-between gap-sp-sm">
                        <span class="text-field-label text-on-surface-variant">Cliente <span class="text-error">*</span></span>
                        <Button v-if="!isConvertida" type="button" variant="outline" size="sm" @click="showClienteModal = true">Nuevo cliente</Button>
                    </div>
                    <div v-if="clienteSeleccionado" class="mt-1 flex items-center justify-between rounded-sm border border-outline-variant px-sp-sm py-2">
                        <span class="text-body-md text-on-surface">{{ clienteSeleccionado.nombre }} <span class="text-on-surface-variant">({{ clienteSeleccionado.empresa || 'sin empresa' }})</span></span>
                        <Button v-if="!isConvertida" variant="link" @click="clienteSeleccionado = null">Cambiar</Button>
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

                <div class="rounded-lg border border-outline-variant bg-surface-container-lowest p-sp-md lg:p-sp-lg">
                    <div class="mb-sp-sm text-eyebrow text-on-surface-variant">Productos</div>
                    <ProductoPicker v-model="form.lineas" :moneda="form.moneda" />
                    <p v-if="errors.lineas" class="mt-1 text-label-sm text-error">{{ errors.lineas[0] }}</p>
                </div>
            </div>

            <div class="flex flex-col gap-sp-sm lg:sticky lg:top-sp-md">
                <div class="rounded-lg border border-outline-variant bg-surface-container-lowest p-sp-md">
                    <div class="mb-sp-sm text-eyebrow text-on-surface-variant">Resumen</div>

                    <div class="flex flex-col gap-sp-sm">
                        <FormSelect v-model="form.moneda" label="Moneda" required :options="[{ value: 'PEN', label: 'PEN' }, { value: 'USD', label: 'USD' }]" />
                        <FormCheckbox v-model="form.aplicarImpuesto" label="Impuestos (opcional) IGV 18%" />
                        <FormInput v-model="form.horas_entrega" type="number" label="Horas de entrega" />
                        <FormInput v-model="form.referencia" label="Referencia" placeholder="Segun muestra" />
                        <FormInput v-model="form.validez_dias" type="number" label="Validez de la oferta (dias)" />
                        <FormInput v-model="form.garantia" label="Garantia" placeholder="6 meses por defectos de fabricacion" />
                        <label class="block">
                            <span class="text-field-label text-on-surface-variant">Notas</span>
                            <textarea
                                v-model="form.detalles"
                                rows="3"
                                placeholder="Observaciones adicionales para el PDF"
                                class="mt-1 w-full rounded-sm border border-outline-variant bg-surface-container-lowest px-sp-sm py-2 text-body-md text-on-surface focus:border-primary focus:ring-2 focus:ring-primary/15 focus:outline-none"
                            />
                        </label>
                    </div>

                    <div class="mt-sp-md flex flex-col gap-2 border-t border-outline-variant pt-sp-sm">
                        <div class="flex justify-between text-body-md text-on-surface-variant">
                            <span>Subtotal</span>
                            <span class="font-mono">{{ form.moneda }} {{ subtotal.toFixed(2) }}</span>
                        </div>
                        <div v-if="form.aplicarImpuesto" class="flex justify-between text-body-md text-on-surface-variant">
                            <span>Impuestos (IGV 18%)</span>
                            <span class="font-mono">{{ form.moneda }} {{ impuestos.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between border-t border-outline-variant pt-2 font-sans text-body-lg font-extrabold text-on-surface">
                            <span>Total</span>
                            <span>{{ form.moneda }} {{ total.toFixed(2) }}</span>
                        </div>
                        <div v-if="form.moneda === 'USD' && totalPen !== null" class="flex justify-between text-body-md text-on-surface-variant">
                            <span>Equivalente (S/ {{ tipoCambio.venta.toFixed(3) }})</span>
                            <span class="font-mono">PEN {{ totalPen.toFixed(2) }}</span>
                        </div>
                        <p v-if="montoDetraccion" class="rounded-sm border border-outline-variant bg-surface-container-high px-sp-sm py-2 text-label-sm text-on-surface-variant">
                            Operacion sujeta a detraccion (SPOT): 12% = S/ {{ montoDetraccion.toFixed(2) }} (aplica al superar S/ 700).
                        </p>
                    </div>
                </div>

                <Button class="w-full" :disabled="saving || isConvertida" @click="guardar">
                    {{ saving ? 'Guardando...' : 'Guardar borrador' }}
                </Button>
                <Button variant="outline" class="w-full" @click="generarPdf">
                    Generar PDF
                </Button>
                <Button v-if="!isConvertida" variant="secondary" class="w-full" :disabled="converting" @click="convertirVenta">
                    {{ converting ? 'Convirtiendo...' : 'Convertir en venta' }}
                </Button>
            </div>
        </div>
    </div>
    <ClienteFormModal :open="showClienteModal" @close="showClienteModal = false" @saved="clienteCreado" />
</template>

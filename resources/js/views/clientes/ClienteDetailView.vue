<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import http from '../../http';
import { downloadPdf } from '../../utils/download';
import Tabs from '../../components/Tabs.vue';
import Badge from '../../components/Badge.vue';
import Button from '../../components/ui/button/Button.vue';

const props = defineProps({ id: { type: [String, Number], required: true } });
const router = useRouter();

const cliente = ref(null);
const loading = ref(true);

const tabs = [
    { key: 'datos', label: 'Datos del Cliente' },
    { key: 'historial', label: 'Historial' },
    { key: 'documentos', label: 'Documentos' },
];

async function fetchCliente() {
    loading.value = true;
    try {
        const { data } = await http.get(`/clientes/${props.id}`);
        cliente.value = data;
    } finally {
        loading.value = false;
    }
}

function money(monto, moneda) {
    return `${moneda} ${Number(monto).toFixed(2)}`;
}

onMounted(fetchCliente);
</script>

<template>
    <div v-if="loading" class="text-on-surface-variant">Cargando...</div>

    <div v-else-if="cliente" class="flex flex-col gap-sp-md">
        <div class="flex items-center gap-sp-sm">
            <Button variant="link" @click="router.push({ name: 'clientes' })">&larr; Clientes</Button>
        </div>

        <h1 class="text-headline-lg-mobile text-on-surface lg:text-headline-lg">{{ cliente.nombre }}</h1>

        <div class="border border-outline-variant bg-surface-container-lowest p-sp-md">
            <Tabs :tabs="tabs">
                <template #datos>
                    <dl class="grid grid-cols-1 gap-sp-md sm:grid-cols-2">
                        <div>
                            <dt class="text-field-label text-on-surface-variant">Nombre</dt>
                            <dd class="text-body-md text-on-surface">{{ cliente.nombre }}</dd>
                        </div>
                        <div>
                            <dt class="text-field-label text-on-surface-variant">RUC</dt>
                            <dd class="text-body-md text-on-surface">{{ cliente.ruc || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-field-label text-on-surface-variant">Empresa</dt>
                            <dd class="text-body-md text-on-surface">{{ cliente.empresa || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-field-label text-on-surface-variant">Direccion</dt>
                            <dd class="text-body-md text-on-surface">{{ cliente.direccion || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-field-label text-on-surface-variant">Correo</dt>
                            <dd class="text-body-md text-on-surface">{{ cliente.correo || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-field-label text-on-surface-variant">Telefono</dt>
                            <dd class="text-body-md text-on-surface">{{ cliente.telefono || '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-field-label text-on-surface-variant">Persona de contacto</dt>
                            <dd class="text-body-md text-on-surface">{{ cliente.contacto || '-' }}</dd>
                        </div>
                    </dl>
                </template>

                <template #historial>
                    <div class="flex flex-col gap-sp-md">
                        <div>
                            <h3 class="text-label-md font-semibold text-on-surface-variant">Cotizaciones ({{ cliente.cotizaciones_count }})</h3>
                            <ul class="mt-sp-sm flex flex-col divide-y divide-outline-variant">
                                <li v-if="!cliente.cotizaciones.length" class="py-sp-sm text-body-md text-on-surface-variant">Sin cotizaciones.</li>
                                <li v-for="cot in cliente.cotizaciones" :key="cot.id" class="flex items-center justify-between py-sp-sm">
                                    <span class="text-body-md text-on-surface">{{ cot.numero }} &middot; {{ money(cot.total, cot.moneda) }}</span>
                                    <Badge :value="cot.estado" />
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-label-md font-semibold text-on-surface-variant">Ventas ({{ cliente.ventas_count }})</h3>
                            <ul class="mt-sp-sm flex flex-col divide-y divide-outline-variant">
                                <li v-if="!cliente.ventas.length" class="py-sp-sm text-body-md text-on-surface-variant">Sin ventas.</li>
                                <li v-for="venta in cliente.ventas" :key="venta.id" class="flex items-center justify-between py-sp-sm">
                                    <span class="text-body-md text-on-surface">{{ venta.numero }}</span>
                                    <span class="text-body-md text-on-surface">{{ money(venta.monto_final, venta.moneda) }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </template>

                <template #documentos>
                    <div class="flex flex-col gap-sp-md">
                        <div>
                            <h3 class="text-label-md font-semibold text-on-surface-variant">PDF de Cotizaciones</h3>
                            <ul class="mt-sp-sm flex flex-col divide-y divide-outline-variant">
                                <li v-if="!cliente.cotizaciones.length" class="py-sp-sm text-body-md text-on-surface-variant">Sin documentos.</li>
                                <li v-for="cot in cliente.cotizaciones" :key="cot.id" class="flex items-center justify-between py-sp-sm">
                                    <span class="text-body-md text-on-surface">{{ cot.numero }}</span>
                                    <Button variant="link" @click="downloadPdf(`/cotizaciones/${cot.id}/pdf`, `${cot.numero}.pdf`)">
                                        Descargar PDF
                                    </Button>
                                </li>
                            </ul>
                        </div>
                        <div>
                            <h3 class="text-label-md font-semibold text-on-surface-variant">PDF de Ventas</h3>
                            <ul class="mt-sp-sm flex flex-col divide-y divide-outline-variant">
                                <li v-if="!cliente.ventas.length" class="py-sp-sm text-body-md text-on-surface-variant">Sin documentos.</li>
                                <li v-for="venta in cliente.ventas" :key="venta.id" class="flex items-center justify-between py-sp-sm">
                                    <span class="text-body-md text-on-surface">{{ venta.numero }}</span>
                                    <Button variant="link" @click="downloadPdf(`/ventas/${venta.id}/pdf`, `${venta.numero}.pdf`)">
                                        Descargar PDF
                                    </Button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </template>
            </Tabs>
        </div>
    </div>
</template>

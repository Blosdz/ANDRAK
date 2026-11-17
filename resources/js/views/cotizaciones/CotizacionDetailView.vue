<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import http from '../../http';
import { downloadPdf } from '../../utils/download';
import Badge from '../../components/Badge.vue';
import Button from '../../components/ui/button/Button.vue';
import Table from '../../components/ui/table/Table.vue';
import TableHeader from '../../components/ui/table/TableHeader.vue';
import TableBody from '../../components/ui/table/TableBody.vue';
import TableRow from '../../components/ui/table/TableRow.vue';
import TableHead from '../../components/ui/table/TableHead.vue';
import TableCell from '../../components/ui/table/TableCell.vue';
import Alert from '../../components/ui/alert/Alert.vue';
import AlertDescription from '../../components/ui/alert/AlertDescription.vue';

const props = defineProps({ id: { type: [String, Number], required: true } });
const router = useRouter();

const cotizacion = ref(null);
const loading = ref(true);
const converting = ref(false);
const error = ref('');

function money(monto, moneda) {
    return `${moneda} ${Number(monto).toFixed(2)}`;
}

async function fetchCotizacion() {
    loading.value = true;
    try {
        const { data } = await http.get(`/cotizaciones/${props.id}`);
        cotizacion.value = data;
    } finally {
        loading.value = false;
    }
}

async function convertirVenta() {
    converting.value = true;
    error.value = '';
    try {
        await http.post(`/cotizaciones/${props.id}/convertir-venta`, {});
        await fetchCotizacion();
    } catch (e) {
        error.value = e.response?.data?.message || 'No se pudo convertir la cotizacion.';
    } finally {
        converting.value = false;
    }
}

onMounted(fetchCotizacion);
</script>

<template>
    <div v-if="loading" class="text-on-surface-variant">Cargando...</div>

    <div v-else-if="cotizacion" class="flex flex-col gap-sp-md">
        <Button variant="link" class="w-fit" @click="router.push({ name: 'cotizaciones' })">&larr; Cotizaciones</Button>

        <div class="flex flex-wrap items-center justify-between gap-sp-sm">
            <div>
                <h1 class="text-headline-lg-mobile text-on-surface lg:text-headline-lg">{{ cotizacion.numero }}</h1>
                <p class="text-body-md text-on-surface-variant">{{ cotizacion.cliente?.nombre }}</p>
            </div>
            <Badge :value="cotizacion.estado" />
        </div>

        <Alert v-if="error" variant="destructive">
            <AlertDescription>{{ error }}</AlertDescription>
        </Alert>

        <div class="border border-outline-variant bg-surface-container-lowest p-sp-md">
            <div class="hidden overflow-hidden border border-outline-variant md:block">
                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-transparent">
                            <TableHead>Producto</TableHead>
                            <TableHead>Cantidad</TableHead>
                            <TableHead>Precio</TableHead>
                            <TableHead>Subtotal</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="linea in cotizacion.lineas" :key="linea.id">
                            <TableCell>{{ linea.nombre }}</TableCell>
                            <TableCell>{{ linea.cantidad }}</TableCell>
                            <TableCell>{{ money(linea.precio_unitario, cotizacion.moneda) }}</TableCell>
                            <TableCell>{{ money(linea.subtotal, cotizacion.moneda) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="divide-y divide-outline-variant border border-outline-variant md:hidden">
                <div v-for="linea in cotizacion.lineas" :key="linea.id" class="flex flex-col gap-sp-sm bg-surface-container-lowest p-sp-sm">
                    <span class="text-body-md font-semibold text-on-surface">{{ linea.nombre }}</span>
                    <div class="flex items-baseline justify-between text-body-md">
                        <span class="text-eyebrow uppercase text-on-surface-variant">Cantidad</span>
                        <span class="text-on-surface">{{ linea.cantidad }}</span>
                    </div>
                    <div class="flex items-baseline justify-between text-body-md">
                        <span class="text-eyebrow uppercase text-on-surface-variant">Precio</span>
                        <span class="text-on-surface">{{ money(linea.precio_unitario, cotizacion.moneda) }}</span>
                    </div>
                    <div class="flex items-baseline justify-between text-body-md">
                        <span class="text-eyebrow uppercase text-on-surface-variant">Subtotal</span>
                        <span class="text-on-surface">{{ money(linea.subtotal, cotizacion.moneda) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-sp-md flex justify-end">
                <table class="w-full max-w-xs text-body-md">
                    <tr>
                        <td class="text-on-surface-variant">Subtotal</td>
                        <td class="text-right">{{ money(cotizacion.subtotal, cotizacion.moneda) }}</td>
                    </tr>
                    <tr v-if="cotizacion.impuestos">
                        <td class="text-on-surface-variant">Impuestos</td>
                        <td class="text-right">{{ money(cotizacion.impuestos, cotizacion.moneda) }}</td>
                    </tr>
                    <tr class="font-semibold">
                        <td>Total</td>
                        <td class="text-right">{{ money(cotizacion.total, cotizacion.moneda) }}</td>
                    </tr>
                    <tr v-if="cotizacion.equivalente_pen" class="text-on-surface-variant">
                        <td>Equivalente (S/ {{ cotizacion.equivalente_pen.tipo_cambio.toFixed(3) }})</td>
                        <td class="text-right">{{ money(cotizacion.equivalente_pen.total, 'PEN') }}</td>
                    </tr>
                </table>
            </div>

            <p v-if="cotizacion.detalles" class="mt-sp-md text-body-md text-on-surface-variant">
                <strong class="text-on-surface">Observaciones:</strong> {{ cotizacion.detalles }}
            </p>

            <div class="mt-sp-md flex flex-wrap justify-end gap-2">
                <Button
                    v-if="cotizacion.estado !== 'convertida'"
                    variant="outline"
                    @click="router.push({ name: 'cotizaciones.editar', params: { id: cotizacion.id } })"
                >
                    Editar
                </Button>
                <Button variant="outline" @click="downloadPdf(`/cotizaciones/${cotizacion.id}/pdf`, `${cotizacion.numero}.pdf`)">
                    Descargar PDF
                </Button>
                <Button
                    v-if="cotizacion.estado !== 'convertida'"
                    variant="secondary"
                    :disabled="converting"
                    @click="convertirVenta"
                >
                    {{ converting ? 'Convirtiendo...' : 'Convertir en venta' }}
                </Button>
            </div>
        </div>
    </div>
</template>

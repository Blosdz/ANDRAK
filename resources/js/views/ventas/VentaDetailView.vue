<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import http from '../../http';
import { downloadFile, downloadPdf } from '../../utils/download';
import Button from '../../components/ui/button/Button.vue';
import Table from '../../components/ui/table/Table.vue';
import TableHeader from '../../components/ui/table/TableHeader.vue';
import TableBody from '../../components/ui/table/TableBody.vue';
import TableRow from '../../components/ui/table/TableRow.vue';
import TableHead from '../../components/ui/table/TableHead.vue';
import TableCell from '../../components/ui/table/TableCell.vue';
import Dialog from '../../components/ui/dialog/Dialog.vue';
import FormSelect from '../../components/form/FormSelect.vue';
import FormInput from '../../components/form/FormInput.vue';
import Badge from '../../components/Badge.vue';
import DropdownMenu from '../../components/ui/dropdown-menu/DropdownMenu.vue';
import { ChevronDownIcon } from '@heroicons/vue/24/outline';

const props = defineProps({ id: { type: [String, Number], required: true } });
const router = useRouter();

const venta = ref(null);
const loading = ref(true);

const dialogOpen = ref(false);
const tipoComprobante = ref('03');
const emitiendo = ref(false);
const emitirError = ref('');
const reenviandoId = ref(null);

const tipoComprobanteOptions = [
    { value: '03', label: 'Boleta de Venta' },
    { value: '01', label: 'Factura' },
];

const estadoBadgeMap = {
    aceptado: 'bg-[#d7f5dd] text-[#0a5a26]',
    observado: 'bg-[#fef3d6] text-[#92660a]',
    rechazado: 'bg-[#fde2e1] text-[#b3261e]',
    pendiente: 'bg-[#e2e8f0] text-[#44474e]',
};

function money(monto, moneda) {
    return `${moneda} ${Number(monto).toFixed(2)}`;
}

function fecha(iso) {
    return iso ? iso.slice(0, 10) : '-';
}

async function fetchVenta() {
    loading.value = true;
    try {
        const { data } = await http.get(`/ventas/${props.id}`);
        venta.value = data;
    } finally {
        loading.value = false;
    }
}

function abrirDialogEmitir() {
    emitirError.value = '';
    dialogOpen.value = true;
}

async function emitirComprobante() {
    emitiendo.value = true;
    emitirError.value = '';
    try {
        await http.post(`/ventas/${venta.value.id}/comprobantes`, {
            tipo_comprobante_id: tipoComprobante.value,
        });
        dialogOpen.value = false;
        await fetchVenta();
    } catch (error) {
        emitirError.value = error.response?.data?.message || 'No se pudo emitir el comprobante.';
    } finally {
        emitiendo.value = false;
    }
}

async function reenviar(comprobante) {
    reenviandoId.value = comprobante.id;
    try {
        await http.post(`/comprobantes/${comprobante.id}/reenviar`);
        await fetchVenta();
    } finally {
        reenviandoId.value = null;
    }
}

function nombreComprobante(comprobante) {
    return `${comprobante.serie}-${String(comprobante.numero).padStart(8, '0')}`;
}

const pagoForm = ref({ monto: '', fecha_pago: new Date().toISOString().slice(0, 10), nota: '' });
const guardandoPago = ref(false);
const pagoError = ref('');
const eliminandoPagoId = ref(null);

async function registrarPago() {
    guardandoPago.value = true;
    pagoError.value = '';
    try {
        await http.post(`/ventas/${venta.value.id}/pagos`, {
            monto: Number(pagoForm.value.monto),
            fecha_pago: pagoForm.value.fecha_pago,
            nota: pagoForm.value.nota || null,
        });
        pagoForm.value = { monto: '', fecha_pago: new Date().toISOString().slice(0, 10), nota: '' };
        await fetchVenta();
    } catch (error) {
        pagoError.value = error.response?.data?.message || 'No se pudo registrar el pago.';
    } finally {
        guardandoPago.value = false;
    }
}

async function eliminarPago(pago) {
    eliminandoPagoId.value = pago.id;
    try {
        await http.delete(`/ventas/${venta.value.id}/pagos/${pago.id}`);
        await fetchVenta();
    } finally {
        eliminandoPagoId.value = null;
    }
}

onMounted(fetchVenta);
</script>

<template>
    <div v-if="loading" class="text-on-surface-variant">Cargando...</div>

    <div v-else-if="venta" class="flex flex-col gap-sp-md">
        <Button variant="link" class="w-fit" @click="router.push({ name: 'ventas' })">&larr; Ventas</Button>

        <div class="flex flex-wrap items-center justify-between gap-sp-sm">
            <div>
                <h1 class="text-headline-lg-mobile text-on-surface lg:text-headline-lg">{{ venta.numero }}</h1>
                <p class="text-body-md text-on-surface-variant">{{ venta.cliente?.nombre }}</p>
            </div>
            <Button @click="abrirDialogEmitir">Emitir comprobante</Button>
        </div>

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
                        <TableRow v-for="linea in venta.cotizacion?.lineas" :key="linea.id">
                            <TableCell>{{ linea.nombre }}</TableCell>
                            <TableCell>{{ linea.cantidad }}</TableCell>
                            <TableCell>{{ money(linea.precio_unitario, venta.moneda) }}</TableCell>
                            <TableCell>{{ money(linea.subtotal, venta.moneda) }}</TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div class="divide-y divide-outline-variant border border-outline-variant md:hidden">
                <div v-for="linea in venta.cotizacion?.lineas" :key="linea.id" class="flex flex-col gap-sp-sm bg-surface-container-lowest p-sp-sm">
                    <span class="text-body-md font-semibold text-on-surface">{{ linea.nombre }}</span>
                    <div class="flex items-baseline justify-between text-body-md">
                        <span class="text-eyebrow uppercase text-on-surface-variant">Cantidad</span>
                        <span class="text-on-surface">{{ linea.cantidad }}</span>
                    </div>
                    <div class="flex items-baseline justify-between text-body-md">
                        <span class="text-eyebrow uppercase text-on-surface-variant">Precio</span>
                        <span class="text-on-surface">{{ money(linea.precio_unitario, venta.moneda) }}</span>
                    </div>
                    <div class="flex items-baseline justify-between text-body-md">
                        <span class="text-eyebrow uppercase text-on-surface-variant">Subtotal</span>
                        <span class="text-on-surface">{{ money(linea.subtotal, venta.moneda) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-sp-md flex justify-end">
                <table class="w-full max-w-xs text-body-md">
                    <tr class="font-semibold">
                        <td>Total</td>
                        <td class="text-right">{{ money(venta.monto_final, venta.moneda) }}</td>
                    </tr>
                    <tr v-if="venta.tipo_cambio && venta.monto_final_pen" class="text-on-surface-variant">
                        <td>Equivalente (S/ {{ Number(venta.tipo_cambio).toFixed(3) }})</td>
                        <td class="text-right">{{ money(venta.monto_final_pen, 'PEN') }}</td>
                    </tr>
                </table>
            </div>

            <dl class="mt-sp-md grid grid-cols-1 gap-sp-md sm:grid-cols-3">
                <div>
                    <dt class="text-field-label text-on-surface-variant">Cotizacion relacionada</dt>
                    <dd class="text-body-md text-on-surface">{{ venta.cotizacion?.numero || '-' }}</dd>
                </div>
                <div>
                    <dt class="text-field-label text-on-surface-variant">Cuotas</dt>
                    <dd class="text-body-md text-on-surface">{{ venta.cuotas }}</dd>
                </div>
                <div>
                    <dt class="text-field-label text-on-surface-variant">Fecha</dt>
                    <dd class="text-body-md text-on-surface">{{ new Date(venta.created_at).toLocaleDateString() }}</dd>
                </div>
            </dl>

            <div class="mt-sp-md flex justify-end gap-2">
                <DropdownMenu
                    :items="[
                        { label: 'Descargar completo', onSelect: () => downloadPdf(`/ventas/${venta.id}/pdf`, `Contrato-${venta.numero}.pdf`) },
                        { label: 'Descargar recibo', onSelect: () => downloadPdf(`/ventas/${venta.id}/pdf?tipo=recibo`, `Recibo-${venta.numero}.pdf`) },
                    ]"
                >
                    <template #trigger>
                        <Button variant="outline">
                            Descargar
                            <ChevronDownIcon class="ml-1 h-4 w-4" />
                        </Button>
                    </template>
                </DropdownMenu>
            </div>
        </div>

        <div class="border border-outline-variant bg-surface-container-lowest p-sp-md">
            <h2 class="text-title-lg text-on-surface">Pagos</h2>

            <dl class="mt-sp-sm grid grid-cols-1 gap-sp-md sm:grid-cols-4">
                <div>
                    <dt class="text-field-label text-on-surface-variant">Total</dt>
                    <dd class="text-body-md text-on-surface">{{ money(venta.monto_final, venta.moneda) }}</dd>
                </div>
                <div>
                    <dt class="text-field-label text-on-surface-variant">Pagado</dt>
                    <dd class="text-body-md text-on-surface">{{ money(venta.monto_pagado, venta.moneda) }} ({{ venta.porcentaje_pagado }}%)</dd>
                </div>
                <div>
                    <dt class="text-field-label text-on-surface-variant">Saldo pendiente</dt>
                    <dd class="text-body-md text-on-surface">{{ money(venta.saldo_pendiente, venta.moneda) }}</dd>
                </div>
            </dl>

            <div v-if="venta.pagos?.length" class="mt-sp-sm hidden overflow-hidden border border-outline-variant md:block">
                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-transparent">
                            <TableHead>Fecha</TableHead>
                            <TableHead>Nota</TableHead>
                            <TableHead>Monto</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="pago in venta.pagos" :key="pago.id">
                            <TableCell>{{ fecha(pago.fecha_pago) }}</TableCell>
                            <TableCell>{{ pago.nota || '-' }}</TableCell>
                            <TableCell>{{ money(pago.monto, venta.moneda) }}</TableCell>
                            <TableCell>
                                <Button
                                    variant="outline" size="sm" :disabled="eliminandoPagoId === pago.id"
                                    @click="eliminarPago(pago)"
                                >
                                    {{ eliminandoPagoId === pago.id ? 'Eliminando...' : 'Eliminar' }}
                                </Button>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="venta.pagos?.length" class="mt-sp-sm divide-y divide-outline-variant border border-outline-variant md:hidden">
                <div v-for="pago in venta.pagos" :key="pago.id" class="flex flex-col gap-sp-sm bg-surface-container-lowest p-sp-sm">
                    <div class="flex items-baseline justify-between text-body-md">
                        <span class="text-eyebrow uppercase text-on-surface-variant">Fecha</span>
                        <span class="text-on-surface">{{ fecha(pago.fecha_pago) }}</span>
                    </div>
                    <div class="flex items-baseline justify-between text-body-md">
                        <span class="text-eyebrow uppercase text-on-surface-variant">Nota</span>
                        <span class="text-right text-on-surface">{{ pago.nota || '-' }}</span>
                    </div>
                    <div class="flex items-baseline justify-between text-body-md">
                        <span class="text-eyebrow uppercase text-on-surface-variant">Monto</span>
                        <span class="text-on-surface">{{ money(pago.monto, venta.moneda) }}</span>
                    </div>
                    <Button
                        variant="outline" size="sm" class="self-end" :disabled="eliminandoPagoId === pago.id"
                        @click="eliminarPago(pago)"
                    >
                        {{ eliminandoPagoId === pago.id ? 'Eliminando...' : 'Eliminar' }}
                    </Button>
                </div>
            </div>

            <form class="mt-sp-md flex flex-wrap items-end gap-sp-sm" @submit.prevent="registrarPago">
                <FormInput v-model="pagoForm.monto" type="number" step="0.01" min="0.01" label="Monto" required class="w-32" />
                <FormInput v-model="pagoForm.fecha_pago" type="date" label="Fecha" required />
                <FormInput v-model="pagoForm.nota" label="Nota" placeholder="Opcional" />
                <Button type="submit" :disabled="guardandoPago">{{ guardandoPago ? 'Guardando...' : 'Registrar pago' }}</Button>
            </form>
            <p v-if="pagoError" class="mt-sp-xs text-body-md text-error">{{ pagoError }}</p>
        </div>

        <div class="border border-outline-variant bg-surface-container-lowest p-sp-md">
            <h2 class="text-title-lg text-on-surface">Comprobantes electronicos</h2>

            <p v-if="!venta.comprobantes?.length" class="mt-sp-sm text-body-md text-on-surface-variant">
                Aun no se ha emitido ningun comprobante para esta venta.
            </p>

            <div v-else class="mt-sp-sm hidden overflow-hidden border border-outline-variant md:block">
                <Table>
                    <TableHeader>
                        <TableRow class="hover:bg-transparent">
                            <TableHead>Comprobante</TableHead>
                            <TableHead>Tipo</TableHead>
                            <TableHead>Estado</TableHead>
                            <TableHead>Total</TableHead>
                            <TableHead></TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="comprobante in venta.comprobantes" :key="comprobante.id">
                            <TableCell>{{ nombreComprobante(comprobante) }}</TableCell>
                            <TableCell>{{ comprobante.tipo_comprobante?.descripcion }}</TableCell>
                            <TableCell>
                                <Badge :value="comprobante.estado" :map="estadoBadgeMap" />
                            </TableCell>
                            <TableCell>{{ money(comprobante.total, comprobante.moneda_id) }}</TableCell>
                            <TableCell>
                                <div class="flex flex-wrap gap-sp-xs">
                                    <Button
                                        variant="outline" size="sm"
                                        @click="downloadPdf(`/comprobantes/${comprobante.id}/pdf`, `${nombreComprobante(comprobante)}.pdf`)"
                                    >
                                        PDF
                                    </Button>
                                    <Button
                                        v-if="comprobante.has_cdr" variant="outline" size="sm"
                                        @click="downloadFile(`/comprobantes/${comprobante.id}/xml`, `${nombreComprobante(comprobante)}.xml`, 'application/xml')"
                                    >
                                        XML
                                    </Button>
                                    <Button
                                        v-if="comprobante.has_cdr" variant="outline" size="sm"
                                        @click="downloadFile(`/comprobantes/${comprobante.id}/cdr`, `${nombreComprobante(comprobante)}-cdr.zip`, 'application/zip')"
                                    >
                                        CDR
                                    </Button>
                                    <Button
                                        v-if="['pendiente', 'rechazado'].includes(comprobante.estado)"
                                        variant="outline" size="sm"
                                        :disabled="reenviandoId === comprobante.id"
                                        @click="reenviar(comprobante)"
                                    >
                                        {{ reenviandoId === comprobante.id ? 'Enviando...' : 'Reenviar' }}
                                    </Button>
                                </div>
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
            </div>

            <div v-if="venta.comprobantes?.length" class="mt-sp-sm divide-y divide-outline-variant border border-outline-variant md:hidden">
                <div v-for="comprobante in venta.comprobantes" :key="comprobante.id" class="flex flex-col gap-sp-sm bg-surface-container-lowest p-sp-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-body-md font-semibold text-on-surface">{{ nombreComprobante(comprobante) }}</span>
                        <Badge :value="comprobante.estado" :map="estadoBadgeMap" />
                    </div>
                    <div class="flex items-baseline justify-between text-body-md">
                        <span class="text-eyebrow uppercase text-on-surface-variant">Tipo</span>
                        <span class="text-on-surface">{{ comprobante.tipo_comprobante?.descripcion }}</span>
                    </div>
                    <div class="flex items-baseline justify-between text-body-md">
                        <span class="text-eyebrow uppercase text-on-surface-variant">Total</span>
                        <span class="text-on-surface">{{ money(comprobante.total, comprobante.moneda_id) }}</span>
                    </div>
                    <div class="flex flex-wrap justify-end gap-sp-xs border-t border-outline-variant pt-sp-sm">
                        <Button
                            variant="outline" size="sm"
                            @click="downloadPdf(`/comprobantes/${comprobante.id}/pdf`, `${nombreComprobante(comprobante)}.pdf`)"
                        >
                            PDF
                        </Button>
                        <Button
                            v-if="comprobante.has_cdr" variant="outline" size="sm"
                            @click="downloadFile(`/comprobantes/${comprobante.id}/xml`, `${nombreComprobante(comprobante)}.xml`, 'application/xml')"
                        >
                            XML
                        </Button>
                        <Button
                            v-if="comprobante.has_cdr" variant="outline" size="sm"
                            @click="downloadFile(`/comprobantes/${comprobante.id}/cdr`, `${nombreComprobante(comprobante)}-cdr.zip`, 'application/zip')"
                        >
                            CDR
                        </Button>
                        <Button
                            v-if="['pendiente', 'rechazado'].includes(comprobante.estado)"
                            variant="outline" size="sm"
                            :disabled="reenviandoId === comprobante.id"
                            @click="reenviar(comprobante)"
                        >
                            {{ reenviandoId === comprobante.id ? 'Enviando...' : 'Reenviar' }}
                        </Button>
                    </div>
                </div>
            </div>
        </div>

        <Dialog :open="dialogOpen" title="Emitir comprobante" @close="dialogOpen = false">
            <div class="flex flex-col gap-sp-md">
                <FormSelect v-model="tipoComprobante" label="Tipo de comprobante" :options="tipoComprobanteOptions" />
                <p v-if="emitirError" class="text-body-md text-error">{{ emitirError }}</p>
                <div class="flex justify-end gap-sp-sm">
                    <Button variant="outline" @click="dialogOpen = false">Cancelar</Button>
                    <Button :disabled="emitiendo" @click="emitirComprobante">
                        {{ emitiendo ? 'Emitiendo...' : 'Emitir' }}
                    </Button>
                </div>
            </div>
        </Dialog>
    </div>
</template>

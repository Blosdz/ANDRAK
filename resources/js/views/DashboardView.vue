<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import http from '../http';
import StatCard from '../components/StatCard.vue';
import Badge from '../components/Badge.vue';
import PageEyebrow from '../components/PageEyebrow.vue';
import DashboardChart from '../components/DashboardChart.vue';

const router = useRouter();
const data = ref(null);
const loading = ref(true);
const error = ref('');
const empresaId = ref('');
const anio = ref(new Date().getFullYear());
const moneda = ref('PEN');
const anios = computed(() => Array.from({ length: 5 }, (_, index) => new Date().getFullYear() - index));
const flujoSeries = computed(() => data.value ? [
    { label: 'Cotizaciones', values: data.value.grafica.cotizaciones, color: '#fbae17' },
    { label: 'Ventas', values: data.value.grafica.ventas, color: '#001b3d' },
] : []);
const crecimientoSeries = computed(() => data.value ? [
    { label: `${anio.value}`, values: data.value.grafica[`ventas_${moneda.value.toLowerCase()}`], color: '#001b3d' },
    { label: `${anio.value - 1}`, values: data.value.grafica[`ventas_${moneda.value.toLowerCase()}_anterior`], color: '#fbae17' },
] : []);
const comprobantesSeries = computed(() => data.value ? [
    { label: 'Facturas', values: data.value.grafica.facturas, color: '#001b3d' },
    { label: 'Boletas', values: data.value.grafica.boletas, color: '#fbae17' },
] : []);

function money(monto, moneda) {
    return `${moneda} ${Number(monto).toFixed(2)}`;
}

async function fetchDashboard() {
    loading.value = true;
    error.value = '';
    try {
        const { data: payload } = await http.get('/dashboard', { params: { empresa_id: empresaId.value || undefined, anio: anio.value } });
        data.value = payload;
    } catch {
        error.value = 'No se pudo cargar el dashboard.';
    } finally {
        loading.value = false;
    }
}

onMounted(fetchDashboard);
</script>

<template>
    <div class="flex flex-col gap-sp-md">
        <div class="flex flex-wrap items-end justify-between gap-sp-sm">
            <div>
                <PageEyebrow>Resumen general</PageEyebrow>
                <h1 class="text-headline-lg-mobile text-on-surface lg:text-headline-lg">Dashboard</h1>
            </div>
            <div class="flex flex-wrap gap-sp-sm">
                <label class="flex flex-col gap-1 text-label-sm text-on-surface-variant">Emisor
                    <select v-model="empresaId" class="min-h-10 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 text-body-md text-on-surface" @change="fetchDashboard">
                        <option value="">Todos los emisores</option>
                        <option v-for="empresa in data?.emisores || []" :key="empresa.id" :value="String(empresa.id)">{{ empresa.nombre_comercial || empresa.razon_social }} · {{ empresa.ruc }}</option>
                    </select>
                </label>
                <label class="flex flex-col gap-1 text-label-sm text-on-surface-variant">Año de las gráficas
                    <select v-model.number="anio" class="min-h-10 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 text-body-md text-on-surface" @change="fetchDashboard">
                        <option v-for="opcion in anios" :key="opcion" :value="opcion">{{ opcion }}</option>
                    </select>
                </label>
            </div>
        </div>

        <div v-if="loading" class="text-on-surface-variant">Cargando...</div>
        <div v-else-if="error" class="text-error">{{ error }}</div>

        <template v-else-if="data">
            <div class="grid grid-cols-2 gap-sp-sm sm:grid-cols-4">
                <StatCard label="Productos (global)" :value="data.total_productos" />
                <StatCard label="Stock (global)" :value="data.stock_total" />
                <StatCard :label="empresaId ? 'Clientes del emisor' : 'Total de clientes'" :value="data.total_clientes" />
                <StatCard label="Cotizaciones del mes" :value="data.cotizaciones_mes" />
                <StatCard label="Ventas del mes" :value="data.ventas_mes" />
                <StatCard label="Monto vendido" :value="`${money(data.monto_vendido_pen, 'PEN')} / ${money(data.monto_vendido_usd, 'USD')}`" />
                <StatCard label="Facturas del mes" :value="data.facturas_mes" />
                <StatCard label="Boletas del mes" :value="data.boletas_mes" />
            </div>

            <div class="grid grid-cols-1 gap-sp-md xl:grid-cols-2">
                <section class="min-w-0 rounded-lg border border-outline-variant bg-surface-container-lowest p-sp-md">
                    <h2 class="mb-sp-sm text-title-md font-semibold text-on-surface">Cotizaciones y ventas · {{ anio }}</h2>
                    <DashboardChart :labels="data.grafica.meses" :series="flujoSeries" />
                </section>
                <section class="min-w-0 rounded-lg border border-outline-variant bg-surface-container-lowest p-sp-md">
                    <div class="mb-sp-sm flex flex-wrap items-center justify-between gap-2">
                        <h2 class="text-title-md font-semibold text-on-surface">Crecimiento de ventas</h2>
                        <select v-model="moneda" aria-label="Moneda de la gráfica de ventas" class="min-h-9 rounded-lg border border-outline-variant bg-surface-container-lowest px-3 text-body-md text-on-surface">
                            <option value="PEN">PEN</option><option value="USD">USD</option>
                        </select>
                    </div>
                    <DashboardChart :labels="data.grafica.meses" :series="crecimientoSeries" type="line" :unit="moneda === 'PEN' ? 'S/ ' : 'US$ '" />
                </section>
                <section class="min-w-0 rounded-lg border border-outline-variant bg-surface-container-lowest p-sp-md xl:col-span-2">
                    <h2 class="mb-sp-sm text-title-md font-semibold text-on-surface">Facturas y boletas registradas · {{ anio }}</h2>
                    <DashboardChart :labels="data.grafica.meses" :series="comprobantesSeries" />
                </section>
            </div>

            <div class="grid grid-cols-1 gap-sp-md lg:grid-cols-2">
                <div class="border border-outline-variant bg-surface-container-lowest p-sp-md">
                    <h2 class="text-label-md font-semibold text-on-surface-variant">Ultimas cotizaciones</h2>
                    <ul class="mt-sp-sm flex flex-col divide-y divide-outline-variant">
                        <li v-if="!data.ultimas_cotizaciones.length" class="py-sp-sm text-body-md text-on-surface-variant">Sin cotizaciones.</li>
                        <li
                            v-for="cot in data.ultimas_cotizaciones"
                            :key="cot.id"
                            class="flex cursor-pointer items-center justify-between py-sp-sm"
                            @click="router.push({ name: 'cotizaciones.show', params: { id: cot.id } })"
                        >
                            <div>
                                <p class="text-body-md text-on-surface">{{ cot.numero }}</p>
                                <p class="text-label-sm text-on-surface-variant">{{ cot.cliente?.nombre }}</p>
                            </div>
                            <Badge :value="cot.estado" />
                        </li>
                    </ul>
                </div>

                <div class="border border-outline-variant bg-surface-container-lowest p-sp-md">
                    <h2 class="text-label-md font-semibold text-on-surface-variant">Productos con stock bajo</h2>
                    <ul class="mt-sp-sm flex flex-col divide-y divide-outline-variant">
                        <li v-if="!data.stock_bajo.length" class="py-sp-sm text-body-md text-on-surface-variant">Todo el stock esta en niveles normales.</li>
                        <li
                            v-for="item in data.stock_bajo"
                            :key="item.id"
                            class="flex cursor-pointer items-center justify-between py-sp-sm"
                            @click="router.push({ name: 'productos' })"
                        >
                            <span class="text-body-md text-on-surface">{{ item.producto?.nombre }}</span>
                            <span class="text-label-md text-error">{{ item.cantidad_disponible }} / min {{ item.stock_minimo }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </template>
    </div>
</template>

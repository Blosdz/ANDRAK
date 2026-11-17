<script setup>
import { onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import http from '../http';
import StatCard from '../components/StatCard.vue';
import Badge from '../components/Badge.vue';
import PageEyebrow from '../components/PageEyebrow.vue';

const router = useRouter();
const data = ref(null);
const loading = ref(true);

function money(monto, moneda) {
    return `${moneda} ${Number(monto).toFixed(2)}`;
}

async function fetchDashboard() {
    loading.value = true;
    try {
        const { data: payload } = await http.get('/dashboard');
        data.value = payload;
    } finally {
        loading.value = false;
    }
}

onMounted(fetchDashboard);
</script>

<template>
    <div class="flex flex-col gap-sp-md">
        <div>
            <PageEyebrow>Resumen general</PageEyebrow>
            <h1 class="text-headline-lg-mobile text-on-surface lg:text-headline-lg">Dashboard</h1>
        </div>

        <div v-if="loading" class="text-on-surface-variant">Cargando...</div>

        <template v-else-if="data">
            <div class="grid grid-cols-2 gap-sp-sm sm:grid-cols-3 lg:grid-cols-6">
                <StatCard label="Total de productos" :value="data.total_productos" />
                <StatCard label="Stock total" :value="data.stock_total" />
                <StatCard label="Total de clientes" :value="data.total_clientes" />
                <StatCard label="Cotizaciones del mes" :value="data.cotizaciones_mes" />
                <StatCard label="Ventas del mes" :value="data.ventas_mes" />
                <StatCard label="Monto vendido" :value="`${money(data.monto_vendido_pen, 'PEN')} / ${money(data.monto_vendido_usd, 'USD')}`" />
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

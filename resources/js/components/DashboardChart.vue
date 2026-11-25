<script setup>
import { computed } from 'vue';

const props = defineProps({
    labels: { type: Array, required: true },
    series: { type: Array, required: true },
    type: { type: String, default: 'bar' },
    unit: { type: String, default: '' },
});

const width = 720;
const height = 260;
const left = 48;
const right = 12;
const top = 18;
const bottom = 36;
const plotWidth = width - left - right;
const plotHeight = height - top - bottom;
const maxValue = computed(() => Math.max(1, ...props.series.flatMap((item) => item.values.map(Number))) * 1.1);
const ticks = computed(() => [0, 1, 2, 3, 4].map((index) => ({
    y: top + (plotHeight / 4) * index,
    value: Math.round(maxValue.value * (1 - index / 4)),
})));
const x = (index) => left + (index + 0.5) * (plotWidth / props.labels.length);
const y = (value) => top + plotHeight * (1 - Number(value) / maxValue.value);
const points = (values) => values.map((value, index) => `${x(index)},${y(value)}`).join(' ');
const barWidth = computed(() => Math.min(20, plotWidth / props.labels.length / (props.series.length + 1)));
const formatted = (value) => `${props.unit}${Number(value).toLocaleString('es-PE', { maximumFractionDigits: props.unit ? 2 : 0 })}`;
</script>

<template>
    <div class="overflow-x-auto">
        <svg :viewBox="`0 0 ${width} ${height}`" class="min-w-[640px] w-full" role="img" :aria-label="`Gráfica ${type === 'line' ? 'de líneas' : 'de barras'}: ${series.map((item) => item.label).join(' y ')}`">
            <g v-for="tick in ticks" :key="tick.y">
                <line :x1="left" :x2="width - right" :y1="tick.y" :y2="tick.y" stroke="#e2e8f0" />
                <text :x="left - 8" :y="tick.y + 4" text-anchor="end" fill="#64748b" font-size="11">{{ tick.value }}</text>
            </g>
            <text v-for="(label, index) in labels" :key="label" :x="x(index)" :y="height - 12" text-anchor="middle" fill="#64748b" font-size="11">{{ label }}</text>

            <template v-if="type === 'line'">
                <g v-for="(item, seriesIndex) in series" :key="item.label">
                    <polyline fill="none" :stroke="item.color" :stroke-width="seriesIndex ? 2 : 3" :stroke-dasharray="seriesIndex ? '6 5' : undefined" stroke-linecap="round" stroke-linejoin="round" :points="points(item.values)" />
                    <circle v-for="(value, index) in item.values" :key="index" :cx="x(index)" :cy="y(value)" r="4" :fill="item.color">
                        <title>{{ labels[index] }} · {{ item.label }}: {{ formatted(value) }}</title>
                    </circle>
                </g>
            </template>
            <template v-else>
                <g v-for="(item, seriesIndex) in series" :key="item.label">
                    <rect v-for="(value, index) in item.values" :key="index"
                        :x="x(index) - (series.length * barWidth) / 2 + seriesIndex * barWidth"
                        :y="y(value)" :width="barWidth - 2" :height="Math.max(0, top + plotHeight - y(value))" :fill="item.color" rx="2">
                        <title>{{ labels[index] }} · {{ item.label }}: {{ formatted(value) }}</title>
                    </rect>
                </g>
            </template>
        </svg>
    </div>
    <div class="mt-sp-sm flex flex-wrap gap-x-sp-md gap-y-1 text-label-sm text-on-surface-variant">
        <span v-for="item in series" :key="item.label" class="inline-flex items-center gap-2">
            <span class="inline-block h-2.5 w-2.5 rounded-full" :style="{ backgroundColor: item.color }"></span>{{ item.label }}
        </span>
    </div>
</template>

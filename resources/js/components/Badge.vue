<script setup>
import { computed } from 'vue';

const props = defineProps({
    value: { type: String, required: true },
    map: { type: Object, default: () => ({}) },
});

// Exact chip colors from the Quotation App reference mockup's statusStyleObj().
const defaultMap = {
    borrador: 'bg-[#e2e8f0] text-[#44474e]',
    enviada: 'bg-[#fef3d6] text-[#92660a]',
    aceptada: 'bg-[#d7f5dd] text-[#0a5a26]',
    rechazada: 'bg-[#fde2e1] text-[#b3261e]',
    convertida: 'bg-[#d7f5dd] text-[#0a5a26]',
    // Cliente / producto statuses (not covered by the mockup's quotation states).
    activo: 'bg-[#d7f5dd] text-[#0a5a26]',
    inactivo: 'bg-[#e2e8f0] text-[#44474e]',
};

const classes = computed(() => props.map[props.value] || defaultMap[props.value] || 'bg-[#e2e8f0] text-[#44474e]');
</script>

<template>
    <span class="inline-flex items-center rounded-sm px-sp-sm py-1.5 text-label-sm font-medium uppercase" :class="classes">
        {{ value }}
    </span>
</template>

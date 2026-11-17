<script setup>
import { computed } from 'vue';
import { tagColorFor } from '../utils/tagColor';

const props = defineProps({
    codigo: { type: String, default: '' },
});

const color = computed(() => tagColorFor(props.codigo));
</script>

<template>
    <span
        v-if="codigo"
        class="inline-flex items-center rounded-sm px-sp-sm py-1 text-label-sm font-medium"
        :style="{ background: color.bg, color: color.text }"
    >
        #{{ codigo }}
    </span>
</template>

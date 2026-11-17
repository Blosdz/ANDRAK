<script setup>
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <tr v-bind="$attrs" :class="cn('odd:bg-surface-container-lowest even:bg-surface-container-low hover:bg-surface-container-high', $attrs.class)">
        <slot />
    </tr>
</template>

<script setup>
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <h5 v-bind="$attrs" :class="cn('mb-1 font-sans text-body-md font-bold leading-none tracking-tight', $attrs.class)">
        <slot />
    </h5>
</template>

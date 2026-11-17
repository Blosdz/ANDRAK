<script setup>
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <thead v-bind="$attrs" :class="cn('bg-surface-container', $attrs.class)">
        <slot />
    </thead>
</template>

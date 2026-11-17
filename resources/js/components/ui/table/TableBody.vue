<script setup>
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <tbody v-bind="$attrs" :class="cn('divide-y divide-outline-variant', $attrs.class)">
        <slot />
    </tbody>
</template>

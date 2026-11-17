<script setup>
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <th
        v-bind="$attrs"
        :class="cn('whitespace-nowrap px-sp-sm py-sp-sm text-eyebrow text-on-surface-variant uppercase', $attrs.class)"
    >
        <slot />
    </th>
</template>

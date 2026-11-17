<script setup>
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <td v-bind="$attrs" :class="cn('px-sp-sm py-sp-sm', $attrs.class)">
        <slot />
    </td>
</template>

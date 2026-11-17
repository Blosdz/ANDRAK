<script setup>
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <div v-bind="$attrs" :class="cn('text-body-md [&_p]:leading-relaxed', $attrs.class)">
        <slot />
    </div>
</template>

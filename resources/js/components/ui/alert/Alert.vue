<script setup>
import { cn } from '@/lib/utils';
import { alertVariants } from './alertVariants';

defineProps({
    variant: { type: String, default: 'default' },
});

defineOptions({ inheritAttrs: false });
</script>

<template>
    <div role="alert" v-bind="$attrs" :class="cn(alertVariants({ variant }), $attrs.class)">
        <slot />
    </div>
</template>

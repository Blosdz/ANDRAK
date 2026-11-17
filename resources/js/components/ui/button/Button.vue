<script setup>
import { cn } from '@/lib/utils';
import { buttonVariants } from './buttonVariants';

defineProps({
    variant: { type: String, default: 'default' },
    size: { type: String, default: 'default' },
    as: { type: String, default: 'button' },
});

defineOptions({ inheritAttrs: false });
</script>

<template>
    <component
        :is="as"
        :type="as === 'button' ? 'button' : undefined"
        v-bind="$attrs"
        :class="cn(buttonVariants({ variant, size }), $attrs.class)"
    >
        <slot />
    </component>
</template>

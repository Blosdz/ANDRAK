<script setup>
import { cn } from '@/lib/utils';

defineProps({
    modelValue: { type: [String, Number], default: '' },
    type: { type: String, default: 'text' },
    error: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
defineOptions({ inheritAttrs: false });
</script>

<template>
    <input
        :value="modelValue"
        :type="type"
        v-bind="$attrs"
        :class="cn(
            'h-12 w-full rounded-sm border bg-surface-container-lowest px-sp-sm text-body-md text-on-surface placeholder-outline outline-none transition-colors focus:ring-2 focus:ring-primary/15',
            error ? 'border-error' : 'border-outline-variant focus:border-primary',
            $attrs.class,
        )"
        @input="$emit('update:modelValue', $event.target.value)"
    >
</template>

<script setup>
defineProps({
    modelValue: { type: Boolean, default: false },
    label: { type: String, default: '' },
});

defineEmits(['update:modelValue']);
</script>

<template>
    <label class="flex h-12 items-center gap-2">
        <input
            type="checkbox"
            :checked="modelValue"
            class="h-5 w-5 shrink-0 rounded-sm border border-outline-variant text-primary accent-primary focus:ring-2 focus:ring-primary/15"
            @change="$emit('update:modelValue', $event.target.checked)"
        >
        <span class="text-body-md text-on-surface">{{ label }}</span>
    </label>
</template>

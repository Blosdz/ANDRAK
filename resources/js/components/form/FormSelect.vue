<script setup>
import Select from '../ui/select/Select.vue';

defineProps({
    modelValue: { type: [String, Number], default: '' },
    label: { type: String, default: '' },
    options: { type: Array, required: true }, // [{ value, label }]
    error: { type: String, default: '' },
    required: { type: Boolean, default: false },
});

defineEmits(['update:modelValue']);
defineOptions({ inheritAttrs: false });
</script>

<template>
    <label class="block">
        <span v-if="label" class="text-field-label text-on-surface-variant">
            {{ label }}<span v-if="required" class="text-error"> *</span><span v-else class="text-outline"> (opcional)</span>
        </span>
        <Select
            class="mt-1"
            :model-value="modelValue"
            :options="options"
            :error="!!error"
            v-bind="$attrs"
            @update:model-value="$emit('update:modelValue', $event)"
        />
        <p v-if="error" class="mt-1 text-label-sm text-error">{{ error }}</p>
    </label>
</template>

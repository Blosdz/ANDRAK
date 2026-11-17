<script setup>
import {
    SelectContent,
    SelectIcon,
    SelectItem,
    SelectItemIndicator,
    SelectItemText,
    SelectPortal,
    SelectRoot,
    SelectTrigger,
    SelectValue,
    SelectViewport,
} from 'reka-ui';
import { ChevronDownIcon, CheckIcon } from '@heroicons/vue/24/outline';
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: { type: [String, Number], default: '' },
    options: { type: Array, required: true }, // [{ value, label }]
    error: { type: Boolean, default: false },
    disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

defineOptions({ inheritAttrs: false });

// Reka UI's SelectItem forbids an empty-string value (it's reserved to mean
// "no selection"), but this app relies on '' for "all" filter options — map
// it to a sentinel internally and translate back on the way out.
const EMPTY_SENTINEL = '__empty__';
const toItemValue = (value) => (value === '' ? EMPTY_SENTINEL : String(value));
const fromItemValue = (value) => (value === EMPTY_SENTINEL ? '' : value);
</script>

<template>
    <SelectRoot
        :model-value="toItemValue(props.modelValue)"
        :disabled="disabled"
        @update:model-value="(v) => emit('update:modelValue', fromItemValue(v))"
    >
        <SelectTrigger
            v-bind="$attrs"
            :class="cn(
                'flex h-12 w-full items-center justify-between rounded-sm border bg-surface-container-lowest px-sp-sm text-body-md text-on-surface outline-none transition-colors focus:ring-2 focus:ring-primary/15 disabled:cursor-not-allowed disabled:opacity-60',
                error ? 'border-error' : 'border-outline-variant focus:border-primary',
                $attrs.class,
            )"
        >
            <SelectValue />
            <SelectIcon>
                <ChevronDownIcon class="h-4 w-4 text-on-surface-variant" />
            </SelectIcon>
        </SelectTrigger>
        <SelectPortal>
            <SelectContent
                class="z-50 max-h-60 overflow-hidden rounded-md border border-outline-variant bg-surface-container-lowest shadow-[0_4px_12px_rgba(0,27,61,0.1)]"
                position="popper"
                :side-offset="4"
            >
                <SelectViewport class="p-1">
                    <SelectItem
                        v-for="option in options"
                        :key="option.value"
                        :value="toItemValue(option.value)"
                        class="flex cursor-pointer items-center justify-between px-sp-sm py-2 text-body-md text-on-surface outline-none data-[highlighted]:bg-surface-container-high"
                    >
                        <SelectItemText>{{ option.label }}</SelectItemText>
                        <SelectItemIndicator>
                            <CheckIcon class="h-4 w-4 text-primary" />
                        </SelectItemIndicator>
                    </SelectItem>
                </SelectViewport>
            </SelectContent>
        </SelectPortal>
    </SelectRoot>
</template>

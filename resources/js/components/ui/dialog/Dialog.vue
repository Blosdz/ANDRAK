<script setup>
import {
    DialogClose,
    DialogContent,
    DialogOverlay,
    DialogPortal,
    DialogRoot,
    DialogTitle,
} from 'reka-ui';
import { XMarkIcon } from '@heroicons/vue/24/outline';
import { cn } from '@/lib/utils';

const props = defineProps({
    open: { type: Boolean, required: true },
    title: { type: String, default: '' },
    maxWidth: { type: String, default: 'max-w-lg' },
});

const emit = defineEmits(['close']);
</script>

<template>
    <DialogRoot :open="props.open" @update:open="(v) => !v && emit('close')">
        <DialogPortal>
            <DialogOverlay class="fixed inset-0 z-40 bg-on-surface/40 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0" />
            <DialogContent
                :class="cn(
                    'fixed top-1/2 left-1/2 z-50 max-h-[90vh] w-full -translate-x-1/2 -translate-y-1/2 overflow-y-auto rounded-lg border border-outline-variant bg-surface-container-lowest p-0 shadow-[0_4px_12px_rgba(0,27,61,0.1)] data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95',
                    maxWidth,
                )"
            >
                <div v-if="title" class="flex items-center justify-between bg-primary px-sp-md py-sp-sm text-on-primary">
                    <DialogTitle class="font-sans text-body-lg font-bold">{{ title }}</DialogTitle>
                    <DialogClose class="ml-auto rounded-sm p-1 text-inverse-primary hover:bg-white/10" @click="emit('close')">
                        <XMarkIcon class="h-5 w-5" />
                    </DialogClose>
                </div>
                <div class="p-margin-mobile">
                    <slot />
                </div>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>

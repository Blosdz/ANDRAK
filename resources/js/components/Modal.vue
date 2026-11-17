<script setup>
import Dialog from './ui/dialog/Dialog.vue';

defineProps({
    open: { type: Boolean, required: true },
    title: { type: String, default: '' },
    maxWidth: { type: String, default: 'max-w-lg' },
});

defineEmits(['close']);
</script>

<template>
    <Dialog :open="open" :title="title" :max-width="maxWidth" @close="$emit('close')">
        <slot />
    </Dialog>
</template>

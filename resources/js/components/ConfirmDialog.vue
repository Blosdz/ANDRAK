<script setup>
import Modal from './Modal.vue';
import Button from './ui/button/Button.vue';

defineProps({
    open: { type: Boolean, required: true },
    title: { type: String, default: 'Confirmar' },
    message: { type: String, required: true },
    confirmLabel: { type: String, default: 'Eliminar' },
    loading: { type: Boolean, default: false },
});

defineEmits(['close', 'confirm']);
</script>

<template>
    <Modal :open="open" :title="title" max-width="max-w-sm" @close="$emit('close')">
        <p class="text-body-md text-on-surface-variant">{{ message }}</p>

        <div class="mt-sp-md flex justify-end gap-2">
            <Button variant="ghost" size="sm" @click="$emit('close')">Cancelar</Button>
            <Button variant="destructive" size="sm" :disabled="loading" @click="$emit('confirm')">
                {{ confirmLabel }}
            </Button>
        </div>
    </Modal>
</template>

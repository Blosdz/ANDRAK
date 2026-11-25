<script setup>
import { reactive, ref, watch } from 'vue';
import http from '../../http';
import Modal from '../../components/Modal.vue';
import FormInput from '../../components/form/FormInput.vue';
import Button from '../../components/ui/button/Button.vue';

const props = defineProps({
    open: { type: Boolean, required: true },
    cliente: { type: Object, default: null },
});

const emit = defineEmits(['close', 'saved']);

const saving = ref(false);
const errors = ref({});

const form = reactive({
    nombre: '', ruc: '', empresa: '', direccion: '', correo: '', telefono: '', contacto: '',
});

watch(() => props.open, (isOpen) => {
    if (!isOpen) return;

    errors.value = {};
    Object.assign(form, {
        nombre: props.cliente?.nombre || '',
        ruc: props.cliente?.ruc || '',
        empresa: props.cliente?.empresa || '',
        direccion: props.cliente?.direccion || '',
        correo: props.cliente?.correo || '',
        telefono: props.cliente?.telefono || '',
        contacto: props.cliente?.contacto || '',
    });
});

async function guardar() {
    saving.value = true;
    errors.value = {};

    try {
        let data;
        if (props.cliente) {
            ({ data } = await http.put(`/clientes/${props.cliente.id}`, form));
        } else {
            ({ data } = await http.post('/clientes', form));
        }

        emit('saved', data);
    } catch (e) {
        errors.value = e.response?.data?.errors || {};
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <Modal :open="open" :title="cliente ? 'Editar Cliente' : 'Nuevo Cliente'" max-width="max-w-lg" @close="$emit('close')">
        <form class="flex flex-col gap-sp-md" @submit.prevent="guardar">
            <FormInput v-model="form.nombre" label="Nombre" required :error="errors.nombre?.[0]" />
            <FormInput v-model="form.ruc" label="RUC" :error="errors.ruc?.[0]" />
            <FormInput v-model="form.empresa" label="Empresa" :error="errors.empresa?.[0]" />
            <FormInput v-model="form.direccion" label="Direccion" :error="errors.direccion?.[0]" />
            <FormInput v-model="form.correo" type="email" label="Correo" :error="errors.correo?.[0]" />
            <FormInput v-model="form.telefono" label="Telefono" :error="errors.telefono?.[0]" />
            <FormInput v-model="form.contacto" label="Persona de contacto" :error="errors.contacto?.[0]" />

            <div class="flex justify-end gap-2">
                <Button type="button" variant="ghost" @click="$emit('close')">Cancelar</Button>
                <Button type="submit" :disabled="saving">{{ saving ? 'Guardando...' : 'Guardar' }}</Button>
            </div>
        </form>
    </Modal>
</template>

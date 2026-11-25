<script setup>
import { computed, reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/auth';
import Input from './ui/input/Input.vue';
import Button from './ui/button/Button.vue';
import Alert from './ui/alert/Alert.vue';
import AlertDescription from './ui/alert/AlertDescription.vue';
import PageEyebrow from './PageEyebrow.vue';

const appName = window.appConfig.appName;
const registrationEnabled = window.appConfig.registrationEnabled !== false;
console.log(appName);
const router = useRouter();
const auth = useAuthStore();

const mode = ref('login');
const loading = ref(false);
const error = ref('');

const form = reactive({
    nombre: '',
    mail: '',
    password: '',
});

const isRegister = computed(() => mode.value === 'register');

const heading = computed(() =>
    isRegister.value ? `Crea tu cuenta en ${appName}` : `Bienvenido de nuevo a ${appName}`,
);

const subheading = computed(() =>
    isRegister.value
        ? 'Registra tus datos para empezar a cotizar y vender.'
        : 'Ingresa tus datos para acceder a tus cotizaciones y ventas.',
);

const submitLabel = computed(() => {
    if (loading.value) {
        return isRegister.value ? 'Creando cuenta...' : 'Iniciando sesion...';
    }

    return isRegister.value ? 'Crear cuenta' : 'Log in';
});

function toggleMode() {
    if (!registrationEnabled) return;
    mode.value = isRegister.value ? 'login' : 'register';
    error.value = '';
}

async function submit() {
    loading.value = true;
    error.value = '';

    try {
        if (isRegister.value) {
            await auth.register(form);
        } else {
            await auth.login({ mail: form.mail, password: form.password });
        }

        router.push({ name: 'dashboard' });
    } catch (e) {
        const errors = e.response?.data?.errors;
        error.value = errors
            ? Object.values(errors).flat()[0]
            : 'Ocurrio un error. Intenta de nuevo.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="flex min-h-screen flex-wrap">
        <!-- Brand panel -->
        <div class="flex min-h-80 flex-1 basis-[420px] flex-col justify-between bg-primary p-margin-mobile lg:p-sp-xl">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 items-center justify-center rounded-lg bg-secondary font-sans text-body-lg font-extrabold text-primary">A</span>
                <span class="font-sans text-body-lg font-bold text-on-primary">{{ appName }}</span>
            </div>

            <div>
                <PageEyebrow dark>Plataforma de cotizaciones</PageEyebrow>
                <h2 class="max-w-md font-sans text-headline-lg-mobile font-extrabold tracking-tight text-inverse-on-surface lg:text-headline-xl">
                    Cotiza, vende y controla tu inventario en un solo lugar.
                </h2>
                <p class="mt-sp-sm max-w-sm text-body-md text-inverse-primary">
                    Una sola plataforma para tus cotizaciones, ventas y almacen.
                </p>
            </div>

            <div class="text-eyebrow normal-case text-inverse-primary/70">© {{ new Date().getFullYear() }} {{appName}}</div>
        </div>

        <!-- Form panel -->
        <div class="flex flex-1 basis-[420px] items-center justify-center bg-surface p-margin-mobile lg:p-sp-xl">
            <div class="w-full max-w-sm">
                <PageEyebrow :tick="false">{{ isRegister ? 'Crear cuenta' : 'Acceso al sistema' }}</PageEyebrow>
                <h1 class="font-sans text-headline-lg-mobile font-extrabold tracking-tight text-on-surface">{{ heading }}</h1>
                <p class="mt-sp-sm text-body-md text-on-surface-variant">{{ subheading }}</p>

                <form class="mt-sp-md flex flex-col gap-sp-md" @submit.prevent="submit">
                    <label v-if="isRegister" class="block">
                        <span class="text-field-label text-on-surface-variant">Nombre completo</span>
                        <Input v-model="form.nombre" type="text" required placeholder="Andrea Ramirez" class="mt-1" />
                    </label>

                    <label class="block">
                        <span class="text-field-label text-on-surface-variant">Correo electronico</span>
                        <Input v-model="form.mail" type="email" required placeholder="usuario@empresa.pe" class="mt-1" />
                    </label>

                    <label class="block">
                        <span class="text-field-label text-on-surface-variant">Contraseña</span>
                        <Input v-model="form.password" type="password" required minlength="8" placeholder="********" class="mt-1" />
                        <span v-if="!isRegister" class="mt-1 block text-right">
                            <Button type="button" variant="link" class="text-xs">¿Olvidaste tu contraseña?</Button>
                        </span>
                    </label>

                    <Alert v-if="error" variant="destructive">
                        <AlertDescription>{{ error }}</AlertDescription>
                    </Alert>

                    <Button type="submit" :disabled="loading" size="lg" class="mt-sp-xs w-full">
                        {{ submitLabel }}
                    </Button>
                </form>

                <p v-if="registrationEnabled" class="mt-sp-lg text-center text-body-md text-on-surface-variant">
                    <template v-if="isRegister">
                        ¿Ya tienes cuenta?
                        <Button variant="link" @click="toggleMode">Inicia sesion</Button>
                    </template>
                    <template v-else>
                        ¿No tienes cuenta?
                        <Button variant="link" @click="toggleMode">Registrate</Button>
                    </template>
                </p>
            </div>
        </div>
    </div>
</template>

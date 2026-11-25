<script setup>
import { computed, onMounted, reactive, ref } from 'vue';
import http from '../http';
import Tabs from '../components/Tabs.vue';
import Button from '../components/ui/button/Button.vue';
import FormInput from '../components/form/FormInput.vue';
import FormSelect from '../components/form/FormSelect.vue';
import Input from '../components/ui/input/Input.vue';
import Table from '../components/ui/table/Table.vue';
import TableHeader from '../components/ui/table/TableHeader.vue';
import TableBody from '../components/ui/table/TableBody.vue';
import TableRow from '../components/ui/table/TableRow.vue';
import TableHead from '../components/ui/table/TableHead.vue';
import TableCell from '../components/ui/table/TableCell.vue';
import { TrashIcon } from '@heroicons/vue/24/outline';

const tabs = [
    { key: 'general', label: 'Datos generales' },
    { key: 'documentos', label: 'Cotizaciones y contratos' },
    { key: 'sunat', label: 'Credenciales SUNAT' },
    { key: 'certificado', label: 'Certificado digital' },
    { key: 'series', label: 'Series' },
];

const loading = ref(true);
const empresas = ref([]);
const empresaId = ref(null);
const nuevoEmisor = reactive({ nombre_comercial: '', razon_social: '', ruc: '10730862453' });
const nuevoEmisorError = ref('');
const creandoEmisor = ref(false);
const faltaSegundoEmisor = computed(() => !empresas.value.some((item) => item.ruc === '10730862453'));
const opcionesEmisor = computed(() => empresas.value.map((item) => ({
    value: item.id,
    label: `${item.nombre_comercial} — ${item.ruc}`,
})));
const savingGeneral = ref(false);
const savingSunat = ref(false);
const uploadingCertificado = ref(false);
const generalMessage = ref('');
const savingDocumentos = ref(false);
const documentosMessage = ref('');
const sunatMessage = ref('');
const certificadoMessage = ref('');
const seriesMessage = ref('');

const empresa = reactive({
    logo: '',
    marca_agua: '',
    nombre_comercial: '',
    eslogan: '',
    razon_social: '',
    ruc: '',
    direccion: '',
    ubigeo: '',
    establecimiento_codigo: '0000',
    telefono: '',
    whatsapp: '',
    correo: '',
    representante_nombre: '',
    representante_dni: '',
    titular_cuenta: '',
    modo_sunat: 'beta',
    sunat_usuario_sol: '',
    sunat_clave_sol: '',
});

const cuentas = ref([]);

function agregarCuenta() {
    cuentas.value.push({ banco: '', moneda: 'PEN', numero_cuenta: '', cci: '' });
}

function quitarCuenta(cuenta) {
    cuentas.value = cuentas.value.filter((c) => c !== cuenta);
}

const certificado = reactive({ certificado: null, vencimiento: null });
const certificadoArchivo = ref(null);
const certificadoPassword = ref('');

const series = ref([]);
const nuevaSerie = reactive({ tipo_comprobante_id: '01', serie: '', correlativo: 0 });

const modoSunatOptions = [
    { value: 'beta', label: 'Beta (pruebas / homologacion)' },
    { value: 'produccion', label: 'Produccion' },
];

async function fetchEmpresas() {
    const { data } = await http.get('/empresas');
    empresas.value = data;
    empresaId.value = empresaId.value || data[0]?.id || null;
}

async function fetchEmpresa() {
    if (!empresaId.value) return;
    const { data } = await http.get(`/empresas/${empresaId.value}`);
    Object.assign(empresa, data);
    empresa.sunat_clave_sol = '';
    cuentas.value = (data.cuentas_bancarias || []).map((c) => ({
        banco: c.banco,
        moneda: c.moneda,
        numero_cuenta: c.numero_cuenta,
        cci: c.cci,
    }));
}

async function fetchCertificado() {
    if (!empresaId.value) return;
    const { data } = await http.get('/certificado', { params: { empresa_id: empresaId.value } });
    Object.assign(certificado, data);
}

async function fetchSeries() {
    if (!empresaId.value) return;
    const { data } = await http.get('/series', { params: { empresa_id: empresaId.value } });
    series.value = data;
}

async function seleccionarEmisor(id) {
    empresaId.value = Number(id);
    await Promise.all([fetchEmpresa(), fetchCertificado(), fetchSeries()]);
    certificadoArchivo.value = null;
    certificadoPassword.value = '';
    generalMessage.value = '';
    documentosMessage.value = '';
    sunatMessage.value = '';
    certificadoMessage.value = '';
    seriesMessage.value = '';
}

async function crearEmisor() {
    creandoEmisor.value = true;
    nuevoEmisorError.value = '';
    try {
        const { data } = await http.post('/empresas', nuevoEmisor);
        await fetchEmpresas();
        await seleccionarEmisor(data.id);
    } catch (error) {
        nuevoEmisorError.value = error.response?.data?.errors?.nombre_comercial?.[0]
            || error.response?.data?.message || 'No se pudo crear el emisor.';
    } finally {
        creandoEmisor.value = false;
    }
}

function onLogoPick(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
        empresa.logo = reader.result;
    };
    reader.readAsDataURL(file);
}

function onMarcaAguaPick(event) {
    const file = event.target.files?.[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = () => {
        empresa.marca_agua = reader.result;
    };
    reader.readAsDataURL(file);
}

async function guardarGeneral() {
    savingGeneral.value = true;
    generalMessage.value = '';
    try {
        const { data } = await http.put(`/empresas/${empresaId.value}`, {
            nombre_comercial: empresa.nombre_comercial,
            razon_social: empresa.razon_social,
            ruc: empresa.ruc,
            direccion: empresa.direccion,
            ubigeo: empresa.ubigeo,
            establecimiento_codigo: empresa.establecimiento_codigo,
            telefono: empresa.telefono,
            logo: empresa.logo || null,
            marca_agua: empresa.marca_agua || null,
        });
        Object.assign(empresa, data);
        await fetchEmpresas();
        generalMessage.value = 'Datos guardados.';
    } finally {
        savingGeneral.value = false;
    }
}

async function guardarDocumentos() {
    savingDocumentos.value = true;
    documentosMessage.value = '';
    try {
        const { data } = await http.put(`/empresas/${empresaId.value}`, {
            nombre_comercial: empresa.nombre_comercial,
            ruc: empresa.ruc,
            eslogan: empresa.eslogan,
            whatsapp: empresa.whatsapp,
            correo: empresa.correo,
            representante_nombre: empresa.representante_nombre,
            representante_dni: empresa.representante_dni,
            titular_cuenta: empresa.titular_cuenta,
            cuentas: cuentas.value,
        });
        Object.assign(empresa, data);
        cuentas.value = (data.cuentas_bancarias || []).map((c) => ({
            banco: c.banco,
            moneda: c.moneda,
            numero_cuenta: c.numero_cuenta,
            cci: c.cci,
        }));
        documentosMessage.value = 'Datos guardados.';
    } finally {
        savingDocumentos.value = false;
    }
}

async function guardarSunat() {
    savingSunat.value = true;
    sunatMessage.value = '';
    try {
        const { data } = await http.put(`/empresas/${empresaId.value}`, {
            nombre_comercial: empresa.nombre_comercial,
            ruc: empresa.ruc,
            modo_sunat: empresa.modo_sunat,
            sunat_usuario_sol: empresa.sunat_usuario_sol,
            sunat_clave_sol: empresa.sunat_clave_sol,
        });
        Object.assign(empresa, data);
        empresa.sunat_clave_sol = '';
        sunatMessage.value = 'Credenciales guardadas.';
    } finally {
        savingSunat.value = false;
    }
}

function onArchivoChange(event) {
    certificadoArchivo.value = event.target.files[0] || null;
}

async function subirCertificado() {
    if (!certificadoArchivo.value || !certificadoPassword.value) {
        certificadoMessage.value = 'Selecciona el archivo .pfx y escribe la contrasena.';
        return;
    }

    uploadingCertificado.value = true;
    certificadoMessage.value = '';
    try {
        const formData = new FormData();
        formData.append('file', certificadoArchivo.value);
        formData.append('password', certificadoPassword.value);
        formData.append('empresa_id', empresaId.value);

        const { data } = await http.post('/certificado', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (data.success) {
            certificado.certificado = data.certificado;
            certificado.vencimiento = data.vencimiento;
            certificadoPassword.value = '';
            certificadoArchivo.value = null;
            certificadoMessage.value = 'Certificado cargado correctamente.';
        } else {
            certificadoMessage.value = data.message;
        }
    } catch (error) {
        certificadoMessage.value = error.response?.data?.message || 'No se pudo cargar el certificado.';
    } finally {
        uploadingCertificado.value = false;
    }
}

async function eliminarCertificado() {
    await http.delete('/certificado', { params: { empresa_id: empresaId.value } });
    certificado.certificado = null;
    certificado.vencimiento = null;
    certificadoMessage.value = 'Certificado eliminado.';
}

async function guardarSerie(serie) {
    seriesMessage.value = '';
    const { data } = await http.put(`/series/${serie.id}`, {
        serie: serie.serie,
        correlativo: serie.correlativo,
    });
    Object.assign(serie, data);
    seriesMessage.value = 'Series actualizadas.';
}

async function agregarSerie() {
    seriesMessage.value = '';
    try {
        await http.post('/series', { ...nuevaSerie, empresa_id: empresaId.value });
        await fetchSeries();
        nuevaSerie.serie = '';
        nuevaSerie.correlativo = 0;
        seriesMessage.value = 'Serie agregada.';
    } catch (error) {
        seriesMessage.value = error.response?.data?.message || 'No se pudo agregar la serie.';
    }
}

onMounted(async () => {
    loading.value = true;
    try {
        await fetchEmpresas();
        await Promise.all([fetchEmpresa(), fetchCertificado(), fetchSeries()]);
    } finally {
        loading.value = false;
    }
});
</script>

<template>
    <div class="flex flex-col gap-sp-md">
        <h1 class="text-headline-lg-mobile text-on-surface lg:text-headline-lg">Configuracion de empresa</h1>

        <div v-if="loading" class="text-on-surface-variant">Cargando...</div>

        <div v-else class="border border-outline-variant bg-surface-container-lowest p-sp-md">
            <div class="mb-sp-md flex flex-col gap-sp-sm border-b border-outline-variant pb-sp-md">
                <FormSelect v-if="empresas.length" :model-value="empresaId" label="Emisor que deseas configurar"
                    :options="opcionesEmisor" @update:model-value="seleccionarEmisor" />
                <form v-if="faltaSegundoEmisor" class="grid grid-cols-1 items-end gap-sp-sm sm:grid-cols-4" @submit.prevent="crearEmisor">
                    <FormInput v-model="nuevoEmisor.ruc" label="Segundo RUC" required maxlength="11" readonly />
                    <FormInput v-model="nuevoEmisor.nombre_comercial" label="Nombre del segundo emisor" required />
                    <FormInput v-model="nuevoEmisor.razon_social" label="Razón social del titular del RUC" required />
                    <Button type="submit" :disabled="creandoEmisor">{{ creandoEmisor ? 'Agregando...' : 'Agregar emisor' }}</Button>
                    <p v-if="nuevoEmisorError" class="text-body-md text-error sm:col-span-4">{{ nuevoEmisorError }}</p>
                </form>
            </div>
            <Tabs :tabs="tabs">
                <template #general>
                    <form class="flex flex-col gap-sp-md" @submit.prevent="guardarGeneral">
                        <div class="grid grid-cols-1 gap-sp-md sm:grid-cols-2">
                            <FormInput v-model="empresa.nombre_comercial" label="Nombre comercial" required />
                            <FormInput v-model="empresa.razon_social" label="Razon social" />
                            <FormInput v-model="empresa.ruc" label="RUC" required maxlength="11" />
                            <FormInput v-model="empresa.telefono" label="Telefono" />
                            <FormInput v-model="empresa.direccion" label="Direccion" />
                            <FormInput v-model="empresa.ubigeo" label="Ubigeo" maxlength="6" />
                            <FormInput v-model="empresa.establecimiento_codigo" label="Codigo de establecimiento" maxlength="4" />
                        </div>

                        <div class="grid grid-cols-1 gap-sp-md sm:grid-cols-2">
                            <div>
                                <span class="text-field-label text-on-surface-variant">Logo de la empresa</span>
                                <div class="mt-1 flex items-center gap-sp-sm">
                                    <img v-if="empresa.logo" :src="empresa.logo" class="h-14 w-14 object-contain border border-outline-variant">
                                    <input type="file" accept="image/*" class="block w-full text-body-md" @change="onLogoPick">
                                </div>
                                <p class="mt-1 text-label-sm text-on-surface-variant">Aparece en la cabecera de las cotizaciones y contratos.</p>
                            </div>
                            <div>
                                <span class="text-field-label text-on-surface-variant">Marca de agua</span>
                                <div class="mt-1 flex items-center gap-sp-sm">
                                    <img v-if="empresa.marca_agua" :src="empresa.marca_agua" class="h-14 w-14 object-contain border border-outline-variant">
                                    <input type="file" accept="image/*" class="block w-full text-body-md" @change="onMarcaAguaPick">
                                </div>
                                <p class="mt-1 text-label-sm text-on-surface-variant">Se muestra tenue y centrada en las cotizaciones y contratos.</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-sp-sm">
                            <Button type="submit" :disabled="savingGeneral">{{ savingGeneral ? 'Guardando...' : 'Guardar' }}</Button>
                            <span v-if="generalMessage" class="text-body-md text-on-surface-variant">{{ generalMessage }}</span>
                        </div>
                    </form>
                </template>

                <template #documentos>
                    <form class="flex flex-col gap-sp-md" @submit.prevent="guardarDocumentos">
                        <p class="text-body-md text-on-surface-variant">
                            Estos datos aparecen en el PDF de cotizaciones y en el contrato de compra: eslogan de la
                            marca, contacto, representante que firma y cuentas bancarias para el pago.
                        </p>
                        <div class="grid grid-cols-1 gap-sp-md sm:grid-cols-2">
                            <FormInput v-model="empresa.eslogan" label="Eslogan" placeholder="Construimos soluciones, impulsamos tu productividad." />
                            <FormInput v-model="empresa.whatsapp" label="WhatsApp" />
                            <FormInput v-model="empresa.correo" label="Correo de ventas" type="email" />
                            <FormInput v-model="empresa.representante_nombre" label="Representante (firma)" />
                            <FormInput v-model="empresa.representante_dni" label="DNI del representante" />
                            <FormInput v-model="empresa.titular_cuenta" label="Titular de la cuenta" />
                        </div>

                        <div class="flex flex-col gap-sp-sm">
                            <div class="flex items-center justify-between">
                                <span class="text-field-label text-on-surface-variant">Cuentas bancarias</span>
                                <Button type="button" variant="outline" size="sm" @click="agregarCuenta">Agregar cuenta</Button>
                            </div>
                            <div v-if="!cuentas.length" class="text-body-md text-on-surface-variant">
                                No hay cuentas bancarias registradas.
                            </div>
                            <div
                                v-for="(cuenta, index) in cuentas"
                                :key="index"
                                class="grid grid-cols-1 items-end gap-sp-sm border border-outline-variant p-sp-sm sm:grid-cols-5"
                            >
                                <FormInput v-model="cuenta.banco" label="Banco" placeholder="BCP" />
                                <FormSelect
                                    v-model="cuenta.moneda"
                                    label="Moneda"
                                    :options="[{ value: 'PEN', label: 'Soles' }, { value: 'USD', label: 'Dolares' }]"
                                />
                                <FormInput v-model="cuenta.numero_cuenta" label="Numero de cuenta" />
                                <FormInput v-model="cuenta.cci" label="CCI" />
                                <Button type="button" variant="outline" size="icon" title="Quitar cuenta" @click="quitarCuenta(cuenta)">
                                    <TrashIcon class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <div class="flex items-center gap-sp-sm">
                            <Button type="submit" :disabled="savingDocumentos">{{ savingDocumentos ? 'Guardando...' : 'Guardar' }}</Button>
                            <span v-if="documentosMessage" class="text-body-md text-on-surface-variant">{{ documentosMessage }}</span>
                        </div>
                    </form>
                </template>

                <template #sunat>
                    <form class="flex flex-col gap-sp-md" @submit.prevent="guardarSunat">
                        <p class="text-body-md text-on-surface-variant">
                            Configura las credenciales SUNAT del emisor seleccionado. Cada RUC requiere sus propias credenciales.
                        </p>
                        <div class="grid grid-cols-1 gap-sp-md sm:grid-cols-2">
                            <FormSelect v-model="empresa.modo_sunat" label="Modo" :options="modoSunatOptions" />
                            <FormInput v-model="empresa.sunat_usuario_sol" label="Usuario SOL (RUC + usuario)" />
                            <FormInput v-model="empresa.sunat_clave_sol" label="Clave SOL" type="password" placeholder="Dejar en blanco para no cambiar" />
                        </div>
                        <div class="flex items-center gap-sp-sm">
                            <Button type="submit" :disabled="savingSunat">{{ savingSunat ? 'Guardando...' : 'Guardar' }}</Button>
                            <span v-if="sunatMessage" class="text-body-md text-on-surface-variant">{{ sunatMessage }}</span>
                        </div>
                    </form>
                </template>

                <template #certificado>
                    <div class="flex flex-col gap-sp-md">
                        <div>
                            <dt class="text-field-label text-on-surface-variant">Certificado actual</dt>
                            <dd class="text-body-md text-on-surface">
                                {{ certificado.certificado || 'Sin certificado cargado' }}
                                <span v-if="certificado.vencimiento" class="text-on-surface-variant"> (vence {{ certificado.vencimiento }})</span>
                            </dd>
                        </div>

                        <div class="grid grid-cols-1 gap-sp-md sm:grid-cols-2">
                            <label class="block">
                                <span class="text-field-label text-on-surface-variant">Archivo .pfx</span>
                                <input type="file" accept=".pfx,.p12" class="mt-1 block w-full text-body-md" @change="onArchivoChange">
                            </label>
                            <FormInput v-model="certificadoPassword" label="Contrasena del certificado" type="password" />
                        </div>

                        <div class="flex items-center gap-sp-sm">
                            <Button :disabled="uploadingCertificado" @click="subirCertificado">
                                {{ uploadingCertificado ? 'Cargando...' : 'Cargar certificado' }}
                            </Button>
                            <Button v-if="certificado.certificado" variant="outline" @click="eliminarCertificado">Eliminar</Button>
                            <span v-if="certificadoMessage" class="text-body-md text-on-surface-variant">{{ certificadoMessage }}</span>
                        </div>
                    </div>
                </template>

                <template #series>
                    <div class="flex flex-col gap-sp-md">
                        <form class="grid grid-cols-1 items-end gap-sp-sm sm:grid-cols-4" @submit.prevent="agregarSerie">
                            <FormSelect v-model="nuevaSerie.tipo_comprobante_id" label="Tipo" :options="[
                                { value: '01', label: 'Factura' }, { value: '03', label: 'Boleta' }
                            ]" />
                            <FormInput v-model="nuevaSerie.serie" label="Nueva serie" required maxlength="4" />
                            <FormInput v-model="nuevaSerie.correlativo" label="Correlativo inicial" type="number" min="0" required />
                            <Button type="submit">Agregar serie</Button>
                        </form>
                        <div class="hidden overflow-hidden border border-outline-variant md:block">
                            <Table>
                                <TableHeader>
                                    <TableRow class="hover:bg-transparent">
                                        <TableHead>Tipo de comprobante</TableHead>
                                        <TableHead>Serie</TableHead>
                                        <TableHead>Correlativo</TableHead>
                                        <TableHead></TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    <TableRow v-for="serie in series" :key="serie.id">
                                        <TableCell>{{ serie.tipo_comprobante?.descripcion }}</TableCell>
                                        <TableCell>
                                            <Input v-model="serie.serie" class="w-24" maxlength="4" />
                                        </TableCell>
                                        <TableCell>
                                            <Input v-model="serie.correlativo" type="number" class="w-32" min="0" />
                                        </TableCell>
                                        <TableCell>
                                            <Button variant="outline" size="sm" @click="guardarSerie(serie)">Guardar</Button>
                                        </TableCell>
                                    </TableRow>
                                </TableBody>
                            </Table>
                        </div>

                        <div class="divide-y divide-outline-variant border border-outline-variant md:hidden">
                            <div v-for="serie in series" :key="serie.id" class="flex flex-col gap-sp-sm bg-surface-container-lowest p-sp-sm">
                                <span class="text-body-md font-semibold text-on-surface">{{ serie.tipo_comprobante?.descripcion }}</span>
                                <label class="block">
                                    <span class="text-field-label text-on-surface-variant">Serie</span>
                                    <Input v-model="serie.serie" class="mt-1 w-full" maxlength="4" />
                                </label>
                                <label class="block">
                                    <span class="text-field-label text-on-surface-variant">Correlativo</span>
                                    <Input v-model="serie.correlativo" type="number" class="mt-1 w-full" min="0" />
                                </label>
                                <Button variant="outline" size="sm" class="self-end" @click="guardarSerie(serie)">Guardar</Button>
                            </div>
                        </div>
                        <span v-if="seriesMessage" class="text-body-md text-on-surface-variant">{{ seriesMessage }}</span>
                    </div>
                </template>
            </Tabs>
        </div>
    </div>
</template>

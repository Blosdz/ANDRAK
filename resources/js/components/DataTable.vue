<script setup>
import { FlexRender, useVueTable, getCoreRowModel } from '@tanstack/vue-table';
import Table from './ui/table/Table.vue';
import TableHeader from './ui/table/TableHeader.vue';
import TableBody from './ui/table/TableBody.vue';
import TableRow from './ui/table/TableRow.vue';
import TableHead from './ui/table/TableHead.vue';
import TableCell from './ui/table/TableCell.vue';
import Button from './ui/button/Button.vue';

const props = defineProps({
    columns: { type: Array, required: true },
    data: { type: Array, required: true },
    loading: { type: Boolean, default: false },
    meta: { type: Object, default: null }, // { current_page, last_page, total, per_page }
});

const emit = defineEmits(['page-change']);

const table = useVueTable({
    get data() {
        return props.data;
    },
    get columns() {
        return props.columns;
    },
    getCoreRowModel: getCoreRowModel(),
});

function visibleCardCells(row) {
    return row.getVisibleCells().filter((cell) => cell.column.id !== 'acciones' && !cell.column.columnDef.meta?.hideOnCard);
}

function accionesCell(row) {
    return row.getVisibleCells().find((cell) => cell.column.id === 'acciones');
}
</script>

<template>
    <div class="overflow-hidden rounded-lg border border-outline-variant">
        <div class="hidden md:block">
            <Table>
                <TableHeader>
                    <TableRow v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id" class="hover:bg-transparent">
                        <TableHead v-for="header in headerGroup.headers" :key="header.id">
                            <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
                        </TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="loading" class="hover:bg-transparent">
                        <TableCell :colspan="columns.length" class="py-sp-lg text-center text-on-surface-variant">Cargando...</TableCell>
                    </TableRow>
                    <TableRow v-else-if="!data.length" class="hover:bg-transparent">
                        <TableCell :colspan="columns.length" class="py-sp-lg text-center text-on-surface-variant">Sin resultados.</TableCell>
                    </TableRow>
                    <TableRow v-for="row in table.getRowModel().rows" :key="row.id">
                        <TableCell v-for="cell in row.getVisibleCells()" :key="cell.id">
                            <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <div class="divide-y divide-outline-variant md:hidden">
            <div v-if="loading" class="py-sp-lg text-center text-on-surface-variant">Cargando...</div>
            <div v-else-if="!data.length" class="py-sp-lg text-center text-on-surface-variant">Sin resultados.</div>
            <div
                v-for="row in table.getRowModel().rows"
                :key="row.id"
                class="flex flex-col gap-sp-sm bg-surface-container-lowest p-sp-sm"
            >
                <div
                    v-for="cell in visibleCardCells(row)"
                    :key="cell.id"
                    class="flex items-baseline justify-between gap-sp-sm text-body-md"
                >
                    <span class="text-eyebrow uppercase text-on-surface-variant">{{ cell.column.columnDef.header }}</span>
                    <span class="text-right text-on-surface">
                        <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                    </span>
                </div>
                <div v-if="accionesCell(row)" class="flex justify-end gap-1 border-t border-outline-variant pt-sp-sm">
                    <FlexRender :render="accionesCell(row).column.columnDef.cell" :props="accionesCell(row).getContext()" />
                </div>
            </div>
        </div>

        <div
            v-if="meta && meta.last_page > 1"
            class="flex flex-col items-start gap-2 border-t border-outline-variant px-sp-sm py-sp-sm text-label-md text-on-surface-variant sm:flex-row sm:items-center sm:justify-between"
        >
            <span>Pagina {{ meta.current_page }} de {{ meta.last_page }} ({{ meta.total }} resultados)</span>
            <div class="flex gap-2">
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="meta.current_page <= 1"
                    @click="emit('page-change', meta.current_page - 1)"
                >
                    Anterior
                </Button>
                <Button
                    variant="outline"
                    size="sm"
                    :disabled="meta.current_page >= meta.last_page"
                    @click="emit('page-change', meta.current_page + 1)"
                >
                    Siguiente
                </Button>
            </div>
        </div>
    </div>
</template>

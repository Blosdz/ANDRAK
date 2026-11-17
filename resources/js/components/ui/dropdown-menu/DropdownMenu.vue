<script setup>
import {
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuPortal,
    DropdownMenuRoot,
    DropdownMenuTrigger,
} from 'reka-ui';

defineProps({
    items: { type: Array, required: true }, // [{ label, onSelect }]
});

defineOptions({ inheritAttrs: false });
</script>

<template>
    <DropdownMenuRoot>
        <DropdownMenuTrigger v-bind="$attrs" as-child>
            <slot name="trigger" />
        </DropdownMenuTrigger>
        <DropdownMenuPortal>
            <DropdownMenuContent
                class="z-50 min-w-[10rem] overflow-hidden rounded-md border border-outline-variant bg-surface-container-lowest p-1 shadow-[0_4px_12px_rgba(0,27,61,0.1)]"
                :side-offset="4"
                align="end"
            >
                <DropdownMenuItem
                    v-for="item in items"
                    :key="item.label"
                    class="cursor-pointer rounded-sm px-sp-sm py-2 text-body-md text-on-surface outline-none data-[highlighted]:bg-surface-container-high"
                    @select="item.onSelect"
                >
                    {{ item.label }}
                </DropdownMenuItem>
            </DropdownMenuContent>
        </DropdownMenuPortal>
    </DropdownMenuRoot>
</template>

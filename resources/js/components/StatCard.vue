<script setup>
defineProps({
    label: { type: String, required: true },
    value: { type: [String, Number], required: true },
});
</script>

<template>
    <div class="rounded-lg border border-outline-variant bg-surface-container-lowest p-sp-md transition-shadow hover:shadow-[0_4px_12px_rgba(0,27,61,0.1)]">
        <p class="text-label-md text-on-surface-variant">{{ label }}</p>
        <p class="mt-1 text-headline-md text-on-surface">{{ value }}</p>
    </div>
</template>

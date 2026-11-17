<script setup>
defineProps({
    dark: { type: Boolean, default: false },
    tick: { type: Boolean, default: true },
});
</script>

<template>
    <div class="mb-2 flex items-center gap-3 text-eyebrow uppercase" :class="dark ? 'text-inverse-primary' : 'text-on-surface-variant'">
        <span v-if="tick" class="inline-block h-[3px] w-8 shrink-0 bg-secondary"></span>
        <slot />
    </div>
</template>

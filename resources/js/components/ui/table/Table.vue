<script setup>
import { cn } from '@/lib/utils';

defineOptions({ inheritAttrs: false });
</script>

<template>
    <div class="overflow-x-auto">
        <table v-bind="$attrs" :class="cn('w-full text-left text-body-md', $attrs.class)">
            <slot />
        </table>
    </div>
</template>

<script setup>
import { TabsContent, TabsList, TabsRoot, TabsTrigger } from 'reka-ui';

defineProps({
    tabs: { type: Array, required: true }, // [{ key, label }]
});
</script>

<template>
    <TabsRoot :default-value="tabs[0]?.key">
        <TabsList class="flex gap-1 overflow-x-auto border-b border-outline-variant">
            <TabsTrigger
                v-for="tab in tabs"
                :key="tab.key"
                :value="tab.key"
                class="-mb-px shrink-0 border-b-2 border-transparent px-sp-sm py-2 text-label-md whitespace-nowrap text-on-surface-variant uppercase hover:text-on-surface focus:outline-none data-[state=active]:border-primary data-[state=active]:text-primary"
            >
                {{ tab.label }}
            </TabsTrigger>
        </TabsList>
        <TabsContent v-for="tab in tabs" :key="tab.key" :value="tab.key" class="mt-sp-md">
            <slot :name="tab.key" />
        </TabsContent>
    </TabsRoot>
</template>

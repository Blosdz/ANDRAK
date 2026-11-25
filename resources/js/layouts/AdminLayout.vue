<script setup>
import TopNav from '../components/layout/TopNav.vue';
</script>

<template>
    <div class="flex min-h-screen flex-col bg-surface-container-low lg:flex-row">
        <TopNav />

        <main class="min-w-0 flex-1 overflow-y-auto p-margin-mobile lg:p-sp-lg">
            <div class="mx-auto max-w-app">
                <router-view />
            </div>
        </main>

    </div>
</template>

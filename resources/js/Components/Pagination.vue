<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: Array
});
</script>

<template>
<div class="flex flex-wrap mt-6 gap-1">
    <template v-for="(link, key) in links" :key="key">
        
        <!-- Disabled Link -->
        <span
            v-if="!link.url"
            class="px-3 py-1 text-gray-400 border rounded"
            v-html="link.label"
        />

        <!-- Active Link -->
        <Link
            v-else
            :href="link.url"
            v-html="link.label"
            class="px-3 py-1 border rounded"
            :class="{
                'bg-blue-600 text-white': link.active,
                'hover:bg-gray-100': !link.active
            }"
        />
        
    </template>
</div>
</template>
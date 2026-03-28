<script setup>
defineProps({
    headers: Array,
    items: Array
});
</script>

<template>
<div class="bg-white shadow rounded overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-gray-100 text-left">
            <tr>
                <th v-for="h in headers" class="p-3 font-semibold">
                    {{ h }}
                </th>
                <th class="p-3">Actions</th>
            </tr>
        </thead>

        <tbody>
            <tr v-for="item in items" :key="item.id" class="border-t hover:bg-gray-50">
                <slot :item="item" />
            </tr>
        </tbody>
    </table>
</div>
</template>
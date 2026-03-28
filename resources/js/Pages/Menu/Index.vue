<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({ items:Object, filters:Object });

const search = ref(props.filters.search || '');
const selected = ref([]);
const selectAll = ref(false);

watch(search, (value) => {
    router.get('/menus', { search: value }, { preserveState:true, replace:true });
});

function toggleAll() {
    if (selectAll.value) {
        selected.value = props.items.data.map(i => i.id);
    } else {
        selected.value = [];
    }
}

function bulkDelete() {
    if (!confirm('Delete selected items?')) return;

    router.post('/menus/bulk-delete', {
        ids: selected.value
    });
}

function exportCSV() {
    window.location.href = '/menus/export';
}
</script>

<template>
<AdminLayout>

<div class="flex justify-between mb-4">
<h1 class="text-xl font-bold">Menu</h1>

<div class="flex gap-2">
<Link href="/menus/create" class="bg-blue-600 text-white px-4 py-2 rounded">+ Create</Link>
<button @click="exportCSV" class="bg-green-600 text-white px-4 py-2 rounded">Export</button>
</div>
</div>

<input v-model="search" class="border p-2 mb-4 w-full" placeholder="Search..." />

<div v-if="selected.length" class="mb-4">
<button @click="bulkDelete" class="bg-red-600 text-white px-4 py-2 rounded">
Delete Selected ({{ selected.length }})
</button>
</div>

<table class="w-full bg-white shadow rounded">
<thead>
<tr>
<th><input type="checkbox" v-model="selectAll" @change="toggleAll" /></th>
<th class='p-3'>Name</th>
<th class='p-3'>Type</th>
<th class='p-3'>Description</th>
<th>Actions</th>
</tr>
</thead>

<tbody>
<tr v-for="item in items.data" :key="item.id">
<td><input type="checkbox" :value="item.id" v-model="selected" /></td>
<td class='p-3'>{{ item.name }}</td>
<td class='p-3'>{{ item.type }}</td>
<td class='p-3'>{{ item.description }}</td>
<td class="flex gap-2">
<!-- <Link :href="\`/menus/${item.id}/edit\`" class="text-blue-600">Edit</Link> -->
<button @click="router.delete('/menus/'+item.id)" class="text-red-600">Delete</button>
</td>
</tr>
</tbody>
</table>

<Pagination :links="items.links" />

</AdminLayout>
</template>
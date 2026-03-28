<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useForm } from '@inertiajs/vue3';

const props = defineProps({
    item: Object
});

const form = useForm({
    name: '',
    type: '',
    description: ''
});

if (props.item) {
    Object.assign(form, props.item);
}

function submit() {
    if (props.item) {
        form.put('/menus/' + props.item.id);
    } else {
        form.post('/menus');
    }
}
</script>

<template>
<AdminLayout>

<h1 class="text-xl mb-4">create Menu</h1>

<form @submit.prevent="submit">
<input v-model="form.name" placeholder="name" class="border p-2 w-full mb-2" />
<input v-model="form.type" placeholder="type" class="border p-2 w-full mb-2" />
<input v-model="form.description" placeholder="description" class="border p-2 w-full mb-2" />

<button class="bg-blue-600 text-white px-4 py-2 rounded">
Submit
</button>
</form>

</AdminLayout>
</template>
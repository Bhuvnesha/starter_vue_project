<script setup>
import AppLayout from '../Layouts/AppLayout.vue';

defineProps({
  message: String
});
</script>

<template>
  <AppLayout>
    <h1 class="text-2xl">Home Page</h1>
    <p>{{ message }}</p>
  </AppLayout>
</template>
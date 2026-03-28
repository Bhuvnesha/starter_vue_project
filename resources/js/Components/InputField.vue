<script setup>
defineProps({
    modelValue: String,
    label: String,
    error: String
});

defineEmits(['update:modelValue']);
</script>

<template>
<div>
    <label class="block text-sm font-medium">{{ label }}</label>

    <input
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        class="w-full border p-2 rounded"
    />

    <p v-if="error" class="text-red-500 text-sm">{{ error }}</p>
</div>
</template>
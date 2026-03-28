<script setup>
import { usePage } from '@inertiajs/vue3'
import { watch } from 'vue'
import { success, error } from '@/Utils/toast'

const page = usePage()

watch(() => page.props.flash, (flash) => {
    if (flash.success) success(flash.success)
    if (flash.error) error(flash.error)
})
</script>

<template>
<div class="flex h-screen bg-gray-100">
    
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white">
        <div class="p-4 text-xl font-bold border-b border-gray-700">
            Admin Panel
        </div>

        <nav class="p-4 space-y-2">
            <a href="/" class="block p-2 rounded hover:bg-gray-700">Dashboard</a>
            <a href="/products" class="block p-2 rounded hover:bg-gray-700">Products</a>
        </nav>
    </aside>

    <!-- Main -->
    <div class="flex-1 flex flex-col">

        <!-- Navbar -->
        <header class="bg-white shadow p-4 flex justify-between">
            <h1 class="font-semibold">Dashboard</h1>
            <div>User</div>
        </header>

        <!-- Content -->
        <main class="p-6 overflow-y-auto">
            <slot />
        </main>

    </div>
</div>
</template>
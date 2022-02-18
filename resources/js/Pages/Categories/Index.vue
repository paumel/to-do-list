<template>
    <Head title="Categories" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Categories
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-end mb-6">
                            <Link :href="route('categories.create')" class="rounded border border-gray-300 py-2 px-4 ml-2">Create</Link>
                        </div>

                        <div v-if="categories.length < 0">
                            <p>There are no Categories</p>
                        </div>
                        <div v-else v-for="category in categories" :key="category.id">
                            <div class="flex justify-between items-center my-2">
                                <div class="flex items-center">
                                    <Link :href="route('categories.edit', category)">{{category.title}}</Link>
                                </div>
                                <div class="flex">
                                    <Link :href="route('categories.edit', category)" class="rounded border border-gray-300 py-2 px-4 ml-2">Edit</Link>
                                    <form @submit.prevent="deleteCategory(category)">
                                        <button type="submit" class="rounded border border-gray-300 py-2 px-4 ml-2">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script setup>
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head, Link } from '@inertiajs/inertia-vue3';
import {Inertia} from "@inertiajs/inertia";

defineProps({
    categories: Array,
})

function deleteCategory(category) {
    Inertia.delete(route('categories.destroy', category))
}

</script>

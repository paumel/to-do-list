<template>
    <Head title="To Do list" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                To Do list
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <div class="flex justify-end mb-6">
                            <Link :href="route('to-dos.create')" class="rounded border border-gray-300 py-2 px-4 ml-2">Create</Link>
                        </div>

                        <div v-if="to_dos.length < 0">
                            <p>There are no To Dos</p>
                        </div>
                        <div v-else v-for="to_do in to_dos" :key="to_do.id">
                            <div class="flex justify-between items-center my-2">
                                <Link :href="route('to-dos.edit', to_do)">{{to_do.title}}</Link>
                                <div class="flex">
                                    <Link :href="route('to-dos.edit', to_do)" class="rounded border border-gray-300 py-2 px-4 ml-2">Edit</Link>
                                    <form @submit.prevent="deleteToDo(to_do)">
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
    to_dos: Array,
})

function deleteToDo(toDo) {
    Inertia.delete(route('to-dos.destroy', toDo))
}

</script>

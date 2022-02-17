<template>
    <Head title="To Do list" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create new To Do
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <div>
                                <label for="title">Title</label>
                                <input id="title" v-model="form.title" class="rounded w-full border border-gray-300 py-2 px-4" />
                            </div>

                            <div>
                                <label for="description">Description</label>
                                <textarea id="description" v-model="form.description" class="rounded w-full border border-gray-300" />
                            </div>

                            <div>
                                <label for="due_date">Due date</label>
                                <input type="datetime-local" id="due_date" v-model="form.due_date" class="rounded w-full border border-gray-300" />
                            </div>

                            <div class="flex justify-end">
                                <button type="submit" class="rounded border border-gray-300 py-2 px-4 mt-4">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </BreezeAuthenticatedLayout>
</template>

<script setup>
import { reactive } from 'vue'
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
import {Inertia} from "@inertiajs/inertia";

const form = reactive({
    title: null,
    description: null,
    due_date: null,
})

function submit() {
    Inertia.post(route('to-dos.store'), form)
}

</script>

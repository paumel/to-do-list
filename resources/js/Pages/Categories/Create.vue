<template>
    <Head title="Create category" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create new Category
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
                                <div v-if="errors.title" class="text-red-500">{{ errors.title }}</div>
                            </div>

                            <div class="mt-4">
                                <label for="max_to_dos">Maximum number of to dos</label>
                                <input id="max_to_dos" type="number" v-model="form.max_to_dos" class="rounded w-full border border-gray-300" />
                                <div v-if="errors.max_to_dos" class="text-red-500">{{ errors.max_to_dos }}</div>
                            </div>

                            <div class="mt-4">
                                <div class="flex justify-start items-center">
                                    <p>Tags</p>
                                    <button @click="addTag" type="button" class="border border-gray-300 rounded ml-4 px-4 py-2">+</button>
                                </div>
                                <div class="flex flex-wrap mt-4">
                                    <div v-for="(tag, index) in form.tags" class="mt-2">
                                        <input type="text" v-model="form.tags[index]" class="rounded border border-gray-300" />
                                        <button @click="removeTag(index)" type="button" class="border border-gray-300 rounded ml-2 mr-8 px-4 py-2">-</button>
                                        <div v-if="errors['tags' + '.' + index]" class="text-red-500">{{ errors['tags' + '.' + index] }}</div>
                                    </div>
                                </div>
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

defineProps({
    errors: Object,
})

const form = reactive({
    title: null,
    max_to_dos: null,
    tags: [],
})

function submit() {
    Inertia.post(route('categories.store'), form)
}

function addTag() {
    form.tags.push('')
}

function removeTag(index) {
    form.tags.splice(index, 1)
}

</script>

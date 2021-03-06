<template>
    <Head title="To Do list" />

    <BreezeAuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit To Do
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
                                <label for="description">Description</label>
                                <textarea id="description" v-model="form.description" class="rounded w-full border border-gray-300" />
                                <div v-if="errors.description" class="text-red-500">{{ errors.description }}</div>
                            </div>

                            <div class="mt-4">
                                <label for="due_date">Due date</label>
                                <Datepicker v-model="form.due_date" class="rounded w-full border border-gray-300" format="yyyy-MM-dd HH:mm" previewFormat="yyyy-MM-dd HH:mm"/>
                                <div v-if="errors.due_date" class="text-red-500">{{ errors.due_date }}</div>
                            </div>

                            <div class="mt-4">
                                <label for="category_id">Category</label>
                                <select name="category_id" id="category_id" class="rounded w-full border border-gray-300" v-model="form.category_id">
                                    <option value=""></option>
                                    <option :value="category.id" v-for="category in categories" :selected="category.id == toDo.category_id">{{category.title}}</option>
                                </select>
                                <div v-if="errors.category_id" class="text-red-500">{{ errors.category_id }}</div>
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
import { reactive, ref } from 'vue'
import BreezeAuthenticatedLayout from '@/Layouts/Authenticated.vue';
import { Head } from '@inertiajs/inertia-vue3';
import {Inertia} from "@inertiajs/inertia";
import Datepicker from "vue3-date-time-picker";
import "vue3-date-time-picker/dist/main.css";
import { format } from "date-fns";

const props = defineProps({
    toDo: Object,
    categories: Array,
    errors: Object,
})

const form = reactive({
    title: props.toDo.title,
    description: props.toDo.description,
    due_date: props.toDo.due_date ? ref(new Date(props.toDo.due_date)) : ref(null),
    category_id: props.toDo.category_id,
    tags: props.toDo.tags.map(tag => tag.name),
})

function submit() {
    form.due_date = form.due_date ? format(form.due_date, 'yyyy-MM-dd HH:mm:ss') : null
    Inertia.put(route('to-dos.update', props.toDo), form, {
        onError: (errors) => {
            form.due_date = form.due_date ? ref(new Date(form.due_date)) : ref(null)
        },
    })
}

function addTag() {
    form.tags.push('')
}

function removeTag(index) {
    form.tags.splice(index, 1)
}

</script>

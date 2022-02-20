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
                <div class="bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200 h-auto">
                        <div class="flex justify-between">
                            <div>
                                <p class="font-semibold">Filters</p>
                                <div class="flex flex-wrap space-x-1 mt-2">
                                    <div>
                                        <p>Category</p>
                                        <select name="" id="" class="rounded" v-model="queryFilters.category_id" @change="filter">
                                            <option value=""></option>
                                            <option v-for="category in categories" :key="category.id" :value="category.id">{{ category.title }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <p>Tag</p>
                                        <select name="" id="" class="rounded" v-model="queryFilters.tag_id" @change="filter">
                                            <option value=""></option>
                                            <option v-for="tag in tags" :key="tag.id" :value="tag.id">{{ tag.name }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <p>Status</p>
                                        <select name="" id="" class="rounded" v-model="queryFilters.finished" @change="filter">
                                            <option value=""></option>
                                            <option value="0">Active</option>
                                            <option value="1">Finished</option>
                                        </select>
                                    </div>
                                    <div>
                                        <p>Start date</p>
                                        <datepicker v-model="start_date" class="rounded" inputFormat="yyyy-MM-dd" clearable />
                                    </div>
                                    <div>
                                        <p>End date</p>
                                        <datepicker v-model="end_date" class="rounded" inputFormat="yyyy-MM-dd" clearable />
                                    </div>
                                </div>
                            </div>

                            <div>
                                <Link :href="route('to-dos.create')" class="rounded border border-gray-300 py-2 px-4 ml-2">Create</Link>
                            </div>
                        </div>

                        <div v-if="to_dos.length <= 0">
                            <p>There are no to dos</p>
                        </div>
                        <div v-else v-for="to_do in to_dos" :key="to_do.id">
                            <div class="flex justify-between items-center my-2 bg-gray-200 rounded p-2" v-bind:class="{ 'opacity-50': to_do.finished }">
                                <div class="flex items-center">
                                    <input type="checkbox" class="mr-2 border-transparent focus:border-transparent focus:ring-0 focus:outline-hidden" :checked="to_do.finished" @change="toggleFinished(to_do)">
                                    <div>
                                        <p class="font-bold">{{to_do.title}}</p>
                                        <p>{{to_do.description}}</p>

                                        <div class="flex justify-start space-x-8">
                                            <div class="flex justify-start items-center" v-if="to_do.due_date">
                                                <p class="text-xs font-semibold">Due date: </p>
                                                <p  class="text-xs ml-2">{{to_do.due_date}}</p>
                                            </div>
                                            <div class="flex justify-start items-center" v-if="to_do.category">
                                                <p class="text-xs font-semibold">Category: </p>
                                                <button  class="text-xs ml-2 underline" @click="filterByCategory(to_do.category)">{{to_do.category.title}}</button>
                                            </div>
                                            <div class="flex justify-start items-center" v-if="to_do.tags.length > 0">
                                                <p class="text-xs font-semibold">Tags: </p>
                                                <button v-for="tag in to_do.tags" :key="tag.id" class="text-sm ml-2 underline" @click="filterByTag(tag)">{{tag.name}}</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex 1">
                                    <Link :href="route('to-dos.edit', to_do)" class="rounded border border-gray-300 py-2 px-4 ml-2 bg-white">Edit</Link>
                                    <form @submit.prevent="deleteToDo(to_do)">
                                        <button type="submit" class="rounded border border-gray-300 py-2 px-4 ml-2 bg-white ">Delete</button>
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
import { ref, watch } from 'vue'
import Datepicker from 'vue3-datepicker'
import { format } from 'date-fns'

const props = defineProps({
    to_dos: Array,
    categories: Array,
    tags: Array,
    filters: Array
})

const queryFilters = {
    category_id: props.filters.category_id,
    tag_id: props.filters.tag_id,
    finished: props.filters.finished,
}

const start_date = props.filters.start_date ? ref(new Date(props.filters.start_date)) : ref(null)
const end_date = props.filters.end_date ? ref(new Date(props.filters.end_date)) : ref(null)

function deleteToDo(toDo) {
    if(confirm('Do you really want to delete this to do?')) {
        Inertia.delete(route('to-dos.destroy', toDo))
    }
}

function toggleFinished(toDo) {
    Inertia.put(route('to-dos.toggle', toDo), clean(queryFilters),{ preserveScroll: true })
}

function filter() {
    if (start_date && start_date.value) {
        queryFilters.start_date = format(start_date.value, 'yyyy-MM-dd')
    }
    if (end_date && end_date.value) {
        queryFilters.end_date = format(end_date.value, 'yyyy-MM-dd')
    }
    Inertia.get(route('to-dos.index', clean(queryFilters)))
}

function filterByTag(tag) {
    queryFilters.tag_id = tag.id
    filter()
}

function filterByCategory(category) {
    queryFilters.category_id = category.id
    filter()
}

function clean(obj) {
    for (var propName in obj) {
        if (obj[propName] === null || obj[propName] === undefined || obj[propName] === '') {
            delete obj[propName];
        }
    }
    return obj
}

watch(start_date, async (newDate, oldDate) => {
    filter(clean(queryFilters))
})

watch(end_date, async (newDate, oldDate) => {
    filter(clean(queryFilters))
})


</script>

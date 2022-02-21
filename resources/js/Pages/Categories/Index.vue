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
                    <div class="p-6 bg-white border-b border-gray-200 h-auto">

                        <div v-if="$page.props.flash.success" class="mb-8 flex items-center justify-between bg-emerald-500 rounded w-full">
                            <div class="flex items-center">
                                <svg class="ml-4 mr-2 shrink-0 w-4 h-4" fill="white" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"><polygon points="0 11 2 9 7 14 18 3 20 5 7 18" /></svg>
                                <div class="py-4 text-white text-sm font-medium">{{ $page.props.flash.success }}</div>
                            </div>
                        </div>

                        <div class="flex justify-between">
                            <div class="w-full">
                                <p class="font-semibold">Filters</p>
                                <div class="flex flex-wrap space-x-1 mt-2 w-full">
                                    <div class="flex-row w-full">
                                        <p>Tag</p>
                                        <select name="" id="" class="rounded w-3/12" v-model="queryFilters.tag_id" @change="filter">
                                            <option value=""></option>
                                            <option v-for="tag in tags" :key="tag.id" :value="tag.id">{{ tag.name }}</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <Link :href="route('categories.create')" class="rounded border border-gray-300 py-2 px-4 ml-2">Create</Link>
                            </div>
                        </div>

                        <div v-if="categories.length <= 0" class="mt-4">
                            <p class="italic">There are no Categories</p>
                        </div>
                        <div v-else v-for="category in categories" :key="category.id">
                            <div class="flex flex-wrap justify-between items-center my-2 bg-gray-200 rounded p-2">
                                <div class="flex-row items-center">
                                    <p class="font-bold">{{category.title}}</p>
                                    <div class="flex flex-wrap justify-start items-center">
                                        <p class="text-xs font-semibold">Tags: </p>
                                        <button v-for="tag in category.tags" :key="tag.id" class="text-sm ml-2 underline" @click="filterByTag(tag)">{{tag.name}}</button>
                                    </div>
                                </div>
                                <div class="flex">
                                    <Link :href="route('categories.edit', category)" class="rounded border border-gray-300 py-2 px-4 ml-2  bg-white">Edit</Link>
                                    <form @submit.prevent="deleteCategory(category)">
                                        <button type="submit" class="rounded border border-gray-300 py-2 px-4 ml-2 bg-white">Delete</button>
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

const props = defineProps({
    categories: Array,
    tags: Array,
    filters: Array
})

const queryFilters = {
    tag_id: props.filters.tag_id,
}

function deleteCategory(category) {
    if(confirm('Do you really want to delete this category?')) {
        Inertia.delete(route('categories.destroy', category))
    }
}

function filter() {
    Inertia.get(route('categories.index', clean(queryFilters)))
}

function filterByTag(tag) {
    queryFilters.tag_id = tag.id
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

</script>

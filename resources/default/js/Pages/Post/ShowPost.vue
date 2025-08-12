<template>
    <AppLayout>
        <AppHead :title="__('Post #:id by :name', { id: post.id, name: post.user.name })" />

        <AppBreadcrumb :items="breadcrumbItems" />

        <div class="px-2 py-4 md:px-10 max-w-screen-2xl mx-auto">
            <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0">
                <div class="flex-grow">
                    <div class="-my-2 overflow-x-auto md:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full md:px-6 lg:px-8">
                            <div class="">
                                <Post :post="post" :comments-section-opened="true" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col space-y-4 flex-none md:w-1/4 h-screen md:sticky top-5">
                    <ServerStatusBox />
                    <ShoutBox />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import Post from '@/Components/Post.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { useTranslations } from '@/Composables/useTranslations';

const { __ } = useTranslations();

const props = defineProps({
    post: {
        type: Object,
        required: true
    }
});

const breadcrumbItems = [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Posts'),
        url: route('post.index'),
        current: false
    },
    {
        text: '#' + props.post.id,
        current: true
    }
];
</script>

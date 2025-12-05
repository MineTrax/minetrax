<template>
    <AppLayout>
        <AppHead :title="__(':title - News', { title: news.title })" />

        <!-- Breadcrumbs -->
        <AppBreadcrumb :items="breadcrumbItems" />

        <div class="py-4 px-2 md:px-10 max-w-screen-2xl mx-auto">
            <div class="flex flex-col md:flex-row md:space-x-4">
                <div class="md:w-9/12 flex-1">
                    <ShowNewsCard :news="news" />
                </div>

                <div class="hidden md:flex flex-col md:w-3/12 flex-none space-y-4 h-screen sticky"
                    :class="{ 'top-16': isStickyNav, 'top-5': !isStickyNav }">
                    <ServerStatusBox />
                    <NewsBox :newslist="newslist" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import NewsBox from '@/Shared/NewsBox.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import ShowNewsCard from '@/Shared/ShowNewsCard.vue';
import { usePage } from '@inertiajs/vue3';
import { truncate } from 'lodash';
import { useTranslations } from '@/Composables/useTranslations';

// Define props
const props = defineProps({
    news: Object,
    newslist: Array
});

// Composition API setup
const page = usePage();
const isStickyNav = page.props.generalSettings.enable_sticky_header_menu;
const { __ } = useTranslations();

// Breadcrumb data stored in JSON format
const breadcrumbItems = computed(() => [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('News'),
        url: route('news.index'),
        current: false
    },
    {
        text: truncate(props.news.title, { length: 20 }),
        current: true
    }
]);
</script>

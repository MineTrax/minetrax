<script setup>
import ServerStatusBox from "@/Shared/ServerStatusBox.vue";
import AppLayout from "@/Layouts/AppLayout.vue";
import { usePage } from "@inertiajs/vue3";
import AppHead from "@/Components/AppHead.vue";
import { useTranslations } from "@/Composables/useTranslations";
import { useHelpers } from "@/Composables/useHelpers";
import AppBreadcrumb from "@/Shared/AppBreadcrumb.vue";
import { truncate } from "lodash";

const { __ } = useTranslations();
const { formatToDayDateString } = useHelpers();
const page = usePage();
const isStickyNav = page.props.generalSettings.enable_sticky_header_menu;

const props = defineProps({
    download: {
        type: Object,
        required: true,
    },
});

const breadcrumbItems = [
    {
        text: __("Home"),
        url: route("home"),
        current: false,
    },
    {
        text: __("Downloads"),
        url: route("download.index"),
        current: false,
    },
    {
        text: truncate(props.download.name, { length: 50 }),
        current: true,
    },
];
</script>

<template>
    <AppLayout>
        <AppHead :title="__(':title - Downloads', { title: download.name })" />

        <AppBreadcrumb :items="breadcrumbItems" />

        <div class="py-4 px-2 md:px-10 max-w-screen-2xl mx-auto">
            <div class="flex flex-col md:flex-row md:space-x-4">
                <div class="md:w-9/12 flex-1">
                    <div class="bg-card text-card-foreground border rounded-lg shadow p-4 md:p-6">
                        <h1 class="font-bold text-4xl text-foreground mb-5">
                            {{ download.name }}
                        </h1>

                        <div class="mb-4">
                            <p class="text-muted-foreground text-sm focus:outline-none">
                                {{ __("Created:") }} {{ formatToDayDateString(download.created_at) }}
                                ▪
                                {{ __("Total Downloads: ") }} {{ download.download_count }}
                            </p>
                        </div>

                        <div class="prose dark:prose-invert max-w-none" v-html="download.description_html" />

                        <div id="download-button-section" class="flex justify-center items-center mb-5 mt-6">
                            <a target="_blank" :href="route('download.download', download.slug)" class="relative inline-block px-4 py-2 md:px-8 md:py-4 font-medium group">
                                <span class="absolute inset-0 w-full h-full transition duration-200 ease-out transform translate-x-1 translate-y-1 bg-primary group-hover:-translate-x-0 group-hover:-translate-y-0" />
                                <span class="absolute inset-0 w-full h-full bg-card border-2 border-border group-hover:bg-primary" />
                                <span class="relative text-card-foreground group-hover:text-primary-foreground">{{ __("Download Now") }}</span>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="hidden md:flex flex-col md:w-3/12 flex-none space-y-4 h-screen sticky" :class="{ 'top-16': isStickyNav, 'top-5': !isStickyNav }">
                    <ServerStatusBox />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

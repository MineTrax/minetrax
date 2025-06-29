<template>
    <AppLayout>
        <AppHead :title="customPage.title" />

        <div v-if="!customPage.is_visible" class="py-4 px-2 md:px-10 max-w-screen-2xl mx-auto">
            <AlertCard variant="warning" class="mb-4">
                {{ __("Page is hidden!") }}
                <template #body>
                    {{ __("This page is not visible to public.") }}
                </template>
            </AlertCard>
        </div>

        <!-- If HTML Page -->
        <div v-if="customPage.is_html_page" v-html="customPage.body_html" />

        <!-- If not HTML Page -->
        <div v-else class="py-4 px-2 md:py-6 md:px-10 max-w-screen-2xl mx-auto">
            <div class="flex flex-col md:flex-row md:space-x-4">
                <div :class="customPage.is_sidebar_visible
                        ? 'md:w-9/12'
                        : 'md:w-full'
                    ">
                    <Card class="transition-shadow duration-300 hover:shadow-lg">
                        <CardHeader>
                            <CardTitle class="text-2xl md:text-4xl">
                                {{ customPage.title }}
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="p-6 sm:p-8 pt-0">
                            <div class="prose max-w-none text-card-foreground/90 prose-headings:text-card-foreground prose-p:text-card-foreground/90 prose-strong:text-card-foreground prose-a:text-primary prose-a:no-underline hover:prose-a:underline prose-blockquote:border-primary/30 prose-blockquote:text-card-foreground/70 prose-code:text-primary prose-code:bg-muted prose-code:rounded prose-code:px-1 prose-pre:bg-muted prose-img:rounded-lg"
                                v-html="customPage.body_html" />
                        </CardContent>
                    </Card>
                </div>

                <div v-if="customPage.is_sidebar_visible"
                    class="hidden md:flex flex-col md:w-3/12 flex-none space-y-4 h-screen sticky"
                    :class="{ 'top-16': isStickyNav, 'top-5': !isStickyNav }">
                    <server-status-box />
                    <shout-box />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import ServerStatusBox from "@/Shared/ServerStatusBox.vue";
import ShoutBox from "@/Shared/ShoutBox.vue";
import AlertCard from "@/Components/AlertCard.vue";
import { Card, CardContent, CardHeader, CardTitle } from "@/Components/ui/card";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const isStickyNav = page.props.generalSettings.enable_sticky_header_menu;

const props = defineProps({
    customPage: {
        type: Object,
        required: true,
    },
});
</script>

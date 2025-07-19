<template>
    <AppLayout>
        <AppHead :title="__('Your Notifications')" />

        <div class="py-4 px-2 md:py-12 md:px-10 max-w-screen-2xl mx-auto">
            <div class="flex space-x-4 mb-4 justify-between md:justify-normal">
                <h1 class="font-bold text-3xl text-foreground">
                    {{ __("Notifications") }}
                </h1>
                <div class="flex">
                    <Link :href="route('notification.mark-as-read')" method="post" :preserve-state="false">
                    <Button variant="ghost">
                        <CheckCheck class="w-4 h-4" />
                        {{ __("Mark all read") }}
                    </Button>
                    </Link>
                </div>
            </div>
            <div class="flex flex-col md:flex-row md:space-x-4">
                <div class="flex flex-col space-y-4 md:w-9/12">
                    <Card>
                        <CardContent class="p-0">
                            <InfiniteScroll :load-more="loadNotifications"
                                :can-load-more="notifications.next_page_url !== null">
                                <TransitionGroup name="list" tag="div" class="space-y-1">
                                    <Notification v-for="notification in notifications.data" :key="notification.id"
                                        :notification="notification" />
                                </TransitionGroup>
                            </InfiniteScroll>
                            <div v-if="notifications.data.length <= 0" :key="999999999"
                                class="flex items-center justify-center italic text-muted-foreground p-8">
                                {{ __("No notifications to show.") }}
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <div class="hidden md:flex flex-col md:w-3/12 flex-none space-y-4 h-screen sticky"
                    :class="{ 'top-16': isStickyNav, 'top-5': !isStickyNav }">
                    <server-status-box />
                    <shout-box />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import InfiniteScroll from '@/Components/InfiniteScroll.vue';
import Notification from '@/Components/Notification.vue';
import { Link } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import {
    Card,
    CardContent,
} from '@/Components/ui/card';
import { Button } from '@/Components/ui/button';
import AppHead from '@/Components/AppHead.vue';
import { CheckCheck } from 'lucide-vue-next';

const props = defineProps({
    notificationsList: Object,
});

const page = usePage();

const isStickyNav = page.props.generalSettings.enable_sticky_header_menu;

const notifications = ref(props.notificationsList);
const loading = ref(false);
const error = ref(null);

const loadNotifications = () => {
    if (!notifications.value.next_page_url) {
        return Promise.resolve();
    }

    loading.value = true;
    return axios(notifications.value.next_page_url).then(response => {
        notifications.value = {
            ...response.data,
            data: [...notifications.value.data, ...response.data.data]
        };
    }).finally(() => {
        loading.value = false;
    });
};
</script>

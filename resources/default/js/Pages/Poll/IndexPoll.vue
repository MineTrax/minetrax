<template>
    <app-layout>
        <AppHead :title="__('Polls')" />

        <AppBreadcrumb :items="breadcrumbItems" />

        <div class="px-2 py-4 md:px-10 max-w-screen-2xl mx-auto">
            <div class="flex flex-col md:flex-row md:space-x-4">
                <div class="hidden md:flex flex-col space-y-4 flex-none w-1/4 h-screen sticky top-5">
                    <DidYouKnowBox :enabled="$page.props.generalSettings.enable_didyouknowbox" />
                    <DiscordServerBox :enabled="$page.props.generalSettings.enable_discordbox"
                        :server="$page.props.generalSettings.discord_server_id"
                        :invite="$page.props.generalSettings.discord_invite_url" />
                </div>

                <div class="flex-grow">
                    <div class="-my-2 overflow-x-auto md:-mx-6 lg:-mx-8">
                        <div class="py-2 align-middle inline-block min-w-full md:px-6 lg:px-8">
                            <div class="">
                                <InfiniteScroll :load-more="loadMorePolls"
                                    :can-load-more="pollList.next_page_url !== null">
                                    <transition-group name="list" tag="div" class="space-y-4">
                                        <poll-box v-for="poll in pollList.data" :key="poll.id" :is-listing="true"
                                            :poll="poll.json_for_vue" />
                                    </transition-group>
                                </InfiniteScroll>

                                <Card v-if="pollList.data <= 0"
                                    class="p-3 italic text-center text-sm text-muted-foreground font-semibold">
                                    {{ __("No Polls Found") }}
                                </Card>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="hidden md:flex flex-col space-y-4 flex-none w-1/4 h-screen sticky top-5">
                    <ServerStatusBox />
                    <ShoutBox />
                </div>
            </div>
        </div>
    </app-layout>
</template>

<script setup>
import { ref } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import InfiniteScroll from '@/Components/InfiniteScroll.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import PollBox from '@/Shared/PollBox.vue';
import DidYouKnowBox from '@/Shared/DidYouKnowBox.vue';
import DiscordServerBox from '@/Shared/DiscordServerBox.vue';
import Card from '@/Components/ui/card/Card.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { useTranslations } from '@/Composables/useTranslations';

const { __ } = useTranslations();

// Props
const props = defineProps({
    polls: Object,
});

const breadcrumbItems = [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Polls'),
        current: true
    }
];

// Reactive data
const pollList = ref(props.polls);

// Methods
const loadMorePolls = () => {
    if (!pollList.value.next_page_url) {
        return Promise.resolve();
    }

    return axios(pollList.value.next_page_url).then(response => {
        pollList.value = {
            ...response.data,
            data: [...pollList.value.data, ...response.data.data]
        };
    });
};
</script>

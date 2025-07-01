<template>
    <AppLayout>
        <AppHead :title="__('Players')" />

        <div class="py-4 px-2 md:py-12 md:px-10 max-w-screen-2xl mx-auto">
            <div class="flex-grow">
                <div class="grid grid-cols-12 gap-4 mb-4">
                    <Deferred data="stats">
                        <template #fallback>
                            <!-- Loading Skeleton Stats -->
                        <div class="col-span-12 md:col-span-4">
                            <Card>
                                <CardContent class="p-4">
                                    <div class="flex flex-row">
                                        <div
                                            class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-primary/10 text-primary">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col flex-grow ml-4">
                                            <div class="text-sm text-muted-foreground">
                                                {{ __("Total Players") }}
                                            </div>
                                            <Skeleton class="h-7 w-20 mt-1" />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                        <div class="col-span-12 md:col-span-4">
                            <Card>
                                <CardContent class="p-4">
                                    <div class="flex flex-row">
                                        <div
                                            class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-green-500/10 text-green-500">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col flex-grow ml-4">
                                            <div class="text-sm text-muted-foreground">
                                                {{ __("Active Players") }}
                                            </div>
                                            <Skeleton class="h-7 w-16 mt-1" />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                        <div class="col-span-12 md:col-span-4">
                            <Card>
                                <CardContent class="p-4">
                                    <div class="flex flex-row">
                                        <div
                                            class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-pink-500/10 text-pink-500">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col flex-grow ml-4">
                                            <div class="text-sm text-muted-foreground">
                                                {{ __("Play Time") }}
                                            </div>
                                            <Skeleton class="h-7 w-24 mt-1" />
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                        </template>

                        <!-- Actual Stats -->
                        <div class="col-span-12 md:col-span-4">
                            <Card>
                                <CardContent class="p-4">
                                    <div class="flex flex-row">
                                        <div
                                            class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-primary/10 text-primary">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col flex-grow ml-4">
                                            <div class="text-sm text-muted-foreground">
                                                {{ __("Total Players") }}
                                            </div>
                                            <div class="font-bold text-lg text-card-foreground">
                                                {{ stats.totalPlayersCount }}
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                        <div class="col-span-12 md:col-span-4">
                            <Card>
                                <CardContent class="p-4">
                                    <div class="flex flex-row">
                                        <div
                                            class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-green-500/10 text-green-500">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col flex-grow ml-4">
                                            <div class="text-sm text-muted-foreground">
                                                {{ __("Active Players") }}
                                            </div>
                                            <div class="font-bold text-lg text-card-foreground">
                                                {{ stats.activePlayersCount }}
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                        <div class="col-span-12 md:col-span-4">
                            <Card>
                                <CardContent class="p-4">
                                    <div class="flex flex-row">
                                        <div
                                            class="flex items-center justify-center flex-shrink-0 h-12 w-12 rounded-xl bg-pink-500/10 text-pink-500">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                xmlns="http://www.w3.org/2000/svg">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="flex flex-col flex-grow ml-4">
                                            <div class="text-sm text-muted-foreground">
                                                {{ __("Play Time") }}
                                            </div>
                                            <div class="font-bold text-lg text-card-foreground">
                                                {{
                                                    stats.totalPlayTime === 0
                                                        ? "0 h"
                                                        : secondsToHMS(
                                                            stats.totalPlayTime
                                                        )
                                                }}
                                            </div>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        </div>
                    </Deferred>
                </div>

                <Card>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-border">
                                <thead class="bg-muted/50">
                                    <tr>
                                        <th scope="col"
                                            class="px-2 py-3 text-xs font-bold text-center uppercase tracking-wider text-muted-foreground">
                                            {{ __("#") }}
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                            {{ __("Flag") }}
                                        </th>
                                        <th scope="col"
                                            class="px-3 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                            {{ __("Rank") }}
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                            {{ __("Name") }}
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                            {{ __("Rating") }}
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                            {{ __("Score") }}
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                            {{ __("Time Played") }}
                                        </th>
                                        <th scope="col"
                                            class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider text-muted-foreground">
                                            {{ __("Last Seen") }}
                                        </th>
                                    </tr>
                                </thead>

                                <tbody class="bg-card divide-y divide-border">
                                    <InfiniteScroll :load-more="loadMorePlayers" :can-load-more="playersList.next_page_url !== null
                                        ">
                                        <tr v-for="(
player, index
                                            ) in playersList.data" :key="player.uuid" :class="{
                                                'bg-muted/20': index % 2 === 1,
                                            }">
                                            <td
                                                class="px-2 py-4 whitespace-nowrap text-center text-sm text-primary font-extrabold">
                                                <span v-if="player.position"
                                                    class="border-2 rounded text-lg px-2 border-primary bg-primary text-primary-foreground">
                                                    {{ player.position }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div v-tippy class="flex-shrink-0 h-10 w-10 focus:outline-none"
                                                        :content="player.country.name
                                                            ">
                                                        <img class="h-10 w-10" :src="player.country
                                                                .photo_path
                                                            " alt="" />
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-4 whitespace-nowrap">
                                                <div v-if="player.rank" class="flex items-center">
                                                    <div v-tippy class="flex-shrink-0 focus:outline-none" :content="player.rank.name
                                                        ">
                                                        <img class="max-h-12 max-w-12" :src="player.rank
                                                                .photo_url
                                                            " alt="" />
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10">
                                                        <img class="h-10 w-10" :src="player.avatar_url
                                                            " alt="" />
                                                    </div>
                                                    <div class="ml-4">
                                                        <inertia-link v-tippy as="a" :href="route(
                                                            'player.show',
                                                            player.uuid
                                                        )
                                                            "
                                                            class="text-sm font-medium text-card-foreground focus:outline-none cursor-pointer hover:underline"
                                                            :content="player.uuid
                                                                ">
                                                            <span v-if="
                                                                player.username
                                                            " :class="player.is_active
                                                                        ? 'text-card-foreground'
                                                                        : 'text-muted-foreground'
                                                                    " class="font-extrabold">{{
                                                                    player.username
                                                                }}</span>
                                                            <span v-else class="text-destructive italic">{{
                                                                __(
                                                                    "Unknown"
                                                                )
                                                            }}</span>
                                                        </inertia-link>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                                                <span v-if="player.rating != null">
                                                    <icon v-tippy class="w-10 h-10 focus:outline-none"
                                                        :name="`rating-${player.rating}`" :content="player.rating" />
                                                </span>
                                                <span v-else class="text-muted-foreground italic">{{ __("none")
                                                    }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                                                {{ player.total_score }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                                                {{
                                                    secondsToHMS(
                                                        player.play_time,
                                                        true
                                                    )
                                                }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-card-foreground">
                                                <span v-tippy class="focus:outline-none" :content="formatToDayDateString(
                                                    player.last_seen_at
                                                )
                                                    ">
                                                    {{
                                                        player.last_seen_at
                                                            ? formatTimeAgoToNow(
                                                                player.last_seen_at
                                                            )
                                                            : "unknown"
                                                    }}
                                                </span>
                                            </td>
                                        </tr>

                                        <template #loading>
                                            <tr>
                                                <td class="border-t px-6 py-4 sm:text-center text-muted-foreground italic"
                                                    colspan="8">
                                                    <span class="flex items-center justify-center gap-2">
                                                        <Loader2Icon class="w-4 h-4 animate-spin" />
                                                        {{ __("Loading more...") }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                    </InfiniteScroll>

                                    <tr v-if="playersList.data.length === 0">
                                        <td class="px-6 py-4 text-center text-muted-foreground" colspan="8">
                                            {{ __("No players found.") }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from "vue";
import { Deferred } from '@inertiajs/vue3';
import AppLayout from "@/Layouts/AppLayout.vue";
import Icon from "@/Components/Icon.vue";
import InfiniteScroll from "@/Components/InfiniteScroll.vue";
import { Card, CardContent } from "@/Components/ui/card";
import { Skeleton } from "@/Components/ui/skeleton";
import { useHelpers } from "@/Composables/useHelpers";
import { Loader2Icon } from "lucide-vue-next";

// Define props
const props = defineProps({
    players: Object,
    stats: Object,
});

// Use composables
const { formatTimeAgoToNow, formatToDayDateString, secondsToHMS } =
    useHelpers();

// Reactive data
const playersList = ref(props.players);

// Methods
const loadMorePlayers = () => {
    if (!playersList.value.next_page_url) {
        return;
    }

    return axios(playersList.value.next_page_url).then((response) => {
        playersList.value = {
            ...response.data,
            data: [...playersList.value.data, ...response.data.data],
        };
    });
};
</script>

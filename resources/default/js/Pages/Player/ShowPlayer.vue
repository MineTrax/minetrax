<template>
  <app-layout>
    <app-head :title="__(':username - Player Details', {username: player.username})" />


    <AppBreadcrumb :items="breadcrumbItems" />

    <div class="px-2 py-4 md:px-10 max-w-screen-2xl mx-auto space-y-4">
      <PlayerSubMenu
        :player="player"
        :can-show-player-intel="canShowPlayerIntel"
        :can-change-player-skin="canChangePlayerSkin"
        :can-change-player-password="canChangePlayerPassword"
      />

      <div class="space-y-4">
        <Card>
          <CardContent class="p-6">
            <div class="grid grid-cols-2 gap-4 md:grid-cols-4 md:divide-x divide-border">
              <!-- Position -->
              <div class="flex flex-col items-center justify-center px-4 py-2 md:py-0">
                 <div class="text-primary font-extrabold text-2xl">
                    <span v-if="player.position">#{{ player.position }}</span>
                    <span v-else class="text-muted-foreground text-base font-normal italic">{{ __("None") }}</span>
                 </div>
                 <span class="text-sm font-bold text-muted-foreground mt-1">{{ __("Position") }}</span>
              </div>

              <!-- Rating -->
              <div class="flex flex-col items-center justify-center px-4 py-2 md:py-0">
                 <icon
                    v-if="player.rating != null"
                    v-tippy
                    :name="`rating-${player.rating}`"
                    :title="__('Rating: :rating',{rating: player.rating})"
                    class="w-10 h-10 focus:outline-none"
                 />
                 <span v-else class="italic text-muted-foreground">{{ __("None") }}</span>
                 <span class="text-sm font-bold text-muted-foreground mt-1">{{ __("Rating") }}</span>
              </div>

              <!-- Rank -->
              <div class="flex flex-col items-center justify-center px-4 py-2 md:py-0">
                 <img
                    v-if="player.rank && player.rank.photo_url"
                    v-tippy
                    :alt="player.rank.name"
                    :src="player.rank.photo_url"
                    :title="__('Rank: :rank', {rank: player.rank.name})"
                    class="h-10 focus:outline-none"
                 >
                 <span v-else class="italic text-muted-foreground">{{ __("None") }}</span>
                 <span class="text-sm font-bold text-muted-foreground mt-1">{{ __("Rank") }}</span>
              </div>

              <!-- Country -->
              <div class="flex flex-col items-center justify-center px-4 py-2 md:py-0">
                 <img
                    v-if="player.country && player.country.name"
                    v-tippy
                    :alt="player.country.name"
                    :src="player.country.photo_path"
                    :title="__('Country: :country', {country: player.country.name})"
                    class="h-10 w-10 focus:outline-none"
                 >
                 <span v-else class="italic text-muted-foreground">{{ __("Unknown") }}</span>
                 <span class="text-sm font-bold text-muted-foreground mt-1">{{ __("Country") }}</span>
              </div>
            </div>
          </CardContent>
        </Card>

        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <!-- Skin Viewer -->
            <Card class="flex flex-col items-center justify-center md:col-span-1 relative bg-card min-h-[500px] order-last md:order-first">
                 <button
                    class="focus:outline-none absolute top-4 left-4 z-10 bg-background/50 p-1 rounded-full hover:bg-background/80 transition"
                    @click="toggle3dPlayerAnimation"
                  >
                    <icon
                      v-if="playerAnimationEnabled"
                      class="w-6 h-6 text-red-500"
                      name="pause"
                    />
                    <icon
                      v-else
                      class="w-6 h-6 text-green-500"
                      name="play"
                    />
                  </button>
                  <canvas id="skin_container" class="rounded-lg w-full h-full object-contain" />
            </Card>

            <!-- Main Stats -->
            <Card class="md:col-span-3">
                <CardHeader>
                    <div class="flex justify-between items-start">
                        <div>
                            <CardTitle class="text-3xl font-extrabold">{{ player.username }}</CardTitle>
                            <p class="text-muted-foreground text-sm font-mono mt-1">{{ player.uuid }}</p>
                        </div>
                        <div v-if="!player.is_active" class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded dark:bg-red-900/30 dark:text-red-400">
                            {{ __("Inactive") }}
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                        <!-- Combat Stats -->
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2 border-b pb-2 mb-2">
                                <FireIcon class="w-5 h-5 text-red-500" />
                                <h3 class="font-semibold text-lg">{{ __("Combat") }}</h3>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium text-sm">{{ __("Mob Kills") }}</span>
                                <span class="font-bold">{{ player.total_mob_kills }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium text-sm">{{ __("Player Kills") }}</span>
                                <span class="font-bold">{{ player.total_player_kills }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium text-sm">{{ __("Deaths") }}</span>
                                <span class="font-bold">{{ player.total_deaths }}</span>
                            </div>
                             <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium text-sm">{{ __("Total Score") }}</span>
                                <span class="font-bold">{{ player.total_score }}</span>
                            </div>
                        </div>

                        <!-- General Stats -->
                        <div class="space-y-4">
                            <div class="flex items-center space-x-2 border-b pb-2 mb-2">
                                <ClockIcon class="w-5 h-5 text-blue-500" />
                                <h3 class="font-semibold text-lg">{{ __("General") }}</h3>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium text-sm">{{ __("Play Time") }}</span>
                                <span class="font-bold">{{ secondsToHMS(player.play_time, true) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium text-sm">{{ __("Afk Time") }}</span>
                                <span class="font-bold">{{ secondsToHMS(player.afk_time, true) }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium text-sm">{{ __("Sessions") }}</span>
                                <span class="font-bold">{{ player.total_leave_game }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium text-sm">{{ __("Servers Played") }}</span>
                                <span class="font-bold">{{ player.servers_count }}</span>
                            </div>
                        </div>

                        <!-- Item Stats -->
                        <div class="space-y-4">
                             <div class="flex items-center space-x-2 border-b pb-2 mb-2">
                                <CubeIcon class="w-5 h-5 text-yellow-500" />
                                <h3 class="font-semibold text-lg">{{ __("Items") }}</h3>
                             </div>
                            <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium text-sm">{{ __("Mined") }}</span>
                                <span class="font-bold">{{ player.total_mined }}</span>
                            </div>
                             <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium text-sm">{{ __("Crafted") }}</span>
                                <span class="font-bold">{{ player.total_crafted }}</span>
                            </div>
                             <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium text-sm">{{ __("Picked Up") }}</span>
                                <span class="font-bold">{{ player.total_picked_up }}</span>
                            </div>
                             <div class="flex justify-between items-center">
                                <span class="text-muted-foreground font-medium text-sm">{{ __("Broken") }}</span>
                                <span class="font-bold">{{ player.total_broken }}</span>
                            </div>
                        </div>

                         <!-- Misc & Account -->
                        <div class="space-y-4 md:col-span-2 lg:col-span-3">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                <div class="space-y-4">
                                    <div class="flex items-center space-x-2 border-b pb-2 mb-2">
                                        <ChartBarIcon class="w-5 h-5 text-purple-500" />
                                        <h3 class="font-semibold text-lg">{{ __("Activity") }}</h3>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-muted-foreground font-medium text-sm">{{ __("Distance Walked") }}</span>
                                        <span class="font-bold">{{ millify(player.distance_traveled) }} {{ __("m") }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-muted-foreground font-medium text-sm">{{ __("Times Slept") }}</span>
                                        <span class="font-bold">{{ player.total_sleep_in_bed }}</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-muted-foreground font-medium text-sm">{{ __("Favorite Server") }}</span>
                                        <span v-if="player.favorite_server" class="font-bold" v-tippy :title="player.favorite_server.hostname">{{ player.favorite_server.name }}</span>
                                        <span v-else class="italic text-muted-foreground">{{ __("None") }}</span>
                                    </div>
                                </div>

                                <div class="space-y-4">
                                    <div class="flex items-center space-x-2 border-b pb-2 mb-2">
                                        <UserIcon class="w-5 h-5 text-green-500" />
                                        <h3 class="font-semibold text-lg">{{ __("Account") }}</h3>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-muted-foreground font-medium text-sm">{{ __("Joined") }}</span>
                                        <span v-tippy :content="formatToDayDateString(player.first_seen_at)" class="font-bold cursor-help">
                                            {{ player.first_seen_at ? formatTimeAgoToNow(player.first_seen_at) : "unknown" }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-muted-foreground font-medium text-sm">{{ __("Last Seen") }}</span>
                                        <span v-tippy :content="formatToDayDateString(player.last_seen_at)" class="font-bold cursor-help">
                                            {{ player.last_seen_at ? formatTimeAgoToNow(player.last_seen_at) : "unknown" }}
                                        </span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-muted-foreground font-medium text-sm">{{ __("Claimed By") }}</span>
                                        <div class="font-bold flex items-center space-x-2">
                                             <inertia-link
                                                v-if="player.owner"
                                                class="text-primary hover:underline"
                                                as="a"
                                                :href="route('user.public.get', player.owner.username)"
                                            >
                                                @{{ player.owner.username }}
                                            </inertia-link>
                                            <span v-else class="italic text-muted-foreground">{{ __("None") }}</span>

                                            <InertiaLink
                                                v-if="can('unlink any_players') && player.owner"
                                                v-confirm="{ message: 'Are you sure you want to unlink this player from current user?' }"
                                                v-tippy
                                                as="button"
                                                method="DELETE"
                                                :href="route('admin.player.unlink', player.uuid)"
                                                class="text-red-500 hover:text-red-700 focus:outline-none transition"
                                                :title="__('Unlink Player')"
                                            >
                                                <LockOpenIcon class="w-4 h-4" />
                                            </InertiaLink>
                                        </div>
                                    </div>
                                     <div class="flex justify-between items-center">
                                         <span class="text-muted-foreground font-medium text-sm">{{ __("Next Rank") }}</span>
                                         <span v-if="player.next_rank" class="font-bold">{{ player.next_rank }}</span>
                                         <span v-else class="italic text-muted-foreground">{{ __("None") }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import * as skinview3d from 'skinview3d';
import { useHelpers } from '@/Composables/useHelpers';
import millify from 'millify';
import PlayerSubMenu from '@/Shared/PlayerSubMenu.vue';
import { LockOpenIcon, ClockIcon, CubeIcon, UserIcon, ChartBarIcon, FireIcon } from '@heroicons/vue/24/outline';
import { useTranslations } from '@/Composables/useTranslations';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

const { __ } = useTranslations();
const { can } = useAuthorizable();

const props = defineProps({
    player: Object,
    canShowPlayerIntel: Boolean,
    canChangePlayerSkin: Boolean,
    canChangePlayerPassword: Boolean,
});

const { secondsToHMS, formatTimeAgoToNow, formatToDayDateString } = useHelpers();

const breadcrumbItems = [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Players'),
        url: route('player.index'),
        current: false
    },
    {
        text: props.player.username || props.player.uuid,
        current: true
    }
];

const playerAnimationEnabled = ref(true);
let skinViewer = null;

onMounted(() => {
    skinViewer = new skinview3d.SkinViewer({
        canvas: document.getElementById('skin_container'),
        width: 300,
        height: 500,
        skin: route('player.skin.get', { uuid: props.player.uuid, username: props.player.username, textureid: props.player.skin_texture_id }),
    });
    skinViewer.autoRotate = true;
    skinViewer.animation = new skinview3d.WalkingAnimation();
    skinViewer.animation.speed = 0.1;
    skinViewer.autoRotateSpeed = 0.5;
});

onUnmounted(() => {
    if (skinViewer) {
        skinViewer.dispose();
    }
});

const toggle3dPlayerAnimation = () => {
    if (playerAnimationEnabled.value) {
        // Disable Animation
        skinViewer.animation.paused = true;
        skinViewer.autoRotate = false;
        playerAnimationEnabled.value = false;
    } else {
        // Enable Animation
        skinViewer.animation.paused = false;
        skinViewer.autoRotate = true;
        playerAnimationEnabled.value = true;
    }
};

</script>

<template>
    <AppLayout>
        <AppHead :title="__('Your Linked Players')" />

        <div class="max-w-6xl px-2 py-3 mx-auto space-y-4 md:py-12 md:px-10">
            <Card
                class="border-t-4 relative overflow-hidden transition-all duration-300"
                :class="{
                    'border-primary': linkSlotsLeft > 0 && otpExpiryCountdownSeconds > 0,
                    'border-orange-500 opacity-35': linkSlotsLeft <= 0,
                    'border-destructive': otpExpiryCountdownSeconds <= 0 && linkSlotsLeft > 0,
                }"
            >
                <!-- Expired Overlay -->
                <div v-if="otpExpiryCountdownSeconds <= 0" class="absolute inset-0 bg-destructive/10 backdrop-blur-md z-10 flex items-center justify-center">
                    <div class="rounded-lg p-6 max-w-md mx-4 text-center space-y-4">
                        <div class="flex justify-center">
                            <div class="bg-destructive/10 p-3 rounded-full">
                                <svg class="w-12 h-12 text-destructive" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-destructive mb-2">
                                {{ __("OTP Expired") }}
                            </h3>
                            <p class="text-muted-foreground">
                                {{ __("This One-Time Password has expired. Please refresh the page to generate a new OTP.") }}
                            </p>
                        </div>
                        <Button as-child variant="default" size="lg" class="w-full">
                            <InertiaLink :href="route('linked-player.list')">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                {{ __("Refresh & Get New OTP") }}
                            </InertiaLink>
                        </Button>
                    </div>
                </div>

                <CardContent class="pt-6 transition-all duration-300" :class="{ 'blur-sm': otpExpiryCountdownSeconds <= 0 }">
                    <div class="flex">
                        <div class="py-1">
                            <svg
                                class="w-6 h-6 mr-4 fill-current"
                                :class="{
                                    'text-primary': linkSlotsLeft > 0,
                                    'text-orange-500': linkSlotsLeft <= 0,
                                }"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                            >
                                <path d="M2.93 17.07A10 10 0 1 1 17.07 2.93 10 10 0 0 1 2.93 17.07zm12.73-1.41A8 8 0 1 0 4.34 4.34a8 8 0 0 0 11.32 11.32zM9 11V9h2v6H9v-4zm0-6h2v2H9V5z" />
                            </svg>
                        </div>
                        <div class="w-full">
                            <p class="font-bold" :class="linkSlotsLeft > 0 ? 'text-primary' : 'text-orange-900 dark:text-orange-400'">
                                <span v-if="linkSlotsLeft > 0">
                                    {{
                                        __("You can link upto :count :player to your account! ( :left available )", {
                                            count: maxPlayerPerUser,
                                            player: maxPlayerPerUser === 1 ? __("player") : __("players"),
                                            left: linkSlotsLeft,
                                        })
                                    }}
                                </span>
                                <span v-else>
                                    {{ __("You have already linked maximum number of players to your account!") }}
                                </span>
                            </p>
                            <div class="flex flex-col items-center mt-2 text-sm">
                                <p class="text-muted-foreground">
                                    {{
                                        __("To link a player to your account, join server and type '/link :otp' in your chat.", {
                                            otp: currentLinkOtp?.otp,
                                        })
                                    }}
                                </p>

                                <!-- OTP Display -->
                                <h1 class="font-mono text-6xl font-extrabold tracking-widest text-center text-foreground mt-2">
                                    {{ currentLinkOtp?.otp }}
                                </h1>

                                <CopyToClipboard v-slot="props">
                                    <button
                                        v-tippy
                                        :title="otpExpiryCountdownSeconds <= 0 ? __('OTP Expired') : __('Click to Copy')"
                                        type="button"
                                        :disabled="otpExpiryCountdownSeconds <= 0"
                                        class="p-2 mt-3 font-semibold text-center font-mono tracking-wider transition duration-150 ease-in-out border rounded-md w-full md:w-1/2 focus:ring-2 focus:ring-ring focus:ring-offset-2 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="{
                                            'text-foreground border-border hover:bg-accent hover:text-accent-foreground': otpExpiryCountdownSeconds > 0,
                                            'text-muted-foreground border-muted': otpExpiryCountdownSeconds <= 0,
                                        }"
                                        @click="props.copy('/link ' + currentLinkOtp?.otp)"
                                    >
                                        <span v-if="props.status !== 'copied'">
                                            {{ "/link " + currentLinkOtp?.otp }}
                                        </span>
                                        <span v-else>
                                            {{ __("Copied!") }}
                                        </span>
                                    </button>
                                </CopyToClipboard>

                                <p
                                    v-if="otpExpiryCountdownSeconds > 0"
                                    class="italic text-muted-foreground mt-4"
                                    :class="{
                                        'text-orange-600 dark:text-orange-400': otpExpiryCountdownSeconds <= 60,
                                    }"
                                >
                                    {{
                                        __("This OTP will expire in :seconds seconds.", {
                                            seconds: otpExpiryCountdownSeconds,
                                        })
                                    }}
                                </p>

                                <div class="text-xs font-bold mt-2 text-destructive">
                                    {{ __("CAUTION: NEVER SHARE THIS OTP WITH ANYONE!") }}
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <div class="gap-4 space-y-2 md:grid md:space-y-0" :class="linkedPlayers.length > 1 ? 'grid-cols-2' : 'grid-cols-1 place-items-center'">
                <div v-if="linkedPlayers.length <= 0" class="flex justify-center pt-5 pb-5">
                    <p class="italic text-destructive">
                        {{ __("No players linked to your account right now.") }}
                    </p>
                </div>

                <Card v-for="player in linkedPlayers" :key="player.uuid" class="shadow-sm">
                    <CardContent class="p-4">
                        <div class="flex flex-col items-center">
                            <div class="flex justify-center pr-10">
                                <canvas :id="`skin_container_${player.uuid}`" />
                                <div class="flex flex-col space-y-2">
                                    <div class="username">
                                        <InertiaLink as="a" :href="route('player.show', player.uuid)" class="text-lg font-bold text-primary hover:text-primary/80">
                                            {{ player.username }}
                                        </InertiaLink>
                                        <p class="text-xs text-muted-foreground">
                                            {{ player.uuid }}
                                        </p>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <p class="font-bold text-foreground">{{ __("Position") }}:</p>
                                        <div class="flex items-center space-x-2 text-sm font-extrabold text-center text-primary">
                                            <span v-if="player.position" class="px-2 text-lg border-2 rounded border-primary bg-primary/10">
                                                {{ player.position }}
                                            </span>
                                            <span v-else class="text-sm italic text-muted-foreground">{{ __("None") }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <p class="font-bold text-foreground">{{ __("Rating") }}:</p>
                                        <Icon v-if="player.rating != null" v-tippy class="w-8 h-8 focus:outline-none" :name="`rating-${player.rating}`" :content="player.rating" />
                                        <p v-else class="text-sm italic text-muted-foreground">
                                            {{ __("None") }}
                                        </p>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <p class="font-bold text-foreground">{{ __("Rank") }}:</p>
                                        <div class="flex items-center space-x-2">
                                            <p v-if="player.rank" class="text-foreground">
                                                {{ player.rank.name }}
                                            </p>
                                            <p v-else class="text-sm italic text-muted-foreground">
                                                {{ __("None") }}
                                            </p>
                                            <img
                                                v-if="player.rank && player.rank.photo_url"
                                                v-tippy
                                                :src="player.rank.photo_url"
                                                :alt="player.rank.name"
                                                :title="player.rank.name"
                                                class="max-h-12 max-w-12 focus:outline-none"
                                            />
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <p class="font-bold text-foreground">{{ __("Country") }}:</p>
                                        <div class="flex items-center space-x-2">
                                            <p class="text-foreground">
                                                {{ player.country.name }}
                                            </p>
                                            <img v-tippy :title="player.country.name" :src="player.country.photo_path" :alt="player.country.name" class="h-8 w-8 -mt-0.5 focus:outline-none" />
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between">
                                        <p class="font-bold text-foreground">{{ __("Last Seen") }}:</p>
                                        <div class="flex items-center space-x-2">
                                            <p v-tippy class="focus:outline-none text-foreground" :title="formatToDayDateString(player.last_seen_at)">
                                                {{ formatTimeAgoToNow(player.last_seen_at) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="flex justify-end space-x-2 mt-4 w-full">
                                <Button v-if="pageProps.pluginSettings?.playerPasswordResetEnabled" v-tippy as-child variant="outline" size="sm" :title="__('Change Password of this player.')">
                                    <InertiaLink
                                        :href="
                                            route('reset-player-password.show', {
                                                player_uuid: player.uuid,
                                            })
                                        "
                                    >
                                        <LockClosedIcon class="w-4 h-4" />
                                        <span class="hidden md:inline">
                                            {{ __("Change Password") }}
                                        </span>
                                    </InertiaLink>
                                </Button>

                                <Button v-if="pageProps.playerSkinChangerEnabled" v-tippy as-child variant="default" size="sm" :title="__('Change Skin of this player.')">
                                    <InertiaLink
                                        :href="
                                            route('change-player-skin.show', {
                                                player_uuid: player.uuid,
                                            })
                                        "
                                    >
                                        <PaintBrushIcon class="w-4 h-4" />
                                        <span class="hidden md:inline">
                                            {{ __("Change Skin") }}
                                        </span>
                                    </InertiaLink>
                                </Button>

                                <Button
                                    v-if="!isUnlinkingDisabled"
                                    v-tippy
                                    v-confirm="{ message: __('Are you sure you want to unlink this player from your account?') }"
                                    as-child
                                    variant="destructive"
                                    size="sm"
                                    :title="__('Unlink this player from your account.')"
                                >
                                    <InertiaLink method="delete" :preserve-scroll="true" :preserve-state="false" :href="route('account-link.delete', player.uuid)">
                                        <BookmarkSlashIcon class="w-4 h-4" />
                                        <span class="hidden md:inline">
                                            {{ __("Unlink Player") }}
                                        </span>
                                    </InertiaLink>
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import { useTranslations } from "@/Composables/useTranslations";
import AppLayout from "@/Layouts/AppLayout.vue";
import Icon from "@/Components/Icon.vue";
import * as skinview3d from "skinview3d";
import { useHelpers } from "@/Composables/useHelpers";
import { PaintBrushIcon, BookmarkSlashIcon, LockClosedIcon } from "@heroicons/vue/24/solid";
import CopyToClipboard from "@/Components/CopyToClipboard.vue";
import { Card, CardContent } from "@/Components/ui/card";
import { Button } from "@/Components/ui/button";

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();
const page = usePage();
const pageProps = page.props;

const props = defineProps({
    linkedPlayers: {
        type: Array,
        required: true,
    },
    maxPlayerPerUser: {
        type: Number,
        required: true,
    },
    currentLinkOtp: {
        type: Object,
        required: false,
    },
    isUnlinkingDisabled: {
        type: Boolean,
        default: false,
    },
});

const skinViewers = ref([]);
const otpExpiryCountdownInterval = ref(null);
const otpExpiryCountdownSeconds = ref(props.currentLinkOtp?.expires_at ? Math.floor((new Date(props.currentLinkOtp.expires_at).getTime() - new Date().getTime()) / 1000) : null);

const linkSlotsLeft = computed(() => {
    return props.maxPlayerPerUser - props.linkedPlayers.length;
});

const startOtpExpiryCountdown = () => {
    if (otpExpiryCountdownSeconds.value > 0) {
        otpExpiryCountdownInterval.value = setInterval(() => {
            otpExpiryCountdownSeconds.value--;
            if (otpExpiryCountdownSeconds.value <= 0) {
                clearInterval(otpExpiryCountdownInterval.value);
            }
        }, 1000);
    }
};

onMounted(() => {
    for (const player of props.linkedPlayers) {
        let skinViewer = new skinview3d.SkinViewer({
            canvas: document.getElementById(`skin_container_${player.uuid}`),
            width: 200,
            height: 300,
            skin: route("player.skin.get", {
                uuid: player.uuid,
                username: player.username,
                textureid: player.skin_texture_id,
            }),
        });
        skinViewer.autoRotate = true;
        skinViewer.animation = new skinview3d.WalkingAnimation();
        skinViewer.animation.speed = 0.1;
        skinViewer.autoRotateSpeed = 0.5;
        skinViewers.value.push(skinViewer);
    }

    startOtpExpiryCountdown();
});

onUnmounted(() => {
    for (const skinViewer of skinViewers.value) {
        skinViewer.dispose();
    }

    clearInterval(otpExpiryCountdownInterval.value);
});
</script>

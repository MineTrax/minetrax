<template>
    <AppLayout>
        <AppHead :title="__(':name profile', { name: profileUser.name })" />

        <div class="max-w-7xl px-2 py-3 mx-auto space-y-4 md:py-12 md:px-10">
            <AlertCard variant="destructive" v-if="profileUser.banned_at">
                {{ __("This User is Banned!") }}
                <template #icon>
                    <icon name="ban" class="w-6 h-6 mr-4 text-destructive" />
                </template>
                <template #body>
                    {{ __("If you think it is a mistake.") }}
                    <Link :href="route('staff.index')" class="font-semibold hover:underline">
                        {{ __("Please contact a Staff") }} </Link>.
                </template>
            </AlertCard>

            <AlertCard v-if="
                $page.props.jetstream.hasEmailVerification &&
                profileUser.email_verified_at === null
            " variant="warning">
                {{ __("This user hasn't verified his email yet!") }}
            </AlertCard>

            <Card class="overflow-hidden border-b shadow max-w-none">
                <div>
                    <div class="w-full bg-center bg-no-repeat bg-cover"
                        :style="`height: 300px; background-image: url('${profileUser.cover_image_url}');`">
                        <img class="w-full h-full opacity-0" :src="`${profileUser.cover_image_url}`"
                            alt="Cover Image" />
                    </div>
                    <CardContent class="px-4 py-2">
                        <div class="relative flex w-full">
                            <!-- Avatar -->
                            <div class="flex flex-1">
                                <div style="margin-top: -6rem">
                                    <div style="height: 9rem; width: 9rem" class="relative rounded-full md avatar">
                                        <img style="height: 9rem; width: 9rem"
                                            class="relative transition bg-card border-4 border-card rounded-full md hover:bg-accent"
                                            :src="profileUser.profile_photo_url" alt="" />
                                        <div class="absolute" />
                                    </div>
                                </div>
                            </div>
                            <!-- Follow Button -->
                            <div v-if="$page.props.auth.user" class="flex text-xs text-right md:text-medium">
                                <div class="p-4 space-x-2">
                                    <Link v-if="
                                        profileUser.id ===
                                        $page.props.auth.user.id
                                    " v-tippy :title="__('Update Profile')" :href="route('profile.show')"
                                        class="inline-flex items-center px-2 py-2 text-sm font-medium border-2 rounded-full border-primary text-primary hover:bg-primary hover:text-primary-foreground">
                                        <PencilSquareIcon class="w-5 h-5 stroke-2" />
                                    </Link>
                                    <Link v-if="
                                        can('mute users') &&
                                        !profileUser.muted_at
                                    " v-tippy :title="__('Mute')" method="post" as="button" :href="route(
                                            'admin.user.mute',
                                            profileUser.id
                                        )
                                            "
                                        class="inline-flex items-center px-2 py-2 text-sm font-medium text-warning-500 border-2 border-warning-500 rounded-full hover:bg-warning-500 hover:text-white">
                                        <SpeakerXMarkIcon class="w-5 h-5 stroke-2" />
                                    </Link>
                                    <Link v-if="
                                        can('mute users') &&
                                        profileUser.muted_at
                                    " v-tippy :title="__('Unmute')" method="post" as="button" :href="route(
                                            'admin.user.unmute',
                                            profileUser.id
                                        )
                                            "
                                        class="inline-flex items-center px-2 py-2 text-sm font-medium text-success-500 border-2 border-success-500 rounded-full hover:bg-success-500 hover:text-white">
                                        <SpeakerWaveIcon class="w-5 h-5 stroke-2" />
                                    </Link>
                                    <Link v-if="
                                        can('ban users') &&
                                        !profileUser.banned_at
                                    " v-tippy :title="__('Ban')" method="post" as="button" :href="route(
                                            'admin.user.ban',
                                            profileUser.id
                                        )
                                            "
                                        class="inline-flex items-center px-2 py-2 text-sm font-medium text-destructive border-2 border-destructive rounded-full hover:bg-destructive hover:text-destructive-foreground">
                                        <NoSymbolIcon class="w-5 h-5 stroke-2" />
                                    </Link>
                                    <Link v-if="
                                        can('ban users') &&
                                        profileUser.banned_at
                                    " v-tippy :title="__('Unban')" method="post" as="button" :href="route(
                                            'admin.user.unban',
                                            profileUser.id
                                        )
                                            "
                                        class="inline-flex items-center px-2 py-2 text-sm font-medium text-success-500 border-2 border-success-500 rounded-full hover:bg-success-500 hover:text-white">
                                        <NoSymbolIcon class="w-5 h-5 stroke-2" />
                                    </Link>
                                    <Link v-if="can('update users')" v-tippy :title="__('Edit')" :href="route(
                                        'admin.user.edit',
                                        profileUser.id
                                    )
                                        "
                                        class="inline-flex items-center px-2 py-2 text-sm font-medium text-green-500 border-2 border-green-500 rounded-full hover:bg-green-500 hover:text-white">
                                        <PencilSquareIcon class="w-5 h-5 stroke-2" />
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Profile info -->
                        <div class="justify-center w-full mt-3 ml-3 space-y-2">
                            <!-- User basic-->
                            <div>
                                <UserDisplayname :user="profileUser" icon-class="w-6 h-6" text-class="text-xl" />
                                <p class="font-medium leading-5 text-card-foreground">
                                    @{{ profileUser.username }}
                                </p>
                            </div>
                            <!-- Rank -->
                            <div>
                                <div v-for="role of profileUser.roles" :key="role.id">
                                    <img v-if="role.photo_url" v-tippy :src="role.photo_url" :alt="role.display_name"
                                        :content="role.display_name" class="focus:outline-none max-h-8" />
                                    <div v-else
                                        class="inline-flex mt-2 font-bold uppercase leading-5 p-1.5 bg-primary text-primary-foreground rounded-sm"
                                        :style="`background-color: ${role.color};`">
                                        {{ role.display_name }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex justify-end mr-4">
                                <p v-tippy
                                    class="text-sm font-medium leading-5 text-muted-foreground focus:outline-none"
                                    :title="formatToDayDateString(
                                        profileUser.created_at
                                    )
                                        ">
                                    {{ __("Joined") }}:
                                    {{
                                        formatTimeAgoToNow(
                                            profileUser.created_at
                                        )
                                    }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </div>
            </Card>

            <div class="flex flex-col md:space-x-4 md:flex-row">
                <div class="flex flex-col mb-4 space-y-4 md:mb-0 md:w-1/2">
                    <Card v-if="profileUser.players.length > 0" class="flex flex-col w-full space-y-2">
                        <div v-for="player in profileUser.players" :key="player.uuid"
                            class="flex justify-around p-4 space-x-4 border-b border-border last:border-b-0">
                            <img :src="route('player.render.get', {
                                uuid: player.uuid,
                                username: player.username,
                                textureid: player.skin_texture_id,
                                scale: 4,
                            })
                                " :alt="player.username" />

                            <div class="flex flex-col flex-1 space-y-2">
                                <div class="username">
                                    <Link as="a" :href="route('player.show', player.uuid)
                                        " class="text-lg font-bold text-primary hover:text-primary/80">
                                        {{ player.username }}
                                    </Link>
                                </div>

                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-card-foreground">
                                        {{ __("Position") }}:
                                    </p>
                                    <div
                                        class="flex items-center space-x-2 text-sm font-extrabold text-center text-primary">
                                        <span v-if="player.position"
                                            class="px-2 text-lg border-2 rounded border-primary bg-primary text-primary-foreground">
                                            {{ player.position }}
                                        </span>
                                        <span v-else class="text-sm italic text-muted-foreground">{{ __("None")
                                            }}</span>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-card-foreground">
                                        {{ __("Rating") }}:
                                    </p>
                                    <icon v-if="player.rating != null" v-tippy class="w-8 h-8 focus:outline-none"
                                        :name="`rating-${player.rating}`" :content="player.rating" />
                                    <p v-else class="text-sm italic text-muted-foreground">
                                        {{ __("None") }}
                                    </p>
                                </div>
                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-card-foreground">
                                        {{ __("Rank") }}:
                                    </p>
                                    <div class="flex items-center space-x-2">
                                        <p v-if="player.rank" class="text-card-foreground">
                                            {{ player.rank.name }}
                                        </p>
                                        <p v-else class="text-sm italic text-muted-foreground">
                                            {{ __("None") }}
                                        </p>
                                        <img v-if="
                                            player.rank &&
                                            player.rank.photo_url
                                        " v-tippy :src="player.rank.photo_url" :alt="player.rank.name"
                                            :title="player.rank.name" class="max-h-12 max-w-12 focus:outline-none" />
                                    </div>
                                </div>

                                <div class="flex items-center justify-between">
                                    <p class="font-bold text-card-foreground">
                                        {{ __("Last Seen") }}:
                                    </p>
                                    <div class="flex items-center space-x-2">
                                        <p v-tippy class="focus:outline-none text-card-foreground" :title="formatToDayDateString(
                                            player.last_seen_at
                                        )
                                            ">
                                            {{
                                                formatTimeAgoToNow(
                                                    player.last_seen_at
                                                )
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Card>

                    <Card v-if="
                        profileUser.badges && profileUser.badges.length > 0
                    ">
                        <CardContent class="p-0">
                            <div class="px-4 py-2">
                                <h3 class="font-extrabold text-card-foreground">
                                    {{ __("Badges") }}
                                </h3>
                            </div>
                            <div class="flex flex-row justify-center space-x-2 pb-2">
                                <div v-for="badge in profileUser.badges" :key="badge.id" v-tippy :title="badge.name">
                                    <img class="w-12 h-12" :src="badge.photo_url" :alt="badge.name" />
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <Card v-if="profileUser.about" class="flex flex-col w-full">
                        <CardContent class="p-4">
                            <span class="whitespace-pre-wrap text-card-foreground">{{ profileUser.about }}</span>
                        </CardContent>
                    </Card>

                    <Card class="flex flex-col w-full space-y-2">
                        <CardContent class="p-4 space-y-2">
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">{{
                                    __("Country")
                                    }}</span>
                                <span class="font-semibold text-card-foreground">
                                    {{ profileUser.country.name }}
                                    <img class="inline h-6 mb-1" :src="profileUser.country.photo_path"
                                        :alt="profileUser.country.name" />
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">{{
                                    __("Day of Birth")
                                    }}</span>
                                <span class="font-semibold text-card-foreground">{{
                                    profileUser.dob_string || __("unknown")
                                }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">{{
                                    __("Gender")
                                    }}</span>
                                <span class="font-semibold text-card-foreground">{{
                                    __(profileUser.gender_string) ||
                                    __("unknown")
                                }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-muted-foreground">{{
                                    __("Total Posts")
                                    }}</span>
                                <span class="font-semibold text-card-foreground">{{ profileUser.posts_count }}</span>
                            </div>
                            <div v-if="
                                profileUser.social_links &&
                                profileUser.social_links.s_discord_username
                            " class="flex justify-between">
                                <span class="text-muted-foreground">{{
                                    __("Discord")
                                    }}</span>
                                <span class="font-semibold text-card-foreground">{{
                                    profileUser.social_links
                                        .s_discord_username
                                }}</span>
                            </div>
                        </CardContent>
                    </Card>

                    <Card v-if="can('read users')" class="flex flex-col w-full space-y-2 border-amber-500">
                        <CardContent class="p-4 space-y-2">
                            <h3 class="font-bold text-card-foreground border-b pb-2 mb-2">
                                {{ __("Staff Only") }}
                            </h3>

                            <div class="flex justify-between">
                                <span class="text-muted-foreground">{{ __("Email") }}</span>
                                <div class="text-right">
                                    <span class="font-semibold text-card-foreground block">{{ profileUser.email }}</span>
                                    <span v-if="profileUser.email_verified_at" class="text-green-500 text-xs">
                                        ({{ __("Verified") }} {{ formatTimeAgoToNow(profileUser.email_verified_at) }})
                                    </span>
                                    <span v-else class="text-destructive text-xs">({{ __("Unverified") }})</span>
                                </div>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-muted-foreground">{{ __("Last Login") }}</span>
                                <span v-if="profileUser.last_login_at" class="font-semibold text-card-foreground" :title="formatToDayDateString(profileUser.last_login_at)">
                                    {{ formatTimeAgoToNow(profileUser.last_login_at) }}
                                </span>
                                <span v-else class="text-sm italic text-muted-foreground">{{ __("Never") }}</span>
                            </div>

                            <div class="flex justify-between">
                                <span class="text-muted-foreground">{{ __("Discord ID") }}</span>
                                <span class="font-semibold text-card-foreground">{{ profileUser.discord_user_id || __("None") }}</span>
                            </div>

                            <div v-if="profileUser.social_accounts && profileUser.social_accounts.length > 0" class="pt-2 mt-2">
                                <h3 class="font-bold text-card-foreground border-b pb-2 mb-2">
                                    {{ __("Linked Social Accounts") }}
                                </h3>
                                <div class="flex flex-col space-y-2">
                                    <div v-for="account in profileUser.social_accounts" :key="account.id" class="bg-muted p-2 rounded text-xs">
                                        <div class="grid grid-cols-2 gap-1">
                                            <span class="font-bold">{{ __("Provider") }}:</span> <span>{{ account.provider_name || account.provider }}</span>
                                            <span class="font-bold">{{ __("ID") }}:</span> <span class="truncate" :title="account.provider_id">{{ account.provider_id }}</span>
                                            <template v-if="account.name">
                                                <span class="font-bold">{{ __("Name") }}:</span> <span>{{ account.name }}</span>
                                            </template>
                                            <template v-if="account.email">
                                                <span class="font-bold">{{ __("Email") }}:</span> <span>{{ account.email }}</span>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <SocialChannelBox v-if="profileUser.social_links" :enabled="!!profileUser.social_links"
                        :show-title="false" :facebook="profileUser.social_links.s_facebook_url"
                        :youtube="profileUser.social_links.s_youtube_url"
                        :twitch="profileUser.social_links.s_twitch_url"
                        :twitter="profileUser.social_links.s_twitter_url"
                        :steam="profileUser.social_links.s_steam_profile_url"
                        :linkedin="profileUser.social_links.s_linkedin_url"
                        :tiktok="profileUser.social_links.s_tiktok_url"
                        :website="profileUser.social_links.s_website_url" />
                </div>
                <PostListBox v-if="$page.props.generalSettings.enable_status_feed" :username="profileUser.username"
                    :show-empty-post="true" />
                <Card v-else class="flex items-center justify-center w-full p-3 space-y-4 text-center">
                    <CardContent>
                        <span class="italic text-muted-foreground">{{
                            __("Posts Feed is disabled!")
                            }}</span>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from "@/Layouts/AppLayout.vue";
import AppHead from "@/Components/AppHead.vue";
import { Link } from "@inertiajs/vue3";
import Icon from "@/Components/Icon.vue";
import PostListBox from "@/Shared/PostListBox.vue";
import SocialChannelBox from "@/Shared/SocialChannelBox.vue";
import AlertCard from "@/Components/AlertCard.vue";
import UserDisplayname from "@/Components/UserDisplayname.vue";
import { Card, CardContent } from "@/Components/ui/card";
import { useAuthorizable } from "@/Composables/useAuthorizable";
import { useHelpers } from "@/Composables/useHelpers";
import {
    NoSymbolIcon,
    PencilSquareIcon,
    SpeakerXMarkIcon,
    SpeakerWaveIcon,
} from "@heroicons/vue/24/outline";

// Define props
defineProps({
    profileUser: {
        type: Object,
        required: true,
    },
});

// Setup composables
const { can } = useAuthorizable();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();
</script>

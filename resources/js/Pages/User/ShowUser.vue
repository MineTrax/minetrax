<template>
  <app-layout>
    <app-head :title="__(':name profile', { name: profileUser.name })" />

    <div class="max-w-6xl px-2 py-3 mx-auto space-y-4 md:py-12 md:px-10">
      <AlertCard
        v-if="profileUser.banned_at"
        text-color="text-red-600 dark:text-red-400"
        border-color="border-red-500"
      >
        {{ __("This User is Banned!") }}
        <template #icon>
          <icon
            name="ban"
            class="w-6 h-6 mr-4 text-red-500"
          />
        </template>
        <template #body>
          {{ __("If you think it is a mistake.") }}
          <inertia-link
            :href="route('staff.index')"
            class="font-semibold hover:underline"
          >
            {{ __("Please contact a Staff") }}
          </inertia-link>.
        </template>
      </AlertCard>

      <AlertCard
        v-if="
          $page.props.jetstream.hasEmailVerification &&
            profileUser.email_verified_at === null
        "
        text-color="text-orange-800 dark:text-orange-500"
        border-color="border-orange-500"
      >
        {{ __("This user hasn't verified his email yet!") }}
      </AlertCard>

      <div
        class="overflow-hidden bg-white border-b border-gray-200 shadow max-w-none dark:bg-cool-gray-800 dark:border-cool-gray-800 md:rounded"
      >
        <div>
          <div
            class="w-full bg-center bg-no-repeat bg-cover"
            :style="`height: 300px; background-image: url('${profileUser.cover_image_url}');`"
          >
            <img
              class="w-full h-full opacity-0"
              :src="`${profileUser.cover_image_url}`"
              alt="Cover Image"
            >
          </div>
          <div class="px-4 py-2">
            <div class="relative flex w-full">
              <!-- Avatar -->
              <div class="flex flex-1">
                <div style="margin-top: -6rem">
                  <div
                    style="height: 9rem; width: 9rem"
                    class="relative rounded-full md avatar"
                  >
                    <img
                      style="height: 9rem; width: 9rem"
                      class="relative transition bg-white border-4 border-white rounded-full md dark:bg-cool-gray-800 hover:bg-gray-200 dark:border-gray-600"
                      :src="profileUser.profile_photo_url"
                      alt=""
                    >
                    <div class="absolute" />
                  </div>
                </div>
              </div>
              <!-- Follow Button -->
              <div
                v-if="$page.props.auth.user"
                class="flex text-xs text-right md:text-medium"
              >
                <div class="p-4 space-x-2">
                  <inertia-link
                    v-if="
                      profileUser.id ===
                        $page.props.auth.user.id
                    "
                    v-tippy
                    :title="__('Update Profile')"
                    :href="route('profile.show')"
                    class="inline-flex items-center px-2 py-2 text-sm font-medium border-2 rounded-full border-light-blue-500 text-light-blue-500 hover:bg-light-blue-500 hover:text-white"
                  >
                    <PencilSquareIcon
                      class="w-5 h-5 stroke-2"
                    />
                  </inertia-link>
                  <inertia-link
                    v-if="
                      can('mute users') &&
                        !profileUser.muted_at
                    "
                    v-tippy
                    :title="__('Mute')"
                    method="post"
                    as="button"
                    :href="
                      route(
                        'admin.user.mute',
                        profileUser.id
                      )
                    "
                    class="inline-flex items-center px-2 py-2 text-sm font-medium text-orange-500 border-2 border-orange-500 rounded-full hover:bg-orange-500 hover:text-white"
                  >
                    <SpeakerXMarkIcon
                      class="w-5 h-5 stroke-2"
                    />
                  </inertia-link>
                  <inertia-link
                    v-if="
                      can('mute users') &&
                        profileUser.muted_at
                    "
                    v-tippy
                    :title="__('Unmute')"
                    method="post"
                    as="button"
                    :href="
                      route(
                        'admin.user.unmute',
                        profileUser.id
                      )
                    "
                    class="inline-flex items-center px-2 py-2 text-sm font-medium text-teal-500 border-2 border-teal-500 rounded-full hover:bg-teal-500 hover:text-white"
                  >
                    <SpeakerWaveIcon
                      class="w-5 h-5 stroke-2"
                    />
                  </inertia-link>
                  <inertia-link
                    v-if="
                      can('ban users') &&
                        !profileUser.banned_at
                    "
                    v-tippy
                    :title="__('Ban')"
                    method="post"
                    as="button"
                    :href="
                      route(
                        'admin.user.ban',
                        profileUser.id
                      )
                    "
                    class="inline-flex items-center px-2 py-2 text-sm font-medium text-red-500 border-2 border-red-500 rounded-full hover:bg-red-500 hover:text-white"
                  >
                    <NoSymbolIcon
                      class="w-5 h-5 stroke-2"
                    />
                  </inertia-link>
                  <inertia-link
                    v-if="
                      can('ban users') &&
                        profileUser.banned_at
                    "
                    v-tippy
                    :title="__('Unban')"
                    method="post"
                    as="button"
                    :href="
                      route(
                        'admin.user.unban',
                        profileUser.id
                      )
                    "
                    class="inline-flex items-center px-2 py-2 text-sm font-medium text-green-500 border-2 border-green-500 rounded-full hover:bg-green-500 hover:text-white"
                  >
                    <NoSymbolIcon
                      class="w-5 h-5 stroke-2"
                    />
                  </inertia-link>
                  <inertia-link
                    v-if="can('update users')"
                    v-tippy
                    :title="__('Edit')"
                    :href="
                      route(
                        'admin.user.edit',
                        profileUser.id
                      )
                    "
                    class="inline-flex items-center px-2 py-2 text-sm font-medium text-blue-500 border-2 border-blue-500 rounded-full hover:bg-blue-500 hover:text-white"
                  >
                    <PencilSquareIcon
                      class="w-5 h-5 stroke-2"
                    />
                  </inertia-link>
                </div>
              </div>
            </div>

            <!-- Profile info -->
            <div class="justify-center w-full mt-3 ml-3 space-y-2">
              <!-- User basic-->
              <div>
                <user-displayname
                  :user="profileUser"
                  icon-class="w-6 h-6"
                  text-class="text-xl"
                />
                <p
                  class="font-medium leading-5 text-gray-600 dark:text-gray-400"
                >
                  @{{ profileUser.username }}
                </p>
              </div>
              <!-- Rank -->
              <div>
                <img
                  v-for="role of profileUser.roles"
                  :key="role.id"
                  v-tippy
                  :src="role.photo_url"
                  :alt="role.display_name"
                  :content="role.display_name"
                  class="focus:outline-none max-h-16"
                >
              </div>
              <div class="flex justify-end mr-4">
                <p
                  v-tippy
                  class="text-sm font-medium leading-5 text-gray-600 focus:outline-none"
                  :title="
                    formatToDayDateString(
                      profileUser.created_at
                    )
                  "
                >
                  {{ __("Joined") }}:
                  {{
                    formatTimeAgoToNow(
                      profileUser.created_at
                    )
                  }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-col md:space-x-4 md:flex-row">
        <div class="flex flex-col mb-4 space-y-4 md:mb-0 md:w-1/2">
          <div
            v-if="profileUser.players.length > 0"
            class="flex flex-col w-full space-y-2 bg-white rounded shadow dark:bg-cool-gray-800"
          >
            <div
              v-for="player in profileUser.players"
              :key="player.uuid"
              class="flex justify-around p-4 space-x-4 border-b border-gray-200 dark:border-none"
            >
              <img
                :src="
                  route('player.render.get', {
                    uuid: player.uuid,
                    username: player.username,
                    scale: 4,
                  })
                "
                :alt="player.username"
              >

              <div class="flex flex-col flex-1 space-y-2">
                <div class="username">
                  <inertia-link
                    as="a"
                    :href="
                      route('player.show', player.uuid)
                    "
                    class="text-lg font-bold text-light-blue-400 hover:text-light-blue-500"
                  >
                    {{ player.username }}
                  </inertia-link>
                </div>

                <div class="flex items-center justify-between">
                  <p class="font-bold dark:text-gray-400">
                    {{ __("Position") }}:
                  </p>
                  <div
                    class="flex items-center space-x-2 text-sm font-extrabold text-center text-light-blue-400"
                  >
                    <span
                      v-if="player.position"
                      class="px-2 text-lg border-2 rounded border-light-blue-300 bg-light-blue-50 dark:bg-cool-gray-800"
                    >
                      {{ player.position }}
                    </span>
                    <span
                      v-else
                      class="text-sm italic text-gray-500 dark:text-gray-400"
                    >{{ __("None") }}</span>
                  </div>
                </div>

                <div class="flex items-center justify-between">
                  <p class="font-bold dark:text-gray-400">
                    {{ __("Rating") }}:
                  </p>
                  <icon
                    v-if="player.rating != null"
                    v-tippy
                    class="w-8 h-8 focus:outline-none"
                    :name="`rating-${player.rating}`"
                    :content="player.rating"
                  />
                  <p
                    v-else
                    class="text-sm italic text-gray-500 dark:text-gray-400"
                  >
                    {{ __("None") }}
                  </p>
                </div>
                <div class="flex items-center justify-between">
                  <p class="font-bold dark:text-gray-400">
                    {{ __("Rank") }}:
                  </p>
                  <div class="flex items-center space-x-2">
                    <p
                      v-if="player.rank"
                      class="dark:text-gray-200"
                    >
                      {{ player.rank.name }}
                    </p>
                    <p
                      v-else
                      class="text-sm italic text-gray-500 dark:text-gray-400"
                    >
                      {{ __("None") }}
                    </p>
                    <img
                      v-if="
                        player.rank &&
                          player.rank.photo_url
                      "
                      v-tippy
                      :src="player.rank.photo_url"
                      :alt="player.rank.name"
                      :title="player.rank.name"
                      class="w-8 h-8 focus:outline-none"
                    >
                  </div>
                </div>

                <div class="flex items-center justify-between">
                  <p class="font-bold dark:text-gray-400">
                    {{ __("Last Seen") }}:
                  </p>
                  <div class="flex items-center space-x-2">
                    <p
                      v-tippy
                      class="focus:outline-none dark:text-gray-200"
                      :title="
                        formatToDayDateString(
                          player.last_seen_at
                        )
                      "
                    >
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
          </div>

          <div
            v-if="
              profileUser.badges && profileUser.badges.length > 0
            "
            class="p-4 bg-white rounded shadow dark:bg-cool-gray-800"
          >
            <h3 class="font-bold text-gray-700 dark:text-gray-200">
              Badges
            </h3>
            <div class="flex flex-row justify-center space-x-2">
              <div
                v-for="badge in profileUser.badges"
                :key="badge.id"
                v-tippy
                :title="badge.name"
              >
                <img
                  class="w-12 h-12"
                  :src="badge.photo_url"
                  :alt="badge.name"
                >
              </div>
            </div>
          </div>

          <div
            v-if="profileUser.about"
            class="flex flex-col w-full p-4 bg-white rounded shadow dark:bg-cool-gray-800"
          >
            <span class="whitespace-pre-wrap dark:text-gray-200">{{
              profileUser.about
            }}</span>
          </div>

          <div
            class="flex flex-col w-full p-4 space-y-2 bg-white rounded shadow dark:bg-cool-gray-800 dark:text-gray-400"
          >
            <div class="flex justify-between">
              <span>{{ __("Country") }}</span>
              <span
                class="font-semibold text-gray-800 dark:text-gray-200"
              >
                {{ profileUser.country.name }}
                <img
                  class="inline h-6 mb-1"
                  :src="profileUser.country.photo_path"
                  :alt="profileUser.country.name"
                >
              </span>
            </div>
            <div class="flex justify-between">
              <span>{{ __("Day of Birth") }}</span>
              <span
                class="font-semibold text-gray-800 dark:text-gray-200"
              >{{
                profileUser.dob_string || __("unknown")
              }}</span>
            </div>
            <div class="flex justify-between">
              <span>{{ __("Gender") }}</span>
              <span
                class="font-semibold text-gray-800 dark:text-gray-200"
              >{{
                __(profileUser.gender_string) ||
                  __("unknown")
              }}</span>
            </div>
            <div class="flex justify-between">
              <span>{{ __("Total Posts") }}</span>
              <span
                class="font-semibold text-gray-800 dark:text-gray-200"
              >{{ profileUser.posts_count }}</span>
            </div>
            <div
              v-if="
                profileUser.social_links &&
                  profileUser.social_links.s_discord_username
              "
              class="flex justify-between"
            >
              <span>{{ __("Discord") }}</span>
              <span
                class="font-semibold text-gray-800 dark:text-gray-200"
              >{{
                profileUser.social_links.s_discord_username
              }}</span>
            </div>
          </div>

          <social-channel-box
            v-if="profileUser.social_links"
            :enabled="!!profileUser.social_links"
            :show-title="false"
            :facebook="profileUser.social_links.s_facebook_url"
            :youtube="profileUser.social_links.s_youtube_url"
            :twitch="profileUser.social_links.s_twitch_url"
            :twitter="profileUser.social_links.s_twitter_url"
            :steam="profileUser.social_links.s_steam_profile_url"
            :linkedin="profileUser.social_links.s_linkedin_url"
            :tiktok="profileUser.social_links.s_tiktok_url"
            :website="profileUser.social_links.s_website_url"
          />
        </div>
        <post-list-box
          v-if="$page.props.generalSettings.enable_status_feed"
          :username="profileUser.username"
          :show-empty-post="true"
        />
        <div
          v-else
          class="flex items-center justify-center w-full p-3 space-y-4 text-center text-gray-500 bg-white rounded shadow sm:px-5 dark:bg-cool-gray-800"
        >
          <span class="italic">{{
            __("Posts Feed is disabled!")
          }}</span>
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import Icon from '@/Components/Icon.vue';
import PostListBox from '@/Shared/PostListBox.vue';
import SocialChannelBox from '@/Shared/SocialChannelBox.vue';
import AlertCard from '@/Components/AlertCard.vue';
import UserDisplayname from '@/Components/UserDisplayname.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { useHelpers } from '@/Composables/useHelpers';
import {
    NoSymbolIcon,
    PencilSquareIcon,
    SpeakerXMarkIcon,
    SpeakerWaveIcon,
} from '@heroicons/vue/24/outline';

export default {
    components: {
        SocialChannelBox,
        Icon,
        AppLayout,
        PostListBox,
        AlertCard,
        UserDisplayname,
        NoSymbolIcon,
        PencilSquareIcon,
        SpeakerXMarkIcon,
        SpeakerWaveIcon,
    },
    props: {
        profileUser: Object,
    },
    setup() {
        const { can } = useAuthorizable();
        const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();
        return { can, formatTimeAgoToNow, formatToDayDateString };
    },
};
</script>

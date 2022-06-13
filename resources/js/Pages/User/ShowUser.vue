<template>
  <app-layout>
    <app-head :title="`${profileUser.name} profile`" />

    <div class="px-2 py-3 md:py-12 md:px-10 max-w-6xl mx-auto space-y-4">
      <div
        v-if="profileUser.banned_at"
        role="alert"
        class="mb-4 bg-white dark:bg-cool-gray-800 border-t-4 border-red-500 rounded-b text-red-900 px-4 py-3 shadow"
      >
        <div class="flex">
          <div class="py-1">
            <icon
              name="ban"
              class="h-6 w-6 text-red-500 mr-4"
            />
          </div>
          <div>
            <p class="font-bold text-red-600 dark:text-red-400">
              This User is Banned!
            </p>
            <p class="text-sm dark:text-red-400">
              If you think it is a mistake. Please contact a <inertia-link
                :href="route('staff.index')"
                class="hover:underline font-semibold"
              >
                Staff
              </inertia-link>.
            </p>
          </div>
        </div>
      </div>

      <div class="shadow max-w-none bg-white dark:bg-cool-gray-800 overflow-hidden border-b border-gray-200 dark:border-cool-gray-800 md:rounded">
        <div>
          <div
            class="w-full bg-cover bg-no-repeat bg-center"
            :style="`height: 300px; background-image: url('${profileUser.cover_image_url}');`"
          >
            <img
              class="opacity-0 w-full h-full"
              :src="`${profileUser.cover_image_url}`"
              alt="Cover Image"
            >
          </div>
          <div class="px-4 py-2">
            <div class="relative flex w-full">
              <!-- Avatar -->
              <div class="flex flex-1">
                <div style="margin-top: -6rem;">
                  <div
                    style="height:9rem; width:9rem;"
                    class="md rounded-full relative avatar"
                  >
                    <img
                      style="height:9rem; width:9rem;"
                      class="md rounded-full bg-white dark:bg-cool-gray-800 transition hover:bg-gray-200 relative border-4 border-white dark:border-gray-600"
                      :src="profileUser.profile_photo_url"
                      alt=""
                    >
                    <div class="absolute" />
                  </div>
                </div>
              </div>
              <!-- Follow Button -->
              <div
                v-if="$page.props.user"
                class="flex space-x-2 text-right text-xs md:text-medium"
              >
                <inertia-link
                  v-if="profileUser.id === $page.props.user.id"
                  :href="route('profile.show')"
                  class="flex justify-center max-h-max whitespace-nowrap focus:outline-none rounded max-w-max border bg-transparent border-light-blue-500 text-light-blue-500 hover:bg-light-blue-50 items-center font-bold py-2 px-4 rounded-full mr-0 ml-auto dark:hover:bg-cool-gray-900"
                >
                  Edit<span class="hidden md:block">&nbsp;Profile</span>
                </inertia-link>
                <inertia-link
                  v-if="can('mute users') && !profileUser.muted_at"
                  method="post"
                  as="button"
                  :href="route('admin.user.mute', profileUser.id)"
                  class="flex justify-center max-h-max whitespace-nowrap focus:outline-none rounded max-w-max border bg-transparent border-yellow-500 text-yellow-500 hover:bg-yellow-50 items-center font-bold py-2 px-4 rounded-full mr-0 ml-auto dark:hover:bg-cool-gray-900"
                >
                  Mute<span class="hidden md:block">&nbsp;User</span>
                </inertia-link>
                <inertia-link
                  v-if="can('mute users') && profileUser.muted_at"
                  method="post"
                  as="button"
                  :href="route('admin.user.unmute', profileUser.id)"
                  class="flex justify-center max-h-max whitespace-nowrap focus:outline-none rounded max-w-max border bg-transparent border-green-500 text-green-500 hover:bg-green-50 items-center font-bold py-2 px-4 rounded-full mr-0 ml-auto dark:hover:bg-cool-gray-900"
                >
                  UnMute<span class="hidden md:block">&nbsp;User</span>
                </inertia-link>
                <inertia-link
                  v-if="can('ban users') && !profileUser.banned_at"
                  method="post"
                  as="button"
                  :href="route('admin.user.ban', profileUser.id)"
                  class="flex justify-center max-h-max whitespace-nowrap focus:outline-none rounded max-w-max border bg-transparent border-red-500 text-red-500 hover:bg-red-50 items-center font-bold py-2 px-4 rounded-full mr-0 ml-auto dark:hover:bg-cool-gray-900"
                >
                  Ban<span class="hidden md:block">&nbsp;User</span>
                </inertia-link>
                <inertia-link
                  v-if="can('ban users') && profileUser.banned_at"
                  method="post"
                  as="button"
                  :href="route('admin.user.unban', profileUser.id)"
                  class="flex justify-center max-h-max whitespace-nowrap focus:outline-none rounded max-w-max border bg-transparent border-green-500 text-green-500 hover:bg-green-50 items-center font-bold py-2 px-4 rounded-full mr-0 ml-auto dark:hover:bg-cool-gray-900"
                >
                  UnBan<span class="hidden md:block">&nbsp;User</span>
                </inertia-link>
              </div>
            </div>

            <!-- Profile info -->
            <div class="space-y-2 justify-center w-full mt-3 ml-3">
              <!-- User basic-->
              <div>
                <h2
                  class="text-xl leading-6 font-bold text-black dark:text-gray-200"
                  :style="[profileUser.roles[0].color ? {color: profileUser.roles[0].color} : null]"
                >
                  {{ profileUser.name }}
                  <icon
                    v-if="profileUser.verified_at"
                    v-tippy
                    name="verified-check-fill"
                    title="Verified Account"
                    class="mb-1 focus:outline-none h-6 w-6 fill-current inline text-light-blue-400"
                  />
                  <icon
                    v-if="profileUser.is_staff"
                    v-tippy
                    name="shield-check-fill"
                    title="Staff Member"
                    class="mb-1 focus:outline-none h-6 w-6 fill-current inline text-pink-400"
                  />
                  <icon
                    v-if="profileUser.muted_at"
                    v-tippy
                    name="volume-off-fill"
                    title="Muted User"
                    class="mb-1 focus:outline-none h-6 w-6 fill-current inline text-red-500"
                  />
                </h2>
                <p class="leading-5 font-medium text-gray-600 dark:text-gray-400">
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
                  class="text-sm leading-5 font-medium text-gray-600 focus:outline-none"
                  :title="format(new Date(profileUser.created_at), 'E, do MMM yyyy, h:mm aaa')"
                >
                  Joined: {{
                    formatDistanceToNowStrict(new Date(profileUser.created_at), {addSuffix: true})
                  }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="flex flex-col md:space-x-4 md:flex-row">
        <div class="flex mb-4 md:mb-0 md:w-1/2 flex-col space-y-4">
          <div
            v-if="profileUser.players.length > 0"
            class="flex flex-col bg-white dark:bg-cool-gray-800 rounded w-full shadow space-y-2"
          >
            <div
              v-for="player in profileUser.players"
              :key="player.uuid"
              class="flex justify-around p-4 space-x-4 border-b border-gray-200 dark:border-none"
            >
              <img
                :src="`https://crafatar.com/renders/body/${player.uuid}?scale=4`"
                :alt="player.username"
              >

              <div class="flex flex-col flex-1 space-y-2">
                <div class="username">
                  <inertia-link
                    as="a"
                    :href="route('player.show', player.uuid)"
                    class="font-bold text-lg text-light-blue-400 hover:text-light-blue-500"
                  >
                    {{ player.username }}
                  </inertia-link>
                </div>

                <div class="flex items-center justify-between">
                  <p class="font-bold dark:text-gray-400">
                    Position:
                  </p>
                  <div class="flex items-center space-x-2 text-center text-sm text-light-blue-400 font-extrabold">
                    <span
                      v-if="player.position"
                      class="border-2 rounded text-lg px-2 border-light-blue-300 bg-light-blue-50 dark:bg-cool-gray-800"
                    >
                      {{ player.position }}
                    </span>
                    <span
                      v-else
                      class="italic text-sm text-gray-500 dark:text-gray-400"
                    >None</span>
                  </div>
                </div>

                <div class="flex items-center justify-between">
                  <p class="font-bold dark:text-gray-400">
                    Rating:
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
                    class="italic text-sm text-gray-500 dark:text-gray-400"
                  >
                    None
                  </p>
                </div>
                <div class="flex items-center justify-between">
                  <p class="font-bold dark:text-gray-400">
                    Rank:
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
                      class="italic text-sm text-gray-500 dark:text-gray-400"
                    >
                      None
                    </p>
                    <img
                      v-if="player.rank && player.rank.photo_url"
                      v-tippy
                      :src="player.rank.photo_url"
                      :alt="player.rank.name"
                      :title="player.rank.name"
                      class="h-8 w-8 focus:outline-none"
                    >
                  </div>
                </div>

                <div class="flex items-center justify-between">
                  <p class="font-bold dark:text-gray-400">
                    Last Seen:
                  </p>
                  <div class="flex items-center space-x-2">
                    <p
                      v-tippy
                      class="focus:outline-none dark:text-gray-200"
                      :title="format(new Date(player.last_seen_at), 'E, do MMM yyyy, h:mm aaa')"
                    >
                      {{
                        formatDistanceToNowStrict(new Date(player.last_seen_at), {addSuffix: true})
                      }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div
            v-if="profileUser.about"
            class="flex flex-col bg-white dark:bg-cool-gray-800 rounded w-full shadow p-4"
          >
            <span class="whitespace-pre-wrap dark:text-gray-200">{{ profileUser.about }}</span>
          </div>

          <div class="flex flex-col bg-white dark:bg-cool-gray-800 dark:text-gray-400 rounded w-full shadow p-4 space-y-2">
            <div class="flex justify-between">
              <span>Country</span>
              <span class="text-gray-800 dark:text-gray-200 font-semibold">
                {{ profileUser.country.name }}
                <img
                  class="inline h-6 mb-1"
                  :src="profileUser.country.photo_path"
                  :alt="profileUser.country.name"
                >
              </span>
            </div>
            <div class="flex justify-between">
              <span>Day of Birth</span>
              <span class="text-gray-800 dark:text-gray-200 font-semibold">{{ profileUser.dob_string || 'unknown' }}</span>
            </div>
            <div class="flex justify-between">
              <span>Gender</span>
              <span class="text-gray-800 dark:text-gray-200 font-semibold">{{ profileUser.gender_string || 'unknown' }}</span>
            </div>
            <div class="flex justify-between">
              <span>Total Posts</span>
              <span class="text-gray-800 dark:text-gray-200 font-semibold">{{ profileUser.posts_count }}</span>
            </div>
            <div
              v-if="profileUser.social_links && profileUser.social_links.s_discord_username"
              class="flex justify-between"
            >
              <span>Discord</span>
              <span class="text-gray-800 dark:text-gray-200 font-semibold">{{
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
          class="space-y-4 w-full p-3 sm:px-5 bg-white dark:bg-cool-gray-800 rounded shadow text-gray-500 text-center justify-center items-center flex"
        >
          <span class="italic">Posts Feed is disabled!</span>  😞
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import JetSectionBorder from '@/Jetstream/SectionBorder';
import {format, formatDistanceToNowStrict} from 'date-fns';
import Icon from '@/Components/Icon';
import PostListBox from '@/Shared/PostListBox';
import SocialChannelBox from '@/Shared/SocialChannelBox';

export default {

    components: {
        SocialChannelBox,
        Icon,
        AppLayout,
        JetSectionBorder,
        PostListBox
    },
    props: {
        profileUser: Object,
    },

    data() {
        return {
            formatDistanceToNowStrict: formatDistanceToNowStrict,
            format: format
        };
    },
};
</script>

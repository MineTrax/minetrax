<script setup>
import Icon from '@/Components/Icon.vue';
import JetApplicationMark from '@/Jetstream/ApplicationMark.vue';
import { usePage } from '@inertiajs/vue3';

const customFooterData = window._customfooter;
const customFooterEnabled = !!customFooterData;
const isAdminRoute = route().current('admin.*');
const canShowCustomFooter = !isAdminRoute && customFooterEnabled;

const generalSettings = usePage().props.generalSettings;
const discordUrl = generalSettings?.discord_invite_url;
const youtubeUrl = generalSettings?.youtube_url;
const facebookUrl = generalSettings?.facebook_url;
const twitterUrl = generalSettings?.twitter_url;
const twitchUrl = generalSettings?.twitch_url;
const tiktokUrl = generalSettings?.tiktok_url;
const linkedinUrl = generalSettings?.linkedin_url;

let customFooterColumns = [];
if(customFooterData?.style == 'variant_1') {
    customFooterColumns = customFooterData?.columns?.filter((column) => column.items.length > 0) ?? [];
} else if(customFooterData?.style == 'variant_2') {
    customFooterColumns = customFooterData?.columns?.reduce((acc, curr) => {
        acc.push(...curr.items);
        return acc;
    }, []);
}
</script>

<template>
  <footer
    class="w-full text-gray-700 bg-white dark:bg-cool-gray-800 body-font"
    :class="{'md:mt-28': canShowCustomFooter}"
  >
    <div
      v-if="canShowCustomFooter && customFooterData.style == 'variant_1'"
      id="variant_1"
      class="container flex flex-col flex-wrap px-5 py-20 mx-auto md:items-center lg:items-start md:flex-row md:flex-no-wrap"
    >
      <div class="flex-shrink-0 w-64 mx-auto text-center md:mx-0 md:text-left">
        <InertiaLink
          class="flex md:justify-start justify-center"
          :href="route('home')"
        >
          <JetApplicationMark class="block w-auto h-9" />
        </InertiaLink>

        <p
          v-if="customFooterData.site_moto"
          class="my-4 text-sm text-gray-500 dark:text-gray-400 whitespace-pre-wrap"
        >
          {{ customFooterData.site_moto }}
        </p>
        <div class="mt-4">
          <span class="inline-flex justify-center mt-2 sm:ml-auto sm:mt-0 sm:justify-start">
            <a
              v-if="facebookUrl"
              target="_blank"
              :href="facebookUrl"
              class="text-gray-500 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200"
            >
              <Icon
                name="facebook"
                class="w-5 h-5 fill-current"
              />
            </a>
            <a
              v-if="twitterUrl"
              target="_blank"
              :href="twitterUrl"
              class="ml-3 text-gray-500 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200"
            >
              <Icon
                name="twitter"
                class="w-5 h-5 fill-current"
              />
            </a>
            <a
              v-if="linkedinUrl"
              target="_blank"
              :href="linkedinUrl"
              class="ml-3 -mt-0.5 text-gray-500 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200"
            >
              <Icon
                name="linkedin"
                class="h-5 fill-current"
              />
            </a>

            <a
              v-if="youtubeUrl"
              target="_blank"
              :href="youtubeUrl"
              class="ml-3 -mt-0.5 text-gray-500 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200"
            >
              <Icon
                name="youtube"
                class="h-6 fill-current"
              />
            </a>
            <a
              v-if="discordUrl"
              target="_blank"
              :href="discordUrl"
              class="ml-3 -mt-0.5 text-gray-500 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200"
            >
              <Icon
                name="discord"
                class="h-6 fill-current"
              />
            </a>
            <a
              v-if="tiktokUrl"
              target="_blank"
              :href="tiktokUrl"
              class="ml-3 text-gray-500 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200"
            >
              <Icon
                name="tiktok"
                class="w-5 h-5 fill-current"
              />
            </a>
            <a
              v-if="twitchUrl"
              target="_blank"
              :href="twitchUrl"
              class="ml-3 text-gray-500 cursor-pointer hover:text-gray-700 dark:hover:text-gray-200"
            >
              <Icon
                name="twitch"
                class="w-5 h-5 fill-current"
              />
            </a>
          </span>
        </div>
      </div>
      <div class="flex flex-col flex-wrap flex-grow mt-10 -mb-10 text-center md:flex-row md:pl-20 md:mt-0 md:text-left">
        <div
          v-for="column,cIndex in customFooterColumns"
          :key="cIndex"
          class="flex-1 px-4"
        >
          <h3 class="mb-3 font-bold text-gray-900 dark:text-gray-300">
            {{ column.title }}
          </h3>
          <nav class="mb-10 list-none">
            <li
              v-for="item,iIndex in column.items"
              :key="cIndex + '-' + iIndex"
              class="mt-3"
            >
              <a
                :href="item.url"
                class="text-gray-500 dark:text-gray-400 dark:hover:text-gray-300 cursor-pointer hover:text-gray-900"
              >{{ item.title }}
              </a>
            </li>
          </nav>
        </div>
      </div>
    </div>



    <div
      v-if="canShowCustomFooter && customFooterData.style == 'variant_2'"
      id="variant_2"
      class="bg-white dark:bg-cool-gray-800"
    >
      <div class="mx-auto max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
        <div class="flex justify-center">
          <InertiaLink
            class="flex md:justify-start justify-center"
            :href="route('home')"
          >
            <JetApplicationMark class="block w-auto h-9" />
          </InertiaLink>
        </div>

        <p
          v-if="customFooterData.site_moto"
          class="mx-auto mt-6 max-w-md text-center whitespace-pre-wrap leading-relaxed text-gray-500 dark:text-gray-400"
        >
          {{ customFooterData.site_moto }}
        </p>

        <ul
          class="mt-12 flex flex-wrap justify-center gap-6 md:gap-8 lg:gap-12"
        >
          <li
            v-for="item,index in customFooterColumns"
            :key="index"
          >
            <a
              class="text-gray-700 transition hover:text-gray-700/75 dark:text-gray-300 dark:hover:text-gray-300/75"
              :href="item.url"
            >
              {{ item.title }}
            </a>
          </li>
        </ul>

        <ul class="mt-12 flex justify-center gap-6 md:gap-8">
          <li
            v-if="facebookUrl"
          >
            <a
              :href="facebookUrl"
              rel="noreferrer"
              target="_blank"
              class="text-gray-700 transition hover:text-gray-700/75 dark:text-gray-400 dark:hover:text-gray-300"
            >
              <span class="sr-only">Facebook</span>
              <Icon
                name="facebook"
                class="h-6 w-6 fill-current"
              />
            </a>
          </li>
          <li
            v-if="twitterUrl"
          >
            <a
              :href="twitterUrl"
              rel="noreferrer"
              target="_blank"
              class="text-gray-700 transition hover:text-gray-700/75 dark:text-gray-400 dark:hover:text-gray-300"
            >
              <span class="sr-only">Twitter</span>
              <Icon
                name="twitter"
                class="h-6 w-6 fill-current"
              />
            </a>
          </li>
          <li
            v-if="linkedinUrl"
            class="-mt-0.5"
          >
            <a
              :href="linkedinUrl"
              rel="noreferrer"
              target="_blank"
              class="text-gray-700 transition hover:text-gray-700/75 dark:text-gray-400 dark:hover:text-gray-300"
            >
              <span class="sr-only">LinkedIn</span>
              <Icon
                name="linkedin"
                class="h-6 w-6 fill-current"
              />
            </a>
          </li>
          <li
            v-if="youtubeUrl"
            class="-mt-0.5"
          >
            <a
              :href="youtubeUrl"
              rel="noreferrer"
              target="_blank"
              class="text-gray-700 transition hover:text-gray-700/75 dark:text-gray-400 dark:hover:text-gray-300"
            >
              <span class="sr-only">YouTube</span>
              <Icon
                name="youtube"
                class="h-7 w-7 fill-current"
              />
            </a>
          </li>
          <li
            v-if="discordUrl"
          >
            <a
              :href="discordUrl"
              rel="noreferrer"
              target="_blank"
              class="text-gray-700 transition hover:text-gray-700/75 dark:text-gray-400 dark:hover:text-gray-300"
            >
              <span class="sr-only">Discord</span>
              <Icon
                name="discord"
                class="h-6 w-6 fill-current"
              />
            </a>
          </li>
          <li
            v-if="tiktokUrl"
          >
            <a
              :href="tiktokUrl"
              rel="noreferrer"
              target="_blank"
              class="text-gray-700 transition hover:text-gray-700/75 dark:text-gray-400 dark:hover:text-gray-300"
            >
              <span class="sr-only">TikTok</span>
              <Icon
                name="tiktok"
                class="h-6 w-6 fill-current"
              />
            </a>
          </li>
          <li
            v-if="twitchUrl"
          >
            <a
              :href="twitchUrl"
              rel="noreferrer"
              target="_blank"
              class="text-gray-700 transition hover:text-gray-700/75 dark:text-gray-400 dark:hover:text-gray-300"
            >
              <span class="sr-only">Twitch</span>
              <Icon
                name="twitch"
                class="h-6 w-6 fill-current"
              />
            </a>
          </li>
        </ul>
      </div>
    </div>

    <div
      :class="{
        'bg-white dark:bg-cool-gray-800 border-t border-gray-200 dark:border-gray-700': canShowCustomFooter,
        'bg-gray-200': !canShowCustomFooter,
      }"
      class="flex flex-col items-center justify-center p-5 dark:bg-gray-900"
    >
      <div class="text-sm text-gray-800 dark:text-gray-400">
        &copy; {{ $page.props.generalSettings.site_name }} {{ new Date().getFullYear() }}
      </div>
      <div
        v-if="$page.props.showPoweredBy"
        class="text-xs text-gray-500"
      >
        {{ __("Powered with") }}
        <Icon
          class="absolute inline-flex w-4 h-4 text-red-500 opacity-75 animate-ping"
          name="heart-fill"
        />
        <Icon
          class="relative inline-flex w-4 h-4 text-red-500"
          name="heart-fill"
        />
        by <a
          target="_blank"
          href="https://minetrax.github.io"
          class="hover:underline hover:text-light-blue-500"
        >MineTrax</a>

        <span
          v-if="$page.props.poweredByExtraName && $page.props.poweredByExtraLink"
        >
          &
          <a
            target="_blank"
            :href="$page.props.poweredByExtraLink"
            class="hover:underline hover:text-light-blue-500"
          >{{ $page.props.poweredByExtraName }}</a>
        </span>
      </div>
    </div>
  </footer>
</template>

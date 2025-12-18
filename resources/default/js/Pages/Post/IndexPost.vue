<template>
  <AppLayout>
    <AppHead
      :title="__('Post Feed')"
    />

    <AppBreadcrumb :items="breadcrumbItems" />

    <div class="px-2 py-4 md:px-10 max-w-screen-2xl mx-auto">
      <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0">
        <div class="hidden md:flex flex-col space-y-4 flex-none w-1/4 h-screen sticky" :class="{'top-16': isStickyNav, 'top-5': !isStickyNav}">
          <DidYouKnowBox :enabled="page.props.generalSettings.enable_didyouknowbox" />
          <DiscordServerBox
            :enabled="page.props.generalSettings.enable_discordbox"
            :server="page.props.generalSettings.discord_server_id"
            :invite="page.props.generalSettings.discord_invite_url"
          />
        </div>

        <div class="flex-grow">
          <div class="-my-2 overflow-x-auto md:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block overflow-hidden min-w-full md:px-6 lg:px-8">
              <div class="">
                <PostListBox />
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col space-y-4 flex-none md:w-1/4 h-screen md:sticky" :class="{'top-16': isStickyNav, 'top-5': !isStickyNav}">
          <ServerStatusBox />
          <ShoutBox />
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import PostListBox from '@/Shared/PostListBox.vue';
import DidYouKnowBox from '@/Shared/DidYouKnowBox.vue';
import DiscordServerBox from '@/Shared/DiscordServerBox.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/Composables/useTranslations';

const { __ } = useTranslations();

const page = usePage();

const isStickyNav = page.props.generalSettings.enable_sticky_header_menu;

const breadcrumbItems = [
    {
        text: __('Home'),
        url: route('home'),
        current: false
    },
    {
        text: __('Posts'),
        current: true
    }
];
</script>

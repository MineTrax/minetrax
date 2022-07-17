<template>
  <app-layout>
    <app-head
      :title="__('Post #:id by :name', {id: post.id, name: post.user.name})"
    />

    <div class="px-2 py-4 md:py-12 md:px-16 max-w-8xl mx-auto">
      <div class="flex flex-col md:flex-row md:space-x-4 space-y-4 md:space-y-0">
        <div class="hidden md:flex flex-col space-y-4 flex-none w-1/4 h-screen sticky top-5">
          <did-you-know-box :enabled="$page.props.generalSettings.enable_didyouknowbox" />
          <discord-server-box
            :enabled="$page.props.generalSettings.enable_discordbox"
            :server="$page.props.generalSettings.discord_server_id"
          />
        </div>

        <div class="flex-grow">
          <div class="-my-2 overflow-x-auto md:-mx-6 lg:-mx-8">
            <div class="py-2 align-middle inline-block min-w-full md:px-6 lg:px-8">
              <div class="">
                <post
                  :post="post"
                  :comments-section-opened="true"
                />
              </div>
            </div>
          </div>
        </div>

        <div class="flex flex-col space-y-4 flex-none md:w-1/4 h-screen md:sticky top-5">
          <server-status-box />
          <shout-box />
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import {formatDistanceToNowStrict} from 'date-fns';
import ShoutBox from '@/Shared/ShoutBox';
import ServerStatusBox from '@/Shared/ServerStatusBox';
import DidYouKnowBox from '@/Shared/DidYouKnowBox';
import DiscordServerBox from '@/Shared/DiscordServerBox';
import Post from '@/Components/Post';

export default {
    components: {
        DiscordServerBox,
        DidYouKnowBox,
        ServerStatusBox,
        AppLayout,
        ShoutBox,
        Post
    },
    props: {
        post: Object,
    },
    data() {
        return {
            formatDistanceToNowStrict: formatDistanceToNowStrict
        };
    },
};
</script>

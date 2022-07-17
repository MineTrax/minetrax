<template>
  <app-layout>
    <app-head />

    <div class="grid gap-4 grid-cols-none md:grid-cols-4 md:gap-6 py-4 px-2 md:py-12 md:px-10 md:px-6 lg:px-16 mx-auto max-w-screen-2xl">
      <div class="col-span-1 space-y-4 order-1 md:order-none">
        <online-players-box v-if="generalSettings.enable_mcserver_onlineplayersbox" />
        <voting-sites-box
          :votingsites="generalSettings.voteforserverbox_content"
          :enabled="generalSettings.enable_voteforserverbox"
        />
        <poll-box :poll="latestPoll" />
        <did-you-know-box :enabled="generalSettings.enable_didyouknowbox" />
        <discord-server-box
          :enabled="generalSettings.enable_discordbox"
          :server="generalSettings.discord_server_id"
        />
        <donation-box />
      </div>
      <div class="col-span-1 md:col-span-2 space-y-4 order-3 md:order-none">
        <version-check v-if="$page.props.user && isStaff($page.props.user)" />
        <welcome-box
          v-if="generalSettings.enable_welcomebox"
          :html-data="welcomeBoxContentHtml"
        />
        <ingame-chat-box
          :default-server-id="chatDefaultServerId"
          :server-list="chatServerList"
        />
        <top-players-list-box
          :enabled="true"
          :players="top10Players"
          :title="__('Top 10 Players')"
        />
        <latest-pinned-news :newslist="pinnedNewsList" />
        <post-list-box v-if="generalSettings.enable_status_feed" />
      </div>
      <div class="col-span-1 space-y-4 order-2 md:order-none">
        <server-status-box />
        <shout-box />
        <news-box :newslist="newslist" />
        <online-users-box
          :users="onlineUsers"
          :enabled="generalSettings.enable_onlineuserbox"
        />
        <newest-user-box
          :user="newestUser"
          :enabled="generalSettings.enable_newuserbox"
        />
        <social-channel-box
          :enabled="generalSettings.enable_socialbox"
          :show-title="true"
          :twitter="generalSettings.twitter_url"
          :twitch="generalSettings.twitch_url"
          :youtube="generalSettings.youtube_url"
          :facebook="generalSettings.facebook_url"
        />
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout';
import NewsBox from '@/Shared/NewsBox';
import ShoutBox from '@/Shared/ShoutBox';
import DiscordServerBox from '@/Shared/DiscordServerBox';
import NewestUserBox from '@/Shared/NewestUserBox';
import VotingSitesBox from '@/Shared/VotingSitesBox';
import WelcomeBox from '@/Shared/WelcomeBox';
import PostListBox from '@/Shared/PostListBox';
import LatestPinnedNews from '@/Shared/LatestPinnedNews';
import SocialChannelBox from '@/Shared/SocialChannelBox';
import DidYouKnowBox from '@/Shared/DidYouKnowBox';
import VersionCheck from '@/Shared/VersionCheck';
import OnlinePlayersBox from '@/Shared/OnlinePlayersBox';
import IngameChatBox from '@/Shared/IngameChatBox';
import ServerStatusBox from '@/Shared/ServerStatusBox';
import PollBox from '@/Shared/PollBox';
import DonationBox from '@/Shared/DonationBox';
import OnlineUsersBox from '@/Shared/OnlineUsersBox';
import TopPlayersListBox from '@/Shared/TopPlayersListBox';

export default {
    components: {
        TopPlayersListBox,
        OnlineUsersBox,
        DonationBox,
        PollBox,
        ServerStatusBox,
        IngameChatBox,
        OnlinePlayersBox,
        DidYouKnowBox,
        VersionCheck,
        SocialChannelBox,
        PostListBox,
        WelcomeBox,
        NewsBox,
        AppLayout,
        ShoutBox,
        DiscordServerBox,
        NewestUserBox,
        VotingSitesBox,
        LatestPinnedNews
    },
    props: {
        pinnedNewsList: Array,
        newslist: Array,
        newestUser: Object,
        latestPoll: Object,
        onlineUsers: Array,
        welcomeBoxContentHtml: String|null,
        chatDefaultServerId: Number,
        chatServerList: Array,
        top10Players: Array
    },

    data() {
        return {
            generalSettings: this.$page.props.generalSettings
        };
    }
};
</script>


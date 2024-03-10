<template>
  <app-layout>
    <app-head
      :title="__('Polls')"
    />

    <div class="px-2 py-4 md:py-12 md:px-10 max-w-7xl mx-auto">
      <div class="flex flex-col md:flex-row md:space-x-4">
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
                <infinite-scroll :load-more="loadMorePolls">
                  <transition-group
                    name="list"
                    tag="div"
                    class="space-y-4"
                  >
                    <poll-box
                      v-for="poll in pollList.data"
                      :key="poll.id"
                      :is-listing="true"
                      :poll="poll.json_for_vue"
                    />
                  </transition-group>
                </infinite-scroll>

                <div
                  v-if="pollList.data <= 0"
                  class="p-3 md:px-5 bg-white dark:bg-cool-gray-800 rounded shadow italic text-gray-500 dark:text-gray-400 text-center text-sm font-semibold"
                >
                  {{ __("No Polls Found") }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="hidden md:flex flex-col space-y-4 flex-none w-1/4 h-screen sticky top-5">
          <server-status-box />
          <shout-box />
        </div>
      </div>
    </div>
  </app-layout>
</template>

<script>
import AppLayout from '@/Layouts/AppLayout.vue';
import ShoutBox from '@/Shared/ShoutBox.vue';
import InfiniteScroll from '@/Components/InfiniteScroll.vue';
import ServerStatusBox from '@/Shared/ServerStatusBox.vue';
import PollBox from '@/Shared/PollBox.vue';
import DidYouKnowBox from '@/Shared/DidYouKnowBox.vue';
import DiscordServerBox from '@/Shared/DiscordServerBox.vue';

export default {

    components: {
        DiscordServerBox,
        DidYouKnowBox,
        ServerStatusBox,
        AppLayout,
        ShoutBox,
        InfiniteScroll,
        PollBox
    },
    props: {
        polls: Object,
    },

    data() {
        return {
            pollList: this.polls
        };
    },

    methods: {
        loadMorePolls() {
            if (!this.pollList.next_page_url) {
                return Promise.resolve();
            }

            return axios(this.pollList.next_page_url).then(response => {
                this.pollList = {
                    ...response.data,
                    data: [...this.pollList.data, ...response.data.data]
                };
            });
        }
    },
};
</script>

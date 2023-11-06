<script setup>
import KpiOverviewCard from '@/Components/Dashboard/KpiOverviewCard.vue';
import AppHead from '@/Components/AppHead.vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import millify from 'millify';
import {
    UserPlusIcon,
    UserIcon,
    FireIcon,
    ChatBubbleBottomCenterTextIcon,
} from '@heroicons/vue/24/solid';
import KpiOverviewCardForDashboard from '@/Components/Dashboard/KpiOverviewCardForDashboard.vue';
import PlayersOverTimeMetricBox from '@/Shared/PlayersOverTimeMetricBox.vue';
import PlayersPerServerMetricBox from '@/Shared/PlayersPerServerMetricBox.vue';
import PlayersPerCountryMetricBox from '@/Shared/PlayersPerCountryMetricBox.vue';
import NetworkTrendsMetricBox from '@/Shared/NetworkTrendsMetricBox.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import PlayersJoinAddressMetricBox from '@/Shared/PlayersJoinAddressMetricBox.vue';
import PlayersMinecraftVersionMetricBox from '@/Shared/PlayersMinecraftVersionMetricBox.vue';

const {can} = useAuthorizable();

defineProps({
    kpiTotalUsers: Number,
    kpiUserCreatedForInterval: Number,
    kpiUserLastSeenForInterval: Number,
    kpiTotalUserPercent: Number,

    kpiTotalPlayers: Number,
    kpiPlayerCreatedForInterval: Number,
    kpiPlayerLastSeenForInterval: Number,
    kpiTotalPlayersPercent: Number,

    kpiTotalFailedJobs: Number,
    kpiFailedJobsForInterval: Number,
    kpiTotalFailedJobPercent: Number,

    kpiTotalPosts: Number,
    kpiPostCreatedForInterval: Number,
    kpiTotalPostsPercent: Number,
    kpiTotalComments: Number,
});
</script>

<template>
  <AdminLayout>
    <AppHead :title="__('Admin Dashboard')" />

    <div
      v-if="!can('view admin_dashboard')"
      class="p-4 flex"
    >
      <div class="flex-1 bg-white dark:bg-gray-800 p-4 rounded text-red-400 text-center italic">
        {{ __("Sorry! You are not allowed to view Admin Dashboard Statistics.") }}
      </div>
    </div>

    <div
      v-else
      class="p-4 space-y-4"
    >
      <div
        id="row1"
        class="flex justify-between flex-1 space-x-4"
      >
        <KpiOverviewCardForDashboard
          class="flex-1"
          title="Registered Users"
          :value="millify(kpiTotalUsers)"
          :sub-value="kpiUserCreatedForInterval"
          :change="kpiTotalUserPercent"
          change-desc="in last 7 days"
          :icon="UserPlusIcon"
          icon-class="text-light-blue-500 bg-light-blue-100 dark:bg-light-blue-500 dark:text-white"
          :description="`Active: ${kpiUserLastSeenForInterval} users`"
        />

        <KpiOverviewCardForDashboard
          class="flex-1"
          title="Total Players"
          :value="kpiTotalPlayers"
          :sub-value="kpiPlayerCreatedForInterval"
          :change="kpiTotalPlayersPercent"
          change-desc="in last 7 days"
          :icon="UserIcon"
          icon-class="text-green-500 bg-green-100 dark:bg-green-500 dark:text-white"
          :description="`Active: ${kpiPlayerLastSeenForInterval} players`"
        />

        <KpiOverviewCardForDashboard
          class="flex-1"
          title="Total Posts"
          :value="kpiTotalPosts"
          :sub-value="kpiPostCreatedForInterval"
          :change="kpiTotalPostsPercent"
          change-desc="in last 7 days"
          :icon="ChatBubbleBottomCenterTextIcon"
          icon-class="text-amber-500 bg-amber-100 dark:bg-amber-500 dark:text-white"
          :description="`Total comments: ${kpiTotalComments}`"
        />

        <KpiOverviewCard
          class="flex-1"
          title="Failed Jobs"
          :value="millify(kpiTotalFailedJobs)"
          :sub-value="`(${
            kpiFailedJobsForInterval > 0 ? '+' : ''
          }${millify(kpiFailedJobsForInterval)})`"
          :sub-value-class="[
            kpiFailedJobsForInterval > 0
              ? 'text-red-500'
              : 'text-green-500',
          ]"
          :change="`${
            kpiTotalFailedJobPercent > 0 ? '+' : ''
          }${millify(kpiTotalFailedJobPercent, { precision: 2 })}%`"
          :change-class="[
            kpiTotalFailedJobPercent > 0
              ? 'text-red-500 bg-red-100'
              : 'text-green-500 bg-green-100',
          ]"
          change-desc="in last 7 days"
          :icon="FireIcon"
          icon-class="text-red-500 bg-red-100 dark:bg-red-500 dark:text-white"
          description="Jobs failed to run."
        />
      </div>

      <div
        id="row2"
        class="flex justify-between flex-1 space-x-4"
      >
        <PlayersOverTimeMetricBox class="basis-8/12" />
        <PlayersPerServerMetricBox class="basis-4/12" />
      </div>

      <div
        id="row3"
        class="flex justify-between flex-1 space-x-4"
      >
        <PlayersPerCountryMetricBox class="basis-1/2" />
        <NetworkTrendsMetricBox class="basis-1/2" />
      </div>

      <div
        id="row4"
        class="flex justify-between flex-1 space-x-4"
      >
        <PlayersJoinAddressMetricBox
          :top-count="20"
          class="basis-1/2"
        />
        <PlayersMinecraftVersionMetricBox
          :top-count="20"
          class="basis-1/2"
        />
      </div>
    </div>
  </AdminLayout>
</template>

<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import CustomFormIntelPieChart from '@/Shared/CustomFormIntelPieChart.vue';
import CustomFormIntelBarChart from '@/Shared/CustomFormIntelBarChart.vue';
import CustomFormIntelListChart from '@/Shared/CustomFormIntelListChart.vue';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { startCase } from 'lodash';

const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString, secondsToHMS } = useHelpers();

const props = defineProps({
    recruitment: {
        type: Object,
    },
    intel: {
        type: Object,
    },
    submissionCountByStatus: {
        type: Object,
    },
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Application Forms'),
        url: route('admin.recruitment.index'),
        current: false,
    },
    {
        text: props.recruitment.title,
        current: true,
    },
];

const requestPieChartData = {
    label: 'Total Requests',
    data: {
        'Open Requests': props.recruitment.open_submissions_count,
        'Closed Requests': props.recruitment.closed_submissions_count,
    }
};

const submissionCountByStatusData = {
    label: 'Submission Status',
    data: props.submissionCountByStatus
};

</script>


<template>
  <AdminLayout>
    <AppHead
      :title="__(':title Intel - Application Form', {
        title: recruitment.title,
      })"
    />

    <div class="px-10 py-8 mx-auto space-y-4">
      <div class="mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <Card>
        <CardHeader>
          <CardTitle class="text-2xl">
            {{ recruitment.title }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Url Slug") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ recruitment.slug }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Status") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ startCase(recruitment.status.value) }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("This application hiring for") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ recruitment.related_role ? recruitment.related_role.display_name : __("not applicable") }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Max submission per user") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ recruitment.max_submission_per_user ? recruitment.max_submission_per_user : __("not applicable") }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Submission Cooldown") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ recruitment.submission_cooldown_in_seconds ? secondsToHMS(recruitment.submission_cooldown_in_seconds, true) : __("none") }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Allow only Player Linked Users") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ recruitment.is_allow_only_player_linked_users }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Allow only Verified Users") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ recruitment.is_allow_only_verified_users }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Minimum staff role weight to view submissions") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ recruitment.min_role_weight_to_view_submission ? recruitment.min_role_weight_to_view_submission : __("none") }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Minimum staff role weight to act on submissions") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ recruitment.min_role_weight_to_act_on_submission ? recruitment.min_role_weight_to_act_on_submission : __("none") }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Enable Messaging Feature") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ recruitment.is_allow_messages_from_users }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Notify staff on Events") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ recruitment.is_notify_staff_on_submission }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Created") }}</span>
              <span
                v-tippy
                :title="formatTimeAgoToNow(recruitment.created_at)"
                class="text-sm text-foreground font-semibold"
              >{{ formatToDayDateString(recruitment.created_at) }}
                ({{ __("by :username" , {
                  username: recruitment.creator.username
                }) }})
              </span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Updated") }}</span>
              <span
                v-tippy
                :title="formatTimeAgoToNow(recruitment.updated_at)"
                class="text-sm text-foreground font-semibold"
              >{{ formatToDayDateString(recruitment.updated_at) }}
                <span v-if="recruitment.updater">
                  ({{ __("by :username" , {
                    username: recruitment.updater.username
                  }) }})
                </span>
              </span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Charts -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <CustomFormIntelPieChart
          :title="requestPieChartData.label"
          :data="requestPieChartData.data"
        />
        <CustomFormIntelPieChart
          :title="submissionCountByStatusData.label"
          :data="submissionCountByStatusData.data"
        />
        <template
          v-for="item of intel"
          :key="item.label"
        >
          <CustomFormIntelPieChart
            v-if="['select', 'radio', 'multiselect', 'checkbox'].includes(item.type)"
            :title="item.label"
            :data="item.data"
          />

          <CustomFormIntelBarChart
            v-if="['datetime-local', 'date', 'time', 'month', 'week'].includes(item.type)"
            :title="item.label"
            :data="item.data"
          />

          <CustomFormIntelListChart
            v-if="['text', 'textarea', 'email', 'number', 'password', 'tel', 'url'].includes(item.type)"
            :title="item.label"
            :data="item.data"
          />
        </template>
      </div>
    </div>
  </AdminLayout>
</template>

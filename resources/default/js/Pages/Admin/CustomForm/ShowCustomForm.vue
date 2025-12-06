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
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

const props = defineProps({
    form: {
        type: Object,
    },
    intel: {
        type: Object,
    }
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Custom Forms'),
        url: route('admin.custom-form.index'),
        current: false,
    },
    {
        text: props.form.title,
        current: true,
    },
];

</script>


<template>
  <AdminLayout>
    <AppHead
      :title="__(':formtitle Intel - Custom Forms', {
        formtitle: form.title,
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
            {{ form.title }}
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Url Slug") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ form.slug }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Status") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ startCase(form.status.value) }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Visible in Listing") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ form.is_visible_in_listing }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Who can submit?") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ form.can_create_submission }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Max submission per user") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ form.max_submission_per_user ? form.max_submission_per_user : __("not applicable") }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Minimum staff role weight to view submissions") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ form.min_role_weight_to_view_submission ? form.min_role_weight_to_view_submission : __("none") }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Notify staff on new submission") }}</span>
              <span class="text-sm text-foreground font-semibold">{{ form.is_notify_staff_on_submission }}</span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Created") }}</span>
              <span
                v-tippy
                :title="formatTimeAgoToNow(form.created_at)"
                class="text-sm text-foreground font-semibold"
              >{{ formatToDayDateString(form.created_at) }}
                ({{ __("by :username" , {
                  username: form.creator.username
                }) }})
              </span>
            </div>

            <div class="flex justify-between items-center py-2 border-b border-border">
              <span class="text-sm font-medium text-muted-foreground">{{ __("Updated") }}</span>
              <span
                v-tippy
                :title="formatTimeAgoToNow(form.updated_at)"
                class="text-sm text-foreground font-semibold"
              >{{ formatToDayDateString(form.updated_at) }}
                <span v-if="form.updater">
                  ({{ __("by :username" , {
                    username: form.updater.username
                  }) }})
                </span>
              </span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Charts -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
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

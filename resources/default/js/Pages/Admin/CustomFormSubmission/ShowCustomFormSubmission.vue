<script setup>
import AppHead from '@/Components/AppHead.vue';
import { useHelpers } from '@/Composables/useHelpers';
import { useTranslations } from '@/Composables/useTranslations';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useAuthorizable } from '@/Composables/useAuthorizable';
import { computed } from 'vue';
import { FormKitSchema } from '@formkit/vue';
import { useFormKit } from '@/Composables/useFormKit';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { ArrowLeftCircleIcon } from '@heroicons/vue/24/outline';

const { can } = useAuthorizable();
const { __ } = useTranslations();
const { formatTimeAgoToNow, formatToDayDateString } = useHelpers();

const props = defineProps({
    submission: {
        type: Object,
    },
});

const parsedData = computed(() => {
    return props.submission.data.map((item) => {
        return {
            ...item,
            value: item.data
        };
    });
});

const formSchema = useFormKit().generateSchemaFromFieldsArray(parsedData.value, true);

const breadcrumbItems = computed(() => [
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
        text: props.submission.deleted_at ? __('Archived Submissions') : __('Submissions'),
        url: props.submission.deleted_at ? route('admin.custom-form-submission.index-archived') : route('admin.custom-form-submission.index'),
        current: false,
    },
    {
        text: __('Submission #') + props.submission.id,
        current: true,
    },
]);
</script>

<template>
  <AdminLayout>
    <AppHead
      :title="__(':formtitle submission #:index - Custom Form Submissions', {
        index: submission.id,
        formtitle: submission.custom_form.title,
      })"
    />

    <div class="px-10 py-8 mx-auto max-w-screen-2xl space-y-4">
      <!-- Breadcrumb -->
      <div class="flex items-center justify-between">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <!-- Header -->
      <div class="flex justify-between items-center">
        <h3 class="text-xl font-extrabold text-foreground dark:text-foreground">
          {{ __(":formtitle - Submission #:index", {index: submission.id, formtitle: submission.custom_form.title}) }}
          <span
            v-if="submission.deleted_at"
            class="text-base text-orange-500 italic"
          >
            {{ __("(Archived)") }}
          </span>
        </h3>
        <div class="flex gap-2">
          <Button
            v-if="can('archive custom_form_submissions') && !submission.deleted_at"
            v-confirm="{
              message: 'Archive this Custom Form Submission? It will move to archive section.',
            }"
            v-tippy
            size="sm"
            class="bg-orange-500 hover:bg-orange-600 text-white"
            as-child
          >
            <InertiaLink
              method="POST"
              :href="route('admin.custom-form-submission.archive', submission.id)"
            >
              {{ __("Archive") }}
            </InertiaLink>
          </Button>

          <Button
            v-if="can('delete custom_form_submissions') && submission.deleted_at"
            v-confirm="{
              message: 'Restore this Custom Form Submission? It will move back to submissions list.',
            }"
            v-tippy
            size="sm"
            class="bg-success-500 hover:bg-success-600 text-white"
            as-child
          >
            <InertiaLink
              method="POST"
              :href="route('admin.custom-form-submission.restore', submission.id)"
            >
              {{ __("Restore") }}
            </InertiaLink>
          </Button>

          <Button
            v-if="can('delete custom_form_submissions')"
            v-confirm="{
              message: 'Delete this Custom Form Submission? This action cannot be undone.',
            }"
            v-tippy
            variant="destructive"
            size="sm"
            as-child
          >
            <InertiaLink
              method="DELETE"
              :href="route('admin.custom-form-submission.delete', submission.id)"
            >
              {{ __("Delete") }}
            </InertiaLink>
          </Button>
        </div>
      </div>

      <!-- Main Content -->
      <div class="flex flex-col md:flex-row gap-4">
        <!-- Left Column (Form Data) -->
        <div class="flex-grow md:w-2/3">
          <div class="bg-card text-card-foreground border rounded-lg shadow p-4 md:p-6 no-disabled-effect">
            <FormKit
              :disabled="true"
              type="form"
              :submit-attrs="{
                inputClass: 'hidden',
              }"
            >
              <FormKitSchema :schema="formSchema" />
            </FormKit>
          </div>
        </div>

        <!-- Right Column (Details) -->
        <div class="md:w-1/3">
          <div class="bg-card text-card-foreground border rounded-lg shadow p-4">
            <ul class="flex flex-col mt-3">
              <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground">
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("User") }}</span>
                  <div>
                    <InertiaLink
                      v-if="submission.user"
                      :href="route('user.public.get', submission.user.username)"
                      class="flex items-center"
                    >
                      <div class="flex-shrink-0 h-10 w-10 mr-2">
                        <img
                          class="h-10 w-10 rounded-full"
                          :src="submission.user.profile_photo_url"
                          alt="Avatar"
                        >
                      </div>
                      <div class="flex-col">
                        <div
                          class="text-sm font-semibold text-foreground dark:text-foreground whitespace-nowrap truncate"
                          :style="[submission.user.roles[0].color ? {color: submission.user.roles[0].color} : null]"
                        >
                          {{ submission.user.name }}
                        </div>
                        <div class="text-sm text-muted-foreground">
                          @{{ submission.user.username }}
                        </div>
                      </div>
                    </InertiaLink>
                    <div
                      v-else
                      class="flex items-center italic text-sm text-foreground dark:text-foreground"
                    >
                      {{ __("Anonymous") }}
                    </div>
                  </div>
                </div>
              </li>

              <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground">
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("User's Country") }}</span>
                  <div class="flex items-center space-x-1">
                    <span>{{ submission.country.name }}</span>
                    <div
                      v-if="submission.country"
                      v-tippy
                      class="flex-shrink-0 h-10 w-10 focus:outline-none"
                      :content="submission.country.name"
                    >
                      <img
                        class="h-10 w-10"
                        :src="submission.country.photo_path"
                        alt=""
                      >
                    </div>
                  </div>
                </div>
              </li>

              <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground">
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("Form") }}</span>
                  <span>{{ submission.custom_form.title }}</span>
                </div>
              </li>

              <li class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground">
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("Created At") }}</span>
                  <span
                    v-tippy
                    :title="formatTimeAgoToNow(submission.created_at)"
                  >{{ formatToDayDateString(submission.created_at) }}</span>
                </div>
              </li>

              <li
                v-if="submission.deleted_at"
                class="inline-flex items-center px-4 py-3 -mt-px text-sm font-semibold text-foreground gap-x-2 first:rounded-t-lg first:mt-0 last:rounded-b-lg dark:text-foreground"
              >
                <div class="flex items-center justify-between w-full">
                  <span>{{ __("Archived At") }}</span>
                  <span
                    v-tippy
                    :title="formatTimeAgoToNow(submission.deleted_at)"
                  >{{ formatToDayDateString(submission.deleted_at) }}</span>
                </div>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

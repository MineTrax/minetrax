<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useTranslations } from '@/Composables/useTranslations';
import AppBreadcrumb from '@/Shared/AppBreadcrumb.vue';
import { Button } from '@/Components/ui/button';
import { Link, useForm } from '@inertiajs/vue3';
import XInput from '@/Components/Form/XInput.vue';
import XTextarea from '@/Components/Form/XTextarea.vue';
import ImageUpload from '@/Components/Form/ImageUpload.vue';

const { __ } = useTranslations();

const props = defineProps({
    rank: Object,
});

const breadcrumbItems = [
    {
        text: __('Admin'),
        current: false,
    },
    {
        text: __('Player Ranks'),
        url: route('admin.rank.index'),
        current: false,
    },
    {
        text: __('Edit Player Rank'),
        current: true,
    },
    {
        text: props.rank.shortname,
        current: true,
    }
];

const form = useForm({
    name: props.rank.name,
    shortname: props.rank.shortname,
    description: props.rank.description,
    total_score_needed: props.rank.total_score_needed,
    total_play_time_needed: props.rank.total_play_time_needed,
    photo: null,
    '_method': 'PUT'
});

function updateRank() {
    form.post(route('admin.rank.update', props.rank.id), {
        preserveScroll: true
    });
}
</script>

<template>
  <AdminLayout>
    <app-head :title="__('Edit Rank - :name', { name: rank.name })" />

    <div class="px-10 py-8 mx-auto max-w-6xl text-foreground">
      <div class="flex justify-between mb-4">
        <AppBreadcrumb
          class="mt-0"
          breadcrumb-class="max-w-none px-0 md:px-0"
          :items="breadcrumbItems"
        />
      </div>

      <div class="mt-6">
        <form @submit.prevent="updateRank">
          <div class="shadow rounded-lg">
            <div class="px-4 py-5 bg-card sm:p-6">
              <div class="grid grid-cols-6 gap-6">
                <div class="col-span-6">
                  <ImageUpload
                    id="photo"
                    name="photo"
                    :label="__('Rank Image')"
                    :hint="__('A small image (Eg: 50x50) is recommended')"
                    :current-url="rank.photo_url"
                    v-model="form.photo"
                    :error="form.errors.photo"
                    :removable="false"
                    shape="rect"
                    :preview-class="'h-32 w-32'"
                    :upload-label="__('Upload')"
                    :change-label="__('Change')"
                    object-fit="contain"
                  />
                </div>

                <div class="col-span-6 sm:col-span-4">
                  <XInput
                    id="name"
                    v-model="form.name"
                    :label="__('Rank Name')"
                    :help="__('Eg: Knight')"
                    :error="form.errors.name"
                    type="text"
                    name="name"
                  />
                </div>

                <div class="col-span-6 sm:col-span-2">
                  <XInput
                    id="shortname"
                    v-model="form.shortname"
                    :label="__('Short Name')"
                    :help="__('Eg: knight')"
                    :error="form.errors.shortname"
                    type="text"
                    name="shortname"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="total_score_needed"
                    v-model="form.total_score_needed"
                    :label="__('Score Needed')"
                    :error="form.errors.total_score_needed"
                    type="number"
                    name="total_score_needed"
                  />
                </div>

                <div class="col-span-6 sm:col-span-3">
                  <XInput
                    id="total_play_time_needed"
                    v-model="form.total_play_time_needed"
                    :label="__('Play Time Needed')"
                    :help="__('Time in seconds. Eg: 60 for 1 minute')"
                    :error="form.errors.total_play_time_needed"
                    type="number"
                    name="total_play_time_needed"
                  />
                </div>

                <div class="col-span-6">
                  <XTextarea
                    id="description"
                    v-model="form.description"
                    :rows="5"
                    :label="__('Description')"
                    :error="form.errors.description"
                    name="description"
                  />
                </div>

                <div class="col-span-6">
                  <p class="text-sm text-muted-foreground">
                    {{ __("Ranks are assigned to players when a given criteria is matched, eg: play time, score etc.") }}
                  </p>
                  <p class="mt-2 text-sm text-muted-foreground">
                    {{ __("Each rank is sorted according to its weight. More the score and time is more the weight.") }}
                  </p>
                  <p class="mt-2 text-sm text-muted-foreground">
                    {{ __("If you instead want to sync player rank from server to web, you can do that too from Settings -> Player Settings. Currently Luckperms is supported. Make sure to have same Short Name for rank name what you have chosen in Luckperms. While rank sync is enabled the criteria like score and time will be ignored but while adding ranks it is recommended to enter something in them so web know which rank has more weight.") }}
                  </p>
                </div>
              </div>
            </div>
            <div class="px-4 py-3 bg-card border-t border-border sm:px-6 flex justify-end gap-2">
              <Button
                variant="outline"
                as-child
              >
                <Link :href="route('admin.rank.index')">
                  {{ __("Cancel") }}
                </Link>
              </Button>
              <Button
                type="submit"
                :disabled="form.processing"
              >
                <svg
                  v-if="form.processing"
                  class="animate-spin -ml-1 mr-2 h-4 w-4"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  />
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  />
                </svg>
                {{ __("Update Rank") }}
              </Button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </AdminLayout>
</template>

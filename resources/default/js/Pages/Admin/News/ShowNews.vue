<template>
  <AdminLayout>
    <app-head :title="news.title" />

    <div class="py-12 px-10 max-w-6xl mx-auto">
      <div class="flex justify-end mb-8">
        <div class="flex">
          <button
            class="mr-2 inline-flex items-center px-4 py-2 bg-error-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-error-700 active:bg-error-900 focus:outline-none focus:border-error-900 focus:shadow-outline-red transition ease-in-out duration-150"
            @click="confirmNewsDeletion(news.id)"
          >
            {{ __("Delete") }}
          </button>
          <inertia-link
            :href="route('admin.news.edit', news.id)"
            class="mr-2 inline-flex items-center px-4 py-2 bg-warning-500 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-warning-600 active:bg-warning-700 focus:outline-none focus:borde-warning-700 focus:shadow-outline-yellow transition ease-in-out duration-150"
          >
            <span>{{ __("Edit") }}</span>
          </inertia-link>
          <inertia-link
            :href="route('admin.news.index')"
            class="inline-flex items-center px-4 py-2 bg-surface-400 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-surface-500 active:bg-surface-600 focus:outline-none focus:border-foreground focus:shadow-outline-gray transition ease-in-out duration-150"
          >
            <span>{{ __("Go Back") }}</span>
          </inertia-link>
        </div>
      </div>
      <div class="flex flex-col">
        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
          <ShowNewsCard :news="news" />
        </div>
      </div>
    </div>

    <jet-confirmation-modal
      :show="!!newsBeingDeleted"
      @close="newsBeingDeleted = null"
    >
      <template #title>
        {{ __("Delete News") }}
      </template>

      <template #content>
        {{ __("Are you sure you would like to delete this News?") }}
      </template>

      <template #footer>
        <jet-secondary-button @click="newsBeingDeleted = null">
          {{ __("Nevermind") }}
        </jet-secondary-button>

        <jet-danger-button
          class="ml-2"
          :class="{ 'opacity-25': form.processing }"
          :disabled="form.processing"
          @click="deleteNews"
        >
          {{ __("Delete News") }}
        </jet-danger-button>
      </template>
    </jet-confirmation-modal>
  </AdminLayout>
</template>

<script>
import JetConfirmationModal from '@/Jetstream/ConfirmationModal.vue';
import JetSecondaryButton from '@/Jetstream/SecondaryButton.vue';
import JetDangerButton from '@/Jetstream/DangerButton.vue';
import { useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useHelpers } from '@/Composables/useHelpers';
import ShowNewsCard from '@/Shared/ShowNewsCard.vue';

export default {
    components: {
        AdminLayout,
        JetConfirmationModal,
        JetSecondaryButton,
        JetDangerButton,
        ShowNewsCard
    },
    props: {
        news: Object
    },
    setup() {
        const {formatTimeAgoToNow, formatToDayDateString} = useHelpers();
        return {formatTimeAgoToNow, formatToDayDateString};
    },

    data() {
        return {
            form: useForm({}),
            newsBeingDeleted: null
        };
    },

    methods: {
        confirmNewsDeletion(newsId) {
            this.newsBeingDeleted = newsId;
        },

        deleteNews() {
            this.form.delete(route('admin.news.delete', this.newsBeingDeleted), {
                preserveScroll: false,
                preserveState: false,
                onSuccess: () => (this.newsBeingDeleted = null),
            });
        },
    }
};
</script>
